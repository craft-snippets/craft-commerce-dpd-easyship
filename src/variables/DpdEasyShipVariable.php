<?php

namespace craftsnippets\dpdeasyship\variables;
use craftsnippets\dpdeasyship\DpdEasyShip;

class DpdEasyShipVariable
{
    public function getAddressPhoneField()
    {
        return DpdEasyShip::getInstance()->easyShip->getPhoneField();
    }

    public function getOrderInstructionsField()
    {
        return DpdEasyShip::getInstance()->easyShip->getInstructionsField();
    }

}