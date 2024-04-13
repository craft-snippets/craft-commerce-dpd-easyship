<?php

namespace craftsnippets\dpdeasyship\requests;

use craftsnippets\dpdeasyship\responses\ParcelCancelResponse;
use DataLinx\DPD\Responses\ResponseInterface;
use DataLinx\DPD\Requests\AbstractRequest;

class ParcelCancelRequest extends AbstractRequest
{
    public $parcels;
    public function send(): ResponseInterface
    {
        return new ParcelCancelResponse($this->sendRequest(), $this);
    }

    public function getData(): array
    {
        $data = [
            'parcels' => $this->parcels,
        ];
        return $data;
    }

    public function getEndpoint(): string
    {
        return 'parcel/parcel_cancel';
    }

    public function validate(): void
    {

    }
}