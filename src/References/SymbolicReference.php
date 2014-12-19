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

    /**
     * @param References $refs
     * @param            $refLink
     */
    public function __construct( References $refs, $refLink )
    {
        $this->refs = $refs;
        $this->refLink = $refLink;
    }

    /**
     * @return string
     */
    public function getSHA()
    {
        return $this->refs->getReference( $this->refLink )->getSHA();
    }
}