<?php

namespace craftsnippets\dpdeasyship\models;

use Craft;
use craft\base\Model;
use craft\commerce\elements\Order;
use craftsnippets\dpdeasyship\models\DpdEasyShipData;

class DpdEasyshipParcel extends Model
{
    public int $number;
    public ?string $status;
    public Order $order;

    public function getTrackingUrl()
    {
        // if parcel status not null and not created
        if(is_null($this->status) || $this->status == DpdEasyShipData::STATUS_CREATED){
            return null;
        }
        $trackingUrl = 'https://www.dpdgroup.com/hr/mydpd/my-parcels/track?parcelNumber='.$this->number;
        return $trackingUrl;
    }

}
