<?php

namespace craftsnippets\dpdeasyship\responses;

use DataLinx\DPD\Responses\AbstractResponse;
class ParcelPrintResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return true;
    }
}