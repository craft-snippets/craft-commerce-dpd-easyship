<?php

namespace craftsnippets\dpdeasyship\jobs;

use Craft;
use craft\queue\BaseJob;
use craftsnippets\dpdeasyship\DpdEasyShip;
use craftsnippets\dpdeasyship\helpers\Common;

class CancelParcelsJob extends BaseJob
{
    public int $orderId;
    public string $parcelsString;
    function execute($queue): void
    {
        try {
            DpdEasyShip::getInstance()->easyShip->sendCancelRequest($this->parcelsString, $this->orderId);
        } catch (\Exception $e) {
            Common::addLog('cancel parcels request fail', 'dpd-easyship');
            $exceptionData = array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            );
            $jsonExceptionData = json_encode($exceptionData);
            Common::addLog($jsonExceptionData, 'dpd-easyship');
        }
    }

    protected function defaultDescription(): ?string
    {
        return Craft::t('dpd-easy-ship', 'DPD EasyShip cancel parcels request');
    }
}