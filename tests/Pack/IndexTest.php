<?php

namespace adrianclay\git\Pack;

use adrianclay\git\Repository;
use adrianclay\git\SHA;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $path = __DIR__ . '/../../';
        $this->repository = new Repository( $path );
    }

    public function testCanGetAOffsetOfZeroPrefixedSha() {
        $sha = new SHA( "00" . "f5270dc9fcb8ec4c31e04334128a4a9f596470" );
        $object = $this->repository->getObject( $sha );
        $this->assertNotNull( $object );
    }
}