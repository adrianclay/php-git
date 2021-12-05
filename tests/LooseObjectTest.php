<?php
namespace adrianclay\git;

use PHPUnit\Framework\TestCase;

class LooseObjectTest extends TestCase
{
    /** @var LooseObject */
    private $looseObject;

    public function setUp(): void
    {
        $this->looseObject = new LooseObject( __DIR__ . '/fixtures/objects/802992c4220de19a90767f3000a79a31b98d0df7' );
    }

    public function testGetType()
    {
        $this->assertEquals( GitObject::TYPE_BLOB, $this->looseObject->getType() );
    }

    public function testGetData()
    {
        $this->assertEquals( "Hello world\n", $this->looseObject->getData() );
    }
}