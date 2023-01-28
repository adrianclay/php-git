<?php

namespace adrianclay\git\References;

use adrianclay\git\References;
use adrianclay\git\SHAReference;

class SymbolicReference implements SHAReference
{
    /** @var References */
    private $refs;

    /** @var string */
    private $refLink;

    public function __construct( References $refs, string $refLink )
    {
        $this->refs = $refs;
        $this->refLink = $refLink;
    }

    public function getSHA(): string
    {
        return $this->refs->getReference( $this->refLink )->getSHA();
    }
}