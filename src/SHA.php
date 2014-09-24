<?php
namespace adrianclay\git;


class SHA implements SHAReference
{

    /** @var string */
    private $sha;

    /**
     * @param string $sha
     */
    public function __construct( $sha )
    {
        $this->sha = $sha;
    }

    /**
     * @return string
     */
    public function getSHA()
    {
        return $this->sha;
    }
}