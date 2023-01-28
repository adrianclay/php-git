<?php
namespace adrianclay\git\Tree;

use adrianclay\git\SHA;
use adrianclay\git\SHAReference;

class Entry implements SHAReference
{
    /** @var int */
    private $mode;

    /** @var string */
    private $filename;

    /** @var \adrianclay\git\SHA */
    private $shaHash;

    public function __construct( int $mode, string $filename, SHA $shaHash )
    {
        $this->mode = $mode;
        $this->filename = $filename;
        $this->shaHash = $shaHash;
    }

    public function isTree(): bool
    {
        return $this->mode < 100000;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getSHA(): string
    {
        return $this->shaHash->getSHA();
    }


}