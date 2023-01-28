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

    public function __construct( SHAReference $from, SHAReference $to, Signature $signature, string $message )
    {
        $this->from = $from;
        $this->to = $to;
        $this->signature = $signature;
        $this->message = $message;
    }

    public function getFrom(): SHAReference
    {
        return $this->from;
    }

    public function getTo(): SHAReference
    {
        return $this->to;
    }

    public function getSignature(): Signature {
        return $this->signature;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

}