<?php

namespace adrianclay\git;

use adrianclay\git\References\ReferencesArray;
use PHPUnit\Framework\TestCase;

class RevisionResolverTest extends TestCase
{
    private $sha = "abcdefabcdefabcdefabcdefabcdefabcdefabcd";

    public function testFullSHA()
    {
        $this->assertEqualSHA( $this->getResolver( [], $this->sha ) );
    }

    public function testHEAD()
    {
        $this->assertEqualSHA( $this->getResolver( [ References::HEAD => new SHA( $this->sha ) ], References::HEAD ) );
    }

    public function testMain()
    {
        $this->assertEqualSHA( $this->getResolver( [ 'refs/heads/main' => new SHA( $this->sha ) ], 'main' ) );
    }

    public function testOriginMain()
    {
        $references = [ 'refs/remotes/origin/main' => new SHA( $this->sha ) ];
        $this->assertEqualSHA( $this->getResolver( $references, 'origin/main' ) );
    }

    /**
     * @param SHAReference[] $references
     * @param string         $revision
     * @return RevisionResolver
     */
    private function getResolver( array $references, $revision )
    {
        return new RevisionResolver( new ReferencesArray( $references ), $revision );
    }

    /**
     * @param RevisionResolver $revisionResolver
     */
    private function assertEqualSHA( RevisionResolver $revisionResolver )
    {
        $this->assertEquals( $this->sha, $revisionResolver->getSHA() );
    }


}