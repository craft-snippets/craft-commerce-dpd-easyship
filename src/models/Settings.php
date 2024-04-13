<?php

namespace craftsnippets\dpdeasyship\models;

use Craft;
use craft\base\Model;
use craft\commerce\Plugin as CommercePlugin;
use craft\elements\Address;
use craft\commerce\elements\Order;
use craft\fields\PlainText;

use craftsnippets\dpdeasyship\DpdEasyShip;
use DataLinx\DPD\ParcelCODType;
use DataLinx\DPD\ParcelType;

/**
 * DPD EasyShip settings
 */
class Settings extends Model
{

    const COUNTRY_CROATIA = 'HR';
    const COUNTRY_SLOVENIA = 'SI';

    public $apiLogin;
    public $apiPassword;
    public $apiCountry = self::COUNTRY_CROATIA;
    public $phoneFieldId;
    public $instructionsFieldId;
    public $enabledShippingMethods = [];
    public $codType = ParcelCODType::AVERAGE;
    public ?int $deliveredOrderStatusId = null;

    public ?int $defaultLocationId = null;

    // debug
    public $reloadOnRequest = true;
    public $hideField = true;

    public function getPhoneFieldOptions()
    {
        $fields = Craft::$app->getFields()->getLayoutByType(Address::class)->getCustomFields();
        $properFields = array_filter($fields, function($single){
            return get_class($single) == PlainText::class;
        });
        $options = [
            [
                'label' => Craft::t('dpd-easy-ship', 'Select'),
                'value' => null,
            ]
        ];
        foreach($properFields as $single){
            $options[] = [
                'label' => $single->name,
                'value' => $single->id,
            ];
        }
        return $options;
    }

    public function getDeliveryInstructionsFieldOptions()
    {
        $fields = Craft::$app->getFields()->getLayoutByType(Order::class)->getCustomFields();
        $properFields = array_filter($fields, function($single){
            return get_class($single) == PlainText::class;
        });
        $options = [
            [
                'label' => Craft::t('dpd-easy-ship', 'Select'),
                'value' => null,
            ]
        ];
        foreach($properFields as $single){
            $options[] = [
                'label' => $single->name,
                'value' => $single->id,
            ];
        }
        return $options;
    }

    public function getShippingMethodsColumns()
    {
        $shippingMethods = CommercePlugin::getInstance()->getShippingMethods()->getAllShippingMethods();
        $shippingMethodsOptions = $shippingMethods->map(function ($shippingMethod) {
            return [
                'label' => $shippingMethod->name,
                'value' => $shippingMethod->id,
            ];
        });
        $parcelTypeOptions = DpdEasyShip::getInstance()->easyShip->getParcelTypeOptions();
        $columns = [
            'shippingMethodId' => [
                'heading' => Craft::t('dpd-easy-ship', 'Shipping method'),
                'type' => 'select',
                'options' => $shippingMethodsOptions,

            ],
            'parcelType' => [
                'heading' => Craft::t('dpd-easy-ship', 'Parcel type'),
                'type' => 'select',
                'options' => $parcelTypeOptions,
            ],
        ];
        return $columns;
    }

    public function getCountryOptions()
    {
        return [
            [
                'label' => Craft::t('dpd-easy-ship', 'Croatia'),
                'value' => self::COUNTRY_CROATIA,
            ],
            [
                'label' => Craft::t('dpd-easy-ship', 'Slovenia'),
                'value' => self::COUNTRY_SLOVENIA,
            ],
        ];
    }

    public function getDeliveredOrderStatusIdOptions()
    {
        $options = [
            [
                'label' => Craft::t('dpd-easy-ship', 'Select'),
                'value' => null,
            ]
        ];
        $statuses = CommercePlugin::getInstance()->getOrderStatuses()->getAllOrderStatuses();
        $options = array_merge($options, array_map(function($status){
            return [
                'label' => $status->name,
                'value' => $status->id,
            ];
        }, $statuses->toArray()));
        return $options;
    }

    public function getLocationOptions()
    {
        // get plugin service
        $options = DpdEasyShip::getInstance()->easyShip->getLocationOptions();
        return $options;
    }

}
