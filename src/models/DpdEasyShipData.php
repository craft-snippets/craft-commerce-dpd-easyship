<?php

namespace craftsnippets\dpdeasyship\models;

use craft\base\Model;
use craft\commerce\elements\Order;
use craftsnippets\dpdeasyship\DpdEasyShip;
use DataLinx\DPD\ParcelType;

class DpdEasyShipData extends Model
{

    const STATUS_PRINTED = 'PRINTED';
    const STATUS_CREATED = 'CREATED';
    const STATUS_DELIVERED = 'DELIVERED';


    public Order $order;

    public $jsonData;
    public $parcels = [];

    // assigned after saving - data of parcel creation request
    public $name1;
    public $name2 = null;
    public $contact = null;
    public $street;
    public $rPropNum = null;
    public $city;
    public $country;
    public $pcode;
    public $email = null;
    public $phone = null;
    public $sender_remark = null;
    public $weight = null;
    public $num_of_parcel;
    public $order_number = null;
    public $order_number2 = null;
    public $parcel_type;
    public $parcel_cod_type = null;
    public $cod_amount = null;
    public $cod_purpose = null;
    public $predict = null;
    public $is_id_check = null;
    public $id_check_receiver = null;
    public $id_check_num = null;
    public $sender_name = null;
    public $sender_city = null;
    public $sender_pcode = null;
    public $sender_country = null;
    public $sender_street = null;
    public $sender_phone = null;
    public $pudo_id = null;
    public $length = null;
    public $width = null;
    public $height = null;

    public static function getJsonProperties()
    {
        return [
            [
                'value' => 'name1',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver company or personal name'),
            ],
            [
                'value' => 'name2',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver additional name (if needed)'),
            ],
            [
                'value' => 'contact',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver contact person'),
            ],
            [
                'value' => 'street',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver street'),
            ],
            [
                'value' => 'rPropNum',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver house number (case-sensitive)'),
            ],
            [
                'value' => 'city',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver city'),
            ],
            [
                'value' => 'country',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver country code (ISO2 standard)'),
            ],
            [
                'value' => 'pcode',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver postal code'),
            ],
            [
                'value' => 'email',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver E-mail address, mandatory for PUDO and export parcels'),
            ],
            [
                'value' => 'phone',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver phone number, mandatory for PUDO and export parcels'),
            ],
            [
                'value' => 'sender_remark',
                'label' => \Craft::t('dpd-easy-ship', 'Delivery instructions for courier'),
            ],
            [
                'value' => 'weight',
                'label' => \Craft::t('dpd-easy-ship', 'Parcel weight (kg), mandatory for PUDO parcels'),
            ],
            [
                'value' => 'num_of_parcel',
                'label' => \Craft::t('dpd-easy-ship', 'Number of parcel labels to be generated'),
            ],
            [
                'value' => 'order_number',
                'label' => \Craft::t('dpd-easy-ship', 'Customer’s parcel reference'),
            ],
            [
                'value' => 'order_number2',
                'label' => \Craft::t('dpd-easy-ship', 'Customer’s additional parcel reference'),
            ],
            [
                'value' => 'parcel_type',
                'label' => \Craft::t('dpd-easy-ship', 'Parcel type'),
            ],
            [
                'value' => 'parcel_cod_type',
                'label' => \Craft::t('dpd-easy-ship', 'Type of the Cash On Delivery amount splitting'),
            ],
            [
                'value' => 'cod_amount',
                'label' => \Craft::t('dpd-easy-ship', 'Cash On Delivery amount'),
            ],
            [
                'value' => 'cod_purpose',
                'label' => \Craft::t('dpd-easy-ship', 'Customer’s COD reference'),
            ],
            [
                'value' => 'predict',
                'label' => \Craft::t('dpd-easy-ship', 'Predict notification'),
            ],
            [
                'value' => 'is_id_check',
                'label' => \Craft::t('dpd-easy-ship', 'ID check'),
            ],
            [
                'value' => 'id_check_receiver',
                'label' => \Craft::t('dpd-easy-ship', 'Name of person for ID check'),
            ],
            [
                'value' => 'id_check_num',
                'label' => \Craft::t('dpd-easy-ship', 'Receiver ID check document number'),
            ],
            [
                'value' => 'sender_name',
                'label' => \Craft::t('dpd-easy-ship', 'Sender company name on the label'),
            ],
            [
                'value' => 'sender_city',
                'label' => \Craft::t('dpd-easy-ship', 'Sender city on the label'),
            ],
            [
                'value' => 'sender_pcode',
                'label' => \Craft::t('dpd-easy-ship', 'Sender postal code on the label'),
            ],
            [
                'value' => 'sender_country',
                'label' => \Craft::t('dpd-easy-ship', 'Sender country on the label'),
            ],
            [
                'value' => 'sender_street',
                'label' => \Craft::t('dpd-easy-ship', 'Sender street on the label'),
            ],
            [
                'value' => 'sender_phone',
                'label' => \Craft::t('dpd-easy-ship', 'Sender phone number on the label'),
            ],
            [
                'value' => 'pudo_id',
                'label' => \Craft::t('dpd-easy-ship', 'PUDO ID'),
            ],
            [
                'value' => 'length',
                'label' => \Craft::t('dpd-easy-ship', 'Length (cm)'),
            ],
            [
                'value' => 'width',
                'label' => \Craft::t('dpd-easy-ship', 'Width (cm)'),
            ],
            [
                'value' => 'height',
                'label' => \Craft::t('dpd-easy-ship', 'Height (cm)'),
            ],
        ];
    }

    public function getSavedProperty($property)
    {
        $value = $this->{$property} ?? null;
        if(is_null($value)){
            return null;
        }
        switch($property){
            case 'parcel_type':
                $parcelTypeOptions = DpdEasyShip::getInstance()->easyShip->getParcelTypeOptions();
                foreach($parcelTypeOptions as $option){
                    if($option['value'] == $value){
                        return $option['label'];
                    }
                }
                return null;

            default:
                return $value;
        }
    }

    public function init(): void
    {
        // decode from field value only if json was provided
        if(is_null($this->jsonData)){
            return;
        }
        $data = json_decode($this->jsonData, true);

        // assign parcels
        if(isset($data['parcels']) && is_array($data['parcels'])){
            $parcels = [];
            foreach ($data['parcels'] as $parcelInArray) {
                if(!isset($parcelInArray['number'])){
                    continue;
                }
                $parcel = new DpdEasyshipParcel(
                    [
                        'number' => $parcelInArray['number'],
                        'status' => $parcelInArray['status'] ?? null,
                        'order' => $this->order,
                    ]
                );
                $parcels[] = $parcel;
            }
            $this->parcels = $parcels;
        }
//        exit();
//        var_dump($data);
        // assign json properties
        foreach ($this->getJsonProperties() as $single) {
            $property = $single['value'];
            if(isset($data[$property])){
                $this->{$property} = $data[$property];
//                var_dump($this->{$property});
            }
        }
    }

    private function getPluginSettings()
    {
        return DpdEasyShip::getInstance()->getSettings();
    }

    public function encodeData()
    {
        $parcels = array_map(function($single){
            return [
                'number' => $single->number,
                'status' => $single->status,
            ];
        }, $this->parcels);
        $array = [
            'parcels' => $parcels,
        ];
        foreach ($this->getJsonProperties() as $single) {
            $property = $single['value'];
            $array[$property] = $this->{$property};
        }
        return json_encode($array);
    }

    public function getIndexColumnStatusesSummary()
    {
        if(empty($this->parcels)){
            return '';  // element index cannot use null
        }
        $statuses = array_column($this->parcels, 'status');
        $statuses = implode(', ', $statuses);
        return $statuses;
    }

    public function getShippingMethodAllowed()
    {
        $methodsIds = array_column($this->getPluginSettings()->enabledShippingMethods, 'shippingMethodId');
        $order = $this->order;
        if(is_null($order->getShippingMethod()) || !in_array($order->getShippingMethod()->id, $methodsIds)){
            return false;
        }
        return true;
    }

    public function getCanUse()
    {
        $order = $this->order;
        // don't add to cart page in the control panel
        if($order->isCompleted == false){
            return false;
        }
        if($this->getShippingMethodAllowed() == false){
            return false;
        }
        return true;
    }

    public function getHasParcels()
    {
        return !empty($this->parcels);
    }

    // before override when creating parcel
    public function getDeliveryInstructions()
    {
        $order = $this->order;
        $field = DpdEasyShip::getInstance()->easyShip->getInstructionsField();
        if(is_null($field)){
            return null;
        }
        return $order->getFieldValue($field->handle);
    }

    public function getDefaultParcelType()
    {
        $settingsShippingMethods = $this->getPluginSettings()->enabledShippingMethods;
        $orderShippingMethod = $this->order->getShippingMethod();
        if(is_null($orderShippingMethod) || empty($settingsShippingMethods)){
            return null;
        }

        $parcelType = null;
        foreach($settingsShippingMethods as $option){
            if($option['shippingMethodId'] == $orderShippingMethod->id){
                $parcelType = $option['parcelType'];
                break;
            }
        }
        return $parcelType;
    }

    public function getDefaultParcelTyPeLabel()
    {
        $parcelType = $this->getDefaultParcelType();
        if(is_null($parcelType)){
            return null;
        }
        $parcelTypeOptions = DpdEasyShip::getInstance()->easyShip->getParcelTypeOptions();
        foreach($parcelTypeOptions as $option){
            if($option['value'] == $parcelType){
                return $option['label'];
            }
        }
        return null;
    }

    public function getCurrentParcelTypeLabel()
    {
        $parcelType = $this->parcel_type;
        if(is_null($parcelType)){
            return null;
        }
        $parcelTypeOptions = DpdEasyShip::getInstance()->easyShip->getParcelTypeOptions();
        foreach($parcelTypeOptions as $option){
            if($option['value'] == $parcelType){
                return $option['label'];
            }
        }
        return null;
    }

    public function isCod()
    {
        $codTypes = [
            ParcelType::CLASSIC_COD,
            ParcelType::HOME_COD,
            ParcelType::HOME_COD_RETURN,
        ];
        return in_array($this->parcel_type, $codTypes);
    }

    public function assignRequestData($request)
    {
        foreach($this->getJsonProperties() as $single){
            $property = $single['value'];
            $this->{$property} = $request->{$property};
        }
    }

    // only used in template to hide button, not in controller
    public function canRemoveParcels()
    {
        $canRemove = true;
        foreach ($this->parcels as $parcel) {
            if($parcel->status != self::STATUS_PRINTED && $parcel->status != self::STATUS_CREATED){
                $canRemove = false;
            }
        }
        return $canRemove;
    }

    public function createParcelsActionAllowed()
    {
        if($this->getHasParcels() == true){
            return false;
        }
        if(DpdEasyShip::getInstance()->easyShip->orderHasShippingField() == false){
            return false;
        }
        if($this->getCanUse() == false){
            return false;
        }
        return true;
    }

    public function updateParcelsActionAllowed()
    {
        if($this->getHasParcels() == false){
            return false;
        }
        if(DpdEasyShip::getInstance()->easyShip->orderHasShippingField() == false){
            return false;
        }
        if($this->getCanUse() == false){
            return false;
        }
        return true;
    }

    public function getLabelActionAllowed()
    {
        if($this->getHasParcels() == false){
            return false;
        }
        if(DpdEasyShip::getInstance()->easyShip->orderHasShippingField() == false){
            return false;
        }
        if($this->getCanUse() == false){
            return false;
        }
        return true;
    }

}