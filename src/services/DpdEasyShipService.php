<?php

namespace craftsnippets\dpdeasyship\services;

use Craft;
use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\elements\Order;
use craft\elements\Address;
use craftsnippets\dpdeasyship\DpdEasyShip;
use craftsnippets\dpdeasyship\helpers\Common;
use yii\base\Component;
use craft\helpers\Queue;

use craftsnippets\dpdeasyship\fields\DpdEasyShipField;

use DataLinx\DPD\API;

// requests
use DataLinx\DPD\Requests\ParcelImport as ParcelImportRequest;
use craftsnippets\dpdeasyship\requests\ParcelPrintRequest;
use craftsnippets\dpdeasyship\requests\ParcelStatusRequest;
use craftsnippets\dpdeasyship\requests\ParcelCancelRequest;
use craftsnippets\dpdeasyship\requests\ParcelDeleteRequest;


use craftsnippets\dpdeasyship\api\ApiPdf;

use craft\helpers\UrlHelper;
use craft\fields\PlainText;

use craftsnippets\dpdeasyship\models\DpdEasyshipParcel;
use craftsnippets\dpdeasyship\models\DpdEasyShipData;

use DataLinx\DPD\ParcelType;
use DataLinx\DPD\ParcelCODType;

use craft\events\DefineRulesEvent;
use yii\base\Event;

class DpdEasyShipService extends Component
{
    const MAX_SENDER_REMARK = 50;
    public function getApiObject($class = null): API
    {
        $login = DpdEasyShip::getInstance()->getSettings()->apiLogin;
        $password = DpdEasyShip::getInstance()->getSettings()->apiPassword;
        $country = DpdEasyShip::getInstance()->getSettings()->apiCountry;

        // class
        if(is_null($class)){
            $class = API::class;
        }

        // Set up the API
        $dpd = new $class($login, $password, $country);
        return $dpd;
    }

    public function getOrderShippingField(): ?DpdEasyShipField
    {
        $fields = Craft::$app->getFields()->getLayoutByType(Order::class)->getCustomFields();
        $shippingFields = array_filter($fields, function($single){
            return get_class($single) == DpdEasyShipField::class;
        });
        $field = reset($shippingFields) ?: null;
        return $field;
    }

    public function orderHasShippingField()
    {
        return !is_null($this->getOrderShippingField());
    }

    public function insertShippingInterface()
    {
        Craft::$app->view->hook('cp.commerce.order.edit.main-pane', function(array &$context) {
            $order = $context['order'];

            // if allowed but no shipping field, display warning
            if($this->orderHasShippingField() == false && $order->dpdEasyship->getCanUse() == true){
                $templatePath = 'dpd-easy-ship/missing-field-error.twig';
                $renderedHtml = Craft::$app->view->renderTemplate(
                    $templatePath, $context, Craft::$app->view::TEMPLATE_MODE_CP
                );
                return $renderedHtml;
            }
            // if not allowed and no shipping field, show nothing
            if($this->orderHasShippingField() == false && $order->dpdEasyship->getCanUse() == false){
                return;
            }

            // show nothing, but only for orders that do no have parcels yet, so when for some reason order later should not be allowed to use easyship anymore, parcels still can be removed
            if($order->dpdEasyship->getCanUse() == false && $order->dpdEasyship->getHasParcels() == false){
                return;
            }
            // pdf url
            $context['pdfUrl'] = $this->getOrdersPdfUrl([$order]);

            // location options
            $context['locationOptions'] = $this->getLocationOptions();
            $context['defaultLocationId'] = DpdEasyShip::getInstance()->getSettings()->defaultLocationId;

            $templatePath = 'dpd-easy-ship/shipping-interface.twig';
            $renderedHtml = Craft::$app->view->renderTemplate(
                $templatePath, $context, Craft::$app->view::TEMPLATE_MODE_CP
            );
            return $renderedHtml;
        });
    }

    public function validateRecipientAddress($address)
    {

    }

    // set from request:
    // num_of_parcel
    // weight
    // sender_remark

    public function createParcels(Order $order, array $requestSettings = [])
    {

        if($order->dpdEasyship->getHasParcels() == true){
            return [
                'success' => false,
                'error' => Craft::t('dpd-easy-ship', 'Parcels already exist for this order.'),
                'errorType' => 'exists',
            ];
        }

        Common::addLog('create', 'dpd-easyship');
        $shippingData = new DpdEasyShipData([
            'order' => $order,
        ]);

        $defaultSettings = [
            'num_of_parcel' => 1,
        ];

        $address = $order->shippingAddress;
        $request = new ParcelImportRequest($this->getApiObject());

        // num of parcel - from request
        $num_of_parcel = $requestSettings['num_of_parcel'] ?? null;
        if(!is_null($num_of_parcel)){
            $request->num_of_parcel = $num_of_parcel;
        }else{
            $request->num_of_parcel = $defaultSettings['num_of_parcel'];
        }

        // other params set from POST request
//        $weight = $requestSettings['weight'] ?? null;
//        if(!is_null($weight)){
//            $request->weight = $weight;
//        }
        $sender_remark = $requestSettings['sender_remark'] ?? null;
        if(!is_null($sender_remark)){
            $request->sender_remark = $sender_remark;
        }

        // set name and additional name
        if(!is_null($address->organization)){
            $request->name1 = $address->organization;
            // if organisation defined, make fullname as receiver additional name
            if(!is_null($address->fullName)){
                $request->name2 = $address->fullName;
            }
        }else if(!is_null($address->fullName)){
            $request->name1 = $address->fullName;
        }else{
            return [
                'success' => false,
                'error' => Craft::t('dpd-easy-ship', 'No organization or full name defined for the shipping address.'),
                'errorType' => 'Address validation',
            ];
        }

        // validate address fields
        if(
            is_null($address->addressLine1) ||
            is_null($address->addressLine2) ||
            is_null($address->locality) ||
            is_null($address->postalCode) ||
            is_null($address->countryCode)
        ){
            return [
                'success' => false,
                'error' => Craft::t('dpd-easy-ship', 'Shipping address does not have all required values entered. Make sure that street (address line 1), street and home number (address line 2), locality, postal code and country are not empty.'),
                'errorType' => 'Address validation',
            ];
        }

        // only croatia and slovenia are allowed
        if($address->countryCode != 'HR' && $address->countryCode != 'SI'){
            return [
                'success' => false,
                'error' => Craft::t('dpd-easy-ship', 'Only Croatia or Slovenia are allowed for delivery address.'),
                'errorType' => 'Address validation',
            ];
        }

        // set address fields
        $request->street = $address->addressLine1;
        $request->rPropNum = $address->addressLine2;
        $request->city = $address->locality;
        $request->pcode = $address->postalCode;
        $request->country = $address->countryCode;

        // other request data
        $request->email = $order->email;
        $request->order_number = $order->getShortNumber();

        // cod
        $request->parcel_type = $shippingData->getDefaultParcelType();
        $request->cod_amount = $order->getTotalPrice();
        $request->cod_purpose = $order->getShortNumber();
        $request->parcel_cod_type = DpdEasyShip::getInstance()->getSettings()->codType;

        // phone number from address field
        if(!is_null($this->getPhoneField())){
            $request->phone = $address->getFieldValue($this->getPhoneField()->handle);
        }

        // sender info
        $senderLocationId = $requestSettings['senderLocationId'] ?? null;

        if(!is_null($senderLocationId) && $location = CommercePlugin::getInstance()->getInventoryLocations()->getInventoryLocationById((int)$senderLocationId)){
            $senderAddress = $location->getAddress();

            // only croatia and slovenia are allowed
            if($senderAddress->countryCode != 'HR' && $senderAddress->countryCode != 'SI'){
                return [
                    'success' => false,
                    'error' => Craft::t('dpd-easy-ship', 'Only Croatia or Slovenia are allowed for sender address.'),
                    'errorType' => 'Address validation',
                ];
            }

            $request->sender_name = $senderAddress->organization;
            $request->sender_city = $senderAddress->locality;
            $request->sender_pcode = $senderAddress->postalCode;
            $request->sender_country = $senderAddress->countryCode;
            $request->sender_street = $senderAddress->addressLine1;
            if(!is_null($this->getPhoneField())){
                $request->sender_phone = $senderAddress->getFieldValue($this->getPhoneField()->handle);
            }
        }

        try {
            $response = $request->send();
        } catch (\DataLinx\DPD\Exceptions\ValidationException $exception) {
            var_dump($exception);
            exit();
        } catch (\DataLinx\DPD\Exceptions\APIException $exception) {
            var_dump($exception);
            exit();
        } catch (\Exception $exception) {
            var_dump($exception);
            exit();
        }

        if(!$response->isSuccessful()){
            return [
                'success' => false,
                'error' => 'API Error: ' . $response->getError(),
                'errorType' => 'api',
            ];
        }

//        Common::addLog($response->getError(), 'dpd-easyship');


        // set parcels
        $parcelNumbers = $response->getParcelNumbers();
        $parcels = array_map(function($single) use($order){
            return new DpdEasyshipParcel([
                'number' => $single,
                'status' => null,
                'order' => $order,
            ]);
        }, $parcelNumbers);
        $shippingData->parcels = $parcels;

        $shippingData->assignRequestData($request);

        // insert data
        $fieldContent = $shippingData->encodeData();
        $order->setFieldValue($this->getOrderShippingField()->handle, $fieldContent);
        $save = Craft::$app->elements->saveElement($order);

        $order->reapplyDpdEasyship();
        $this->updateParcelsStatus($order);

        if(!$save){
            return [
                'success' => false,
                'error' => implode(' ', $order->getErrorSummary(true)),
                'errorType' => 'Order validation',
            ];
        }
        return [
            'success' => true,
            'error' => null,
        ];
    }



    public function removeParcels($order)
    {
        // already printed and not printed must use different request type
        $notPrintedParcels = array_filter($order->dpdEasyship->parcels, function($single) use($order){
            return $single->status != $order->dpdEasyship::STATUS_PRINTED;
        });
        $printedParcels = array_filter($order->dpdEasyship->parcels, function($single) use($order){
            return $single->status == $order->dpdEasyship::STATUS_PRINTED;
        });

        $allSuccess = true;
        $status = [];
        if(!empty($notPrintedParcels)){
            $parcelsString = implode(',', array_column($notPrintedParcels, 'number'));
            $return = $this->pushDeleteParcelsJob($parcelsString, $order->id);
//            $return = $this->sendDeleteRequest($parcelsString, $order->id);
//            if($return['success'] == false){
//                $allSuccess = false;
//                $status[] = 'Delete request - ' . $return['status'];
//            }
        }
        if(!empty($printedParcels)){
            $parcelsString = implode(',', array_column($printedParcels, 'number'));
            $return = $this->pushCancelParcelsJob($parcelsString, $order->id);
//            $return = $this->sendCancelRequest($parcelsString, $order->id);
//            if($return['success'] == false){
//                $allSuccess = false;
//                $status[] = 'Cancel request - ' . $return['status'];
//            }
        }

//        $statusString = implode(', ', $status);
//
//        if(!$allSuccess){
//            return [
//                'success' => false,
//                'status' => $statusString,
//                'errorType' => 'api',
//            ];
//        }

        $order->setFieldValue($this->getOrderShippingField()->handle, null);
        $save = Craft::$app->elements->saveElement($order);
        if(!$save){
            return [
                'success' => false,
                'status' => implode(' ', $order->getErrorSummary(true)),
                'errorType' => 'Order validation',
            ];
        }
        return [
            'success' => true,
//            'status' => $statusString,
            'status' => 'ok',
        ];
    }

    public function pushCancelParcelsJob($parcelsString, $orderId)
    {
        Common::addLog('push cancel parcels job', 'dpd-easyship');
        Queue::push(new \craftsnippets\dpdeasyship\jobs\CancelParcelsJob(
            [
                'orderId' => $orderId,
                'parcelsString' => $parcelsString,
            ]
        ));
    }

    public function pushDeleteParcelsJob($parcelsString, $orderId)
    {
        Common::addLog('push delete parcels job', 'dpd-easyship');
        Queue::push(new \craftsnippets\dpdeasyship\jobs\DeleteParcelsJob(
            [
                'orderId' => $orderId,
                'parcelsString' => $parcelsString,
            ]
        ));
    }

    private function addLogForRequest($label, $request, $response, $orderId = null)
    {
        $log = [
            'label' => $label,
            'orderId' => $orderId ?? null,
            'request' => $request->getData(),
            'response' => $response->getData(),
        ];
        Common::addLog($log, 'dpd-easyship');
    }

    public function sendDeleteRequest($parcelsString, $orderId)
    {
        Common::addLog('starting send delete request', 'dpd-easyship');
        $request = new ParcelDeleteRequest($this->getApiObject());
        $request->parcels = $parcelsString;
        try {
            $response = $request->send();
        } catch (\DataLinx\DPD\Exceptions\ValidationException $exception) {
            var_dump($exception);
            exit();
        } catch (\DataLinx\DPD\Exceptions\APIException $exception) {
            var_dump($exception);
            exit();
        } catch (\Exception $exception) {
            var_dump($exception);
            exit();
        }

        $this->addLogForRequest('Delete parcel request', $request, $response, $orderId);

        return [
            'success' => $response->isSuccessful(),
            'status' => $response->getStatus(),
        ];
    }

    public function sendCancelRequest($parcelsString, $orderId)
    {
        Common::addLog('starting send cancel request', 'dpd-easyship');
        $request = new ParcelCancelRequest($this->getApiObject());
        $request->parcels = $parcelsString;
        try {
            $response = $request->send();
        } catch (\DataLinx\DPD\Exceptions\ValidationException $exception) {
            var_dump($exception);
            exit();
        } catch (\DataLinx\DPD\Exceptions\APIException $exception) {
            var_dump($exception);
            exit();
        } catch (\Exception $exception) {
            var_dump($exception);
            exit();
        }

        $this->addLogForRequest('Cancel parcel request', $request, $response, $orderId);

        return [
            'success' => $response->isSuccessful(),
            'status' => $response->getStatus(),
        ];
    }

    public function hasSettings(): bool
    {
        $settings = DpdEasyShip::getInstance()->getSettings();
        if($settings->apiLogin && $settings->apiPassword && $settings->apiCountry){
            return true;
        }
        return false;
    }

    public function printLabels($orderIds)
    {
        $orders = Order::find()->id($orderIds)->all();
        if(empty($orders)){
            return false;
        }
        $parcels = [];
        foreach($orders as $singleOrder) {
            $parcels = array_merge($parcels, $singleOrder->dpdEasyship->parcels);
        }
        if(empty($parcels)){
            return false;
        }

        $parcelNumbers = array_column($parcels, 'number');

        $request = new ParcelPrintRequest($this->getApiObject(ApiPdf::class));
        $request->parcels = $parcelNumbers;
        $request->orderIds = $orderIds;

        try {
            $response = $request->send();
//            $this->updateParcelsStatus($orders[0]);
            var_dump('1');
            echo '<pre>';
            var_dump($response);
            echo '</pre>';
            exit();
        } catch (\DataLinx\DPD\Exceptions\ValidationException $exception) {
            var_dump('2');
            var_dump($exception);
            exit();
        } catch (\DataLinx\DPD\Exceptions\APIException $exception) {
            echo '<pre>';
            var_dump('3');
            var_dump($exception->getMessage());
            echo '</pre>';
            exit();
        } catch (\Exception $exception) {
            var_dump('4');
            var_dump($exception);
            exit();
        }
    }

    public function updateParcelsStatus($order)
    {
        Common::addLog('update order ID ' . $order->id);
//        dump($myOrderQuery = \craft\commerce\elements\Order::find()->id($order->id)->one()->dpdEasyship->parcels);
//        exit();

        $parcels = [];
        $allStatusDelivered = true;
        foreach ($order->dpdEasyship->parcels as $parcel) {
            $status = $this->getParcelStatus($parcel->number);
            $parcel->status = $status;
            $parcels[] = $parcel;
            if($parcel->status != DpdEasyShipData::STATUS_DELIVERED){
                $allStatusDelivered = false;
            }
        }

        // check if delivered, if yes set prtoper order status
        $deliveredOrderStatusId = DpdEasyShip::getInstance()->getSettings()->deliveredOrderStatusId;
        if($allStatusDelivered && !is_null($deliveredOrderStatusId)){
            $statusExists = CommercePlugin::getInstance()->getOrderStatuses()->getOrderStatusById($deliveredOrderStatusId);
            if(!is_null($deliveredOrderStatusId)){
                $order->orderStatusId = $deliveredOrderStatusId;
            }
        }

        $order->dpdEasyship->parcels = $parcels;
        $fieldContent = $order->dpdEasyship->encodeData();
        $order->setFieldValue($this->getOrderShippingField()->handle, $fieldContent);
        return Craft::$app->elements->saveElement($order);
    }

    public function getParcelStatus($parcelNumber)
    {
        $request = new ParcelStatusRequest($this->getApiObject());
        $request->parcelNumber = $parcelNumber;

        try {
            $response = $request->send();
        } catch (\DataLinx\DPD\Exceptions\ValidationException $exception) {
            var_dump($exception);
            exit();
        } catch (\DataLinx\DPD\Exceptions\APIException $exception) {
            echo '<pre>';
            var_dump($exception);
            echo '</pre>';
            exit();
        } catch (\Exception $exception) {
            var_dump($exception);
            exit();
        }

        return $response->getParcelStatus();
    }

    public function getOrdersPdfUrl(array $orders)
    {
        $orderIds = array_column($orders, 'id');
        return UrlHelper::actionUrl('dpd-easy-ship/api/print-labels', [
            'orderIds' => $orderIds,
        ]);
    }

    public function getPhoneField()
    {
        $fieldId = DpdEasyShip::getInstance()->getSettings()->phoneFieldId;
        if(!$fieldId){
            return null;
        }
        // if field is assigned to address field layout

        $field = Craft::$app->getFields()->getFieldById($fieldId);
        if(!$field){
            return null;
        }
        return $field;
    }

    public function getInstructionsField()
    {
        $fieldId = DpdEasyShip::getInstance()->getSettings()->instructionsFieldId;
        if(!$fieldId){
            return null;
        }
        $field = Craft::$app->getFields()->getFieldById($fieldId);
        if(!$field){
            return null;
        }
        return $field;
    }

    public function getParcelTypeOptions()
    {
        $options = [
            [
                'label' => Craft::t('dpd-easy-ship', 'DPD Classic'),
                'value' => ParcelType::CLASSIC,
            ],
            [
                'label' => Craft::t('dpd-easy-ship', 'DPD Classic COD'),
                'value' => ParcelType::CLASSIC_COD,
            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'DPD Classic Document return'),
//                'value' => ParcelType::CLASSIC_DOCUMENT_RETURN,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'DPD Home (B2C)'),
//                'value' => ParcelType::HOME_B2C,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'DPD Home COD'),
//                'value' => ParcelType::HOME_COD,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'Exchange'),
//                'value' => ParcelType::EXCHANGE,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'Tyre'),
//                'value' => ParcelType::TYRE,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'Tyre (B2C)'),
//                'value' => ParcelType::TYRE_B2C,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'Parcel shop'),
//                'value' => ParcelType::PARCEL_SHOP,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'Pallet'),
//                'value' => ParcelType::PALLET,
//            ],
//            [
//                'label' => Craft::t('dpd-easy-ship', 'DPD Home COD with return label'),
//                'value' => ParcelType::HOME_COD_RETURN,
//            ],
        ];

        return $options;
    }

    public function getCodTypeOptions()
    {
        $options = [
            [
                'label' => Craft::t('dpd-easy-ship', 'Average - the amount of each parcel will be the average amount of the total COD amount'),
                'value' => ParcelCODType::AVERAGE,
            ],
            [
                'label' => Craft::t('dpd-easy-ship', 'All - all parcels have the same amount which is the total COD amount'),
                'value' => ParcelCODType::ALL,
            ],
            [
                'label' => Craft::t('dpd-easy-ship', 'First only - only the first parcel will have the COD amount and the other parcels will be DPD Classic parcels'),
                'value' => ParcelCODType::FIRST_ONLY,
            ],
        ];
        return $options;
    }

    public function getOrderQueryForUpdate()
    {
        $settings = DpdEasyShip::getInstance()->getSettings();
        $field = $this->getOrderShippingField();
        if(is_null($field)){
            return null;
        }
        $query = Order::find();

        // has parcel
        $query = $query->{$field->handle}(':notempty:');

        // without status delivered
        if(!is_null($settings->deliveredOrderStatusId)){
            $query = $query->orderStatusId(['not', $settings->deliveredOrderStatusId]);
        }

        // todo not older tha month

        return $query;
    }

    public function updateAllOrdersParcels()
    {
        if(is_null($this->getOrderQueryForUpdate())){
            Common::addLog('Dpd Easyship field missing from order field layout.', 'dpd-easyship');
            return;
        }
        $orderIds = $this->getOrderQueryForUpdate()->ids();
        $this->pushUpdateStatusJob($orderIds);
    }

    public function pushUpdateStatusJob($orderIds)
    {
        Common::addLog('Pushed update parcels job', 'dpd-easyship');
        Queue::push(new \craftsnippets\dpdeasyship\jobs\UpdateParcelsStatusJob([
            'orderIds' => $orderIds,
        ]));
    }

    public function getLocationOptions()
    {
        $options = [
            [
                'label' => Craft::t('dpd-easy-ship', 'Select'),
                'value' => null,
            ]
        ];
        $inventoryLocations = CommercePlugin::getInstance()->getInventoryLocations()->getAllInventoryLocations();
        $options = array_merge($options, array_map(function($location){
            return [
                'label' => $location->name,
                'value' => $location->id,
            ];
        }, $inventoryLocations->toArray()));
        return $options;
    }

    const PHONE_MAX = 30;
    const DELIVERY_INSTRUCTIONS_MAX = 50;

    public function addValidationRules()
    {
        $phoneField = $this->getPhoneField();
        if(!is_null($phoneField)){
            Event::on(
                Address::class,
                Address::EVENT_DEFINE_RULES,
                function(DefineRulesEvent $event) {
                    $field = DpdEasyShip::getInstance()->easyShip->getPhoneField();
                    $event->rules[] = ['field:'.$field->handle, 'string', 'max' => self::PHONE_MAX];
                }
            );
        }

        $instructionsField = $this->getInstructionsField();
        if(!is_null($instructionsField)){
            Event::on(
                Order::class,
                Order::EVENT_DEFINE_RULES,
                function(DefineRulesEvent $event) {
                    $field = DpdEasyShip::getInstance()->easyShip->getInstructionsField();
                    $event->rules[] = ['field:'.$field->handle, 'string', 'max' => self::DELIVERY_INSTRUCTIONS_MAX];
                }
            );
        }


    }

}
