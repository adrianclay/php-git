<?php
namespace adrianclay\git;


class SHA implements SHAReference
{

    /** @var string */
    private $sha;

    public function __construct( string $sha )
    {
        $this->sha = $sha;
    }

    public function getSHA(): string
    {
        return $this->sha;
    }
}