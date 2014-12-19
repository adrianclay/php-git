<?php

namespace adrianclay\git;

interface References extends \Traversable
{
    const HEAD = 'HEAD';

    /**
     * @param string $name
     * @return SHAReference
     */
    public function getReference( $name );

}