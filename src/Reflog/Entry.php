<?php

namespace adrianclay\git\Reflog;

use adrianclay\git\SHAReference;
use adrianclay\git\Signature;

class Entry
{
    /** @var \adrianclay\git\SHAReference */
    private $from;
    /** @var \adrianclay\git\SHAReference */
    private $to;
    /** @var \adrianclay\git\Signature */
    private $signature;
    /** @var string */
    private $message;

    /**
     * @param SHAReference $from
     * @param SHAReference $to
     * @param Signature    $signature
     * @param string       $message
     */
    public function __construct( SHAReference $from, SHAReference $to, Signature $signature, $message )
    {
        $this->from = $from;
        $this->to = $to;
        $this->signature = $signature;
        $this->message = $message;
    }

    /**
     * @return SHAReference
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return SHAReference
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return Signature
     */
    public function getSignature() {
        return $this->signature;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

}