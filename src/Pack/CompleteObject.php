<?php
namespace adrianclay\git\Pack;

use adrianclay\git\Pack;

class CompleteObject implements \adrianclay\git\GitObject
{
    /** @var string */
    private $data;

    /** @var int */
    private $type;

    /**
     * @param string $data
     * @param int    $type
     */
    public function __construct( $data, $type) {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getType()
    {
        switch( $this->type ) {
            case Pack::TYPE_COMMIT:
                return self::TYPE_COMMIT;
            case Pack::TYPE_TREE:
                return self::TYPE_TREE;
            case Pack::TYPE_BLOB:
                return self::TYPE_BLOB;
        }
    }
}