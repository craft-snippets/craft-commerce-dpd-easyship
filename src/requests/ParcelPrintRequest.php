<?php

namespace craftsnippets\dpdeasyship\requests;

use DataLinx\DPD\Requests\AbstractRequest;
use craftsnippets\dpdeasyship\responses\ParcelPrintResponse;
use DataLinx\DPD\Responses\ResponseInterface;

class ParcelPrintRequest extends AbstractRequest
{
    public array $parcels;
    public array $orderIds;

    public function send(): ResponseInterface
    {
        $this->api->orderIds = $this->orderIds;
        return new ParcelPrintResponse($this->sendRequest(), $this);
    }

    public function getData(): array
    {
        $parcels = implode(',', $this->parcels);
        $data = [
            'parcels' => $parcels,
        ];
        return $data;
    }

    public function getEndpoint(): string
    {
        return 'parcel/parcel_print';
    }

    public function validate(): void
    {
        // TODO: Implement validate() method.
    }

}