<?php

namespace craftsnippets\dpdeasyship\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\web\Controller;
use craftsnippets\dpdeasyship\requests\ParcelPrintRequest;
use yii\web\Response;
use craftsnippets\dpdeasyship\DpdEasyShip;
use craftsnippets\dpdeasyship\jobs\CreateParcels;

/**
 * Api Controller controller
 */
class ApiController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * _dpd-easy-ship/api-controller action
     */



    public function actionCreateParcel()
    {
        $this->requirePermission('manageDpdEasyship');

        $orderId = Craft::$app->getRequest()->getRequiredBodyParam('orderId');
        $requestSettings = Craft::$app->getRequest()->getRequiredBodyParam('requestSettings');
        $requestSettings = json_decode($requestSettings, true);
        $order = Order::find()->id($orderId)->one();

        // can't create parcels if shipping method not allowed
        if($order->dpdEasyship->getCanUse() == false){
            return $this->asJson([
                'success' => false,
                'error' => 'Shipping method not allowed',
            ]);
        }
        if(is_null($order)){
            return $this->asJson([
                'success' => false,
                'error' => 'Order not found',
            ]);
        }
        $result = DpdEasyShip::getInstance()->easyShip->createParcels($order, $requestSettings);
        return $this->asJson([
            'success' => $result['success'],
            'error' => $result['error'] ?? null,
            'errorType' => $result['errorType'] ?? null,
        ]);
    }

    public function actionRemoveParcels()
    {
        $this->requirePermission('manageDpdEasyship');

        $orderId = Craft::$app->getRequest()->getRequiredBodyParam('orderId');
        $order = Order::find()->id($orderId)->one();
        if(is_null($order)){
            return $this->asJson([
                'success' => false,
            ]);
        }
        $result = DpdEasyShip::getInstance()->easyShip->removeParcels($order);
        return $this->asJson([
            'success' => $result['success'],
            'error' => $result['error'] ?? null,
            'errorType' => $result['errorType'] ?? null,
            'status' => $result['status'] ?? null,
        ]);
    }

    public function actionPrintLabels()
    {
        $this->requirePermission('manageDpdEasyship');

        $orderIds = Craft::$app->getRequest()->getRequiredQueryParam('orderIds');
        $result = DpdEasyShip::getInstance()->easyShip->printLabels($orderIds);
        return $this->asJson([
            'success' => $result,
        ]);
    }

    public function actionUpdateParcelsStatus()
    {
        $this->requirePermission('manageDpdEasyship');

        $orderId = Craft::$app->getRequest()->getRequiredBodyParam('orderId');
        $order = Order::find()->id($orderId)->one();
        if(is_null($order)){
            return $this->asJson([
                'success' => false,
            ]);
        }
        $result = DpdEasyShip::getInstance()->easyShip->updateParcelsStatus($order);
        return $this->asJson([
            'success' => $result,
        ]);
    }

    public function actionPushParcelsStatusesUpdateJob()
    {
        $this->requirePermission('manageDpdEasyship');

        DpdEasyShip::getInstance()->easyShip->updateAllOrdersParcels();
        Craft::$app->getSession()->setNotice(Craft::t('dpd-easy-ship', 'DPD EasyShip update parcels queue job started.'));
        return $this->redirect('utilities/dpd-easyship-utility');
    }

}
