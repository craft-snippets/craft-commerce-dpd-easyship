<?php

namespace craftsnippets\dpdeasyship\requests;

use DataLinx\DPD\Requests\AbstractRequest;
use craftsnippets\dpdeasyship\responses\ParcelStatusResponse;
use DataLinx\DPD\Responses\ResponseInterface;
class ParcelStatusRequest extends AbstractRequest
{
    public string $parcelNumber;

    public function send(): ResponseInterface
    {
        return new ParcelStatusResponse($this->sendRequest(), $this);
    }

    public function getData(): array
    {
        $data = [
            'parcel_number' => $this->parcelNumber,
            // this code was included in documentation and is required for some reason
            'secret' => 'FcJyN7vU7WKPtUh7m1bx',
        ];
        return $data;
    }

    public function getEndpoint(): string
    {
        return 'parcel/parcel_status';
    }

    public function validate(): void
    {

    }

}