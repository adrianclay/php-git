<?php

namespace adrianclay\git;

use adrianclay\git\References\ReferencesArray;

class RevisionResolverTest extends \PHPUnit_Framework_TestCase
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

    public function testMaster()
    {
        $this->assertEqualSHA( $this->getResolver( [ 'refs/heads/master' => new SHA( $this->sha ) ], 'master' ) );
    }

    public function testOriginMaster()
    {
        $references = [ 'refs/remotes/origin/master' => new SHA( $this->sha ) ];
        $this->assertEqualSHA( $this->getResolver( $references, 'origin/master' ) );
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