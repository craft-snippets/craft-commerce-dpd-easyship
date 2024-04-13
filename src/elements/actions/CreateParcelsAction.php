<?php

namespace craftsnippets\dpdeasyship\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\commerce\elements\Order;
use craftsnippets\dpdeasyship\jobs\CreateParcels;

/**
 * Create Parcels Action element action
 */
class CreateParcelsAction extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('dpd-easy-ship', 'DPD EasyShip - create parcels');
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($type) => <<<JS
            (() => {
                new Craft.ElementActionTrigger({
                    type: $type,
                    bulk: true,
                    validateSelection: (selectedItems) => {
                        var allowed = true;
                        // selectedItems is object instead of regular array
                        for (let key in selectedItems) {
                                if (!isNaN(parseInt(key))) {
                                    let single = selectedItems[key];
                                    if(single.querySelector('[data-dpd-easyship-create-allowed]') == null){
                                        allowed = false;
                                    }    
                                }
                        }                  
                        return allowed;
                    },
                });
            })();
        JS, [static::class]);
        return null;
    }

    public function performAction(Craft\elements\db\ElementQueryInterface $query): bool
    {
//        $orderIds = $query->ids();
//
//        Craft::$app->queue->push(new CreateParcels([
//            'orderIds' => $orderIds,
//        ]));
//        return true;

        $orders = $query->all();
        $successAll = true;
        $errors = [];
        foreach ($orders as $order){
            $result = \craftsnippets\dpdeasyship\DpdEasyShip::getInstance()->easyShip->createParcels($order);
            if($result['success'] == false){
                $successAll = false;
                $errors[] = $result['error'];
            }
        }

        if($successAll == true){
            $message = Craft::t('dpd-easy-ship', 'DPD EasyShip parcels created for the selected orders.');
        }else{
            $message = Craft::t('dpd-easy-ship', 'Could not create DPD EasyShip parcels for the all selected orders. Errors:');
            $errors = join(', ', $errors);
            $message = $message . ' ' . $errors;
        }

        $this->setMessage($message);
        return $successAll;
    }

    public function  getConfirmationMessage(): string
    {
        return Craft::t('dpd-easy-ship', 'Are you sure you want to create DPD EasyShip parcels for the selected orders? Default settings will be used for the each parcel.');
    }

//    public function  getMessage(): string
//    {
//        return ;
//    }
}
