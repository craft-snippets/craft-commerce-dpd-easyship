<?php
namespace craftsnippets\dpdeasyship\console\controllers;

use craft\console\Controller;
use craftsnippets\dpdeasyship\DpdEasyShip;
use craftsnippets\dpdeasyship\helpers\Common;
use yii\console\ExitCode;
use craft\helpers\Console;

class ParcelsController extends Controller
{
    public function actionUpdateParcelsStatuses()
    {
        Common::addLog('Console command - update parcels statuses', 'dpd-easyship');
        DpdEasyShip::getInstance()->easyShip->updateAllOrdersParcels();
        $this->stdout("Updating parcel statuses..". PHP_EOL);
        return ExitCode::OK;
    }
}