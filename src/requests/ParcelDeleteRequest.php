<?php

namespace craftsnippets\dpdeasyship\requests;

use craftsnippets\dpdeasyship\responses\ParcelDeleteResponse;
use DataLinx\DPD\Responses\ResponseInterface;
use DataLinx\DPD\Requests\AbstractRequest;

class ParcelDeleteRequest extends AbstractRequest
{
    public $parcels;
    public function send(): ResponseInterface
    {
        return new ParcelDeleteResponse($this->sendRequest(), $this);
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
        return 'parcel/parcel_delete';
    }

    public function validate(): void
    {

    }
}