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

    /**
     * @param int $mode
     * @param string $filename
     * @param SHA $shaHash
     */
    public function __construct( $mode, $filename, SHA $shaHash )
    {
        $this->mode = $mode;
        $this->filename = $filename;
        $this->shaHash = $shaHash;
    }

    /**
     * @return bool
     */
    public function isTree()
    {
        return $this->mode < 100000;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getSHA()
    {
        return $this->shaHash->getSHA();
    }


}