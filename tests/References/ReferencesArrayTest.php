<?php

namespace adrianclay\git\References;

use adrianclay\git\References;
use adrianclay\git\SHA;
use PHPUnit\Framework\TestCase;

class ReferencesArrayTest extends TestCase
{
    /** @var SHA */
    private $testSha;

    /** @var SHA[] */
    private $mapping;

    /** @var ReferencesArray */
    private $references;

    public function setUp(): void
    {
        parent::setUp();
        $this->testSha = new SHA( 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' );
        $this->mapping = [ References::HEAD => $this->testSha ];
        $this->references = new ReferencesArray( $this->mapping );
    }

    public function testGetReference()
    {
        $this->assertEquals( $this->testSha, $this->references->getReference( References::HEAD ) );
    }

    public function testTraversable()
    {
        $this->assertEquals( $this->mapping, iterator_to_array( $this->references ) );
    }

}