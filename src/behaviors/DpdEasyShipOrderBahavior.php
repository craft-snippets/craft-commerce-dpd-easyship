<?php

namespace craftsnippets\dpdeasyship\behaviors;
use craftsnippets\dpdeasyship\DpdEasyShip;
use yii\base\Behavior;
use craftsnippets\dpdeasyship\models\DpdEasyShipData;

class DpdEasyShipOrderBahavior extends Behavior
{

    private $_easyShipData;

    public function getDpdEasyship()
    {
        if($this->_easyShipData !== null){
            return $this->_easyShipData;
        }

        $field = DpdEasyShip::getInstance()->easyShip->getOrderShippingField();
        if(is_null($field)){
            $jsonData = null;
        }else{
            $jsonData = $this->owner->getFieldValue($field->handle);
        }
        $obj = new DpdEasyShipData([
            'order' => $this->owner,
            'jsonData' => $jsonData,
        ]);
        $this->_easyShipData = $obj;
        return $obj;
    }

    // used after we create parcels but before page reloads
    public function reapplyDpdEasyship()
    {
        $this->_easyShipData = null;
        return $this->getDpdEasyship();
    }

}