<?php

namespace craftsnippets\dpdeasyship\jobs;

use Craft;
use craft\queue\BaseJob;
use craftsnippets\dpdeasyship\DpdEasyShip;
use craftsnippets\dpdeasyship\helpers\Common;

/**
 * Update Parcel Status Job queue job
 */
class UpdateParcelsStatusJob extends BaseJob
{
    public array $orderIds;
    function execute($queue): void
    {
        $orderIds = $this->orderIds;
        $query = \craft\commerce\elements\Order::find()->id($orderIds);
        $totalElements = $query->count();
        $currentElement = 0;

        try {
            $i = 0;
            foreach ($query->each() as $order) {
                $i ++;
                $this->setProgress($queue, $currentElement++ / $totalElements);
                try{
                    DpdEasyShip::getInstance()->easyShip->updateParcelsStatus($order);
                } catch(\Exception $e){
                    Common::addLog($e, 'dpd-easyship');
                }
            }
        } catch (\Exception $e) {
            // Fail silently
        }
    }

    protected function defaultDescription(): ?string
    {
        return Craft::t('dpd-easy-ship', 'Updating DPD EasyShip parcels statuses');
    }
}
