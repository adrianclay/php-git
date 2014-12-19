<?php

namespace adrianclay\git\References;

use adrianclay\git\References;
use adrianclay\git\SHAReference;

class ReferencesArray implements \IteratorAggregate, References
{
    /** @var SHAReference[] */
    private $references;

    /**
     * @param SHAReference[] $references
     */
    public function __construct( array $references )
    {
        $this->references = $references;
    }

    public function getIterator()
    {
        return new \ArrayIterator( $this->references );
    }

    /**
     * @param string $name
     * @return SHAReference
     */
    public function getReference( $name )
    {
        if ( array_key_exists( $name, $this->references ) ) {
            return $this->references[$name];
        }
    }
}