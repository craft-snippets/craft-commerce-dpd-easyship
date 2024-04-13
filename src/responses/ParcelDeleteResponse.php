<?php

namespace craftsnippets\dpdeasyship\responses;

use DataLinx\DPD\Responses\ResponseInterface;
use DataLinx\DPD\Responses\AbstractResponse;

class ParcelDeleteResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return is_null($this->getParameter('errlog'));
    }
    public function getStatus(): ?string
    {
        return $this->getParameter('status');
    }

    public function getError(): ?string
    {
        return $this->getParameter('errlog');
    }

    public function getDetails(): ?string
    {
        return $this->getStatus() . ' - ' . $this->getError();
    }
}