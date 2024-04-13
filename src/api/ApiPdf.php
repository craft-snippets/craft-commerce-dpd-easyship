<?php

namespace craftsnippets\dpdeasyship\api;

use DataLinx\DPD\API;
use DataLinx\DPD\Exceptions\APIException;

use Craft;
use craftsnippets\dpdeasyship\DpdEasyShip;

class ApiPdf extends API
{
    public $orderIds;
    public function sendRequest(string $endpoint, array $data): array
    {
        // update order statuses
        // todo - update status only for created parcels
        DpdEasyShip::getInstance()->easyShip->pushUpdateStatusJob($this->orderIds);

        $ch = curl_init($this->getUrl() . $endpoint . '?' . http_build_query($data));

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true,
            CURLOPT_ENCODING => 'UTF-8',
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $err_no = curl_errno($ch);

        curl_close($ch);

        if (empty($response)) {
            throw new APIException('DPD API request failed! cURL error: '. $error .' (err.no.: '. $err_no .', HTTP code: '. $code .')', $code);
        }
        if(json_decode($response) === null){
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: inline; filename="dpd_easyship_parcel_label.pdf"');
            echo $response;
        }else{
            $response = json_decode($response, true);
            echo $response['errlog'] ?? null;
        }
        exit();
    }
}