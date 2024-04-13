<?php

namespace craftsnippets\dpdeasyship\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\helpers\Queue;
use craftsnippets\dpdeasyship\DpdEasyShip;

/**
 * Update Parcels Status Action element action
 */
class UpdateParcelsStatusAction extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('dpd-easy-ship', 'DPD EasyShip - update parcels status');
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
                                    if(single.querySelector('[data-dpd-easyship-label-allowed]') == null){
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
        $orders = $query->all();
        foreach ($orders as $order){
            DpdEasyShip::getInstance()->easyShip->updateParcelsStatus($order);
        }
        // cant use queue, need to refresh orders list only after all api calls end during one request

//        $orderIds = $query->ids();
//        Queue::push(new \craftsnippets\dpdeasyship\jobs\UpdateParcelsStatusJob([
//            'orderIds' => $orderIds,
//        ]));
        return true;
    }

    public function  getMessage(): string
    {
        return Craft::t('dpd-easy-ship', 'DPD EasyShip parcels status updated for the selected orders.');
    }
}
