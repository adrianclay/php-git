<?php

namespace adrianclay\git;

interface References extends \Traversable
{
    const HEAD = 'HEAD';

    public function getReference( string $name ): ?SHAReference;

}