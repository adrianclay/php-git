<?php
namespace adrianclay\git\Pack;

use adrianclay\git\Pack;

class CompleteObject implements \adrianclay\git\GitObject
{
    /** @var string */
    private $data;

    /** @var int */
    private $type;

    public function __construct( string $data, int $type) {
        $this->data = $data;
        $this->type = $type;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getType(): string
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