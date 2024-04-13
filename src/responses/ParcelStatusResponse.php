<?php

namespace craftsnippets\dpdeasyship\responses;

use DataLinx\DPD\Responses\AbstractResponse;
class ParcelStatusResponse extends AbstractResponse
{

    public function isSuccessful(): bool
    {
        return true;
    }
    public function getParcelStatus(): string
    {
        return $this->getParameter('parcel_status');
    }
}