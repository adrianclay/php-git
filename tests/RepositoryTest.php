<?php

namespace adrianclay\git;

use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    const GIT_IGNORE_BLOB_SHA = "a725465aee245635a2bd129af54858ed32c84cb8";
    /** @var \adrianclay\git\Repository */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $path = __DIR__ . '/../';
        $this->repository = new Repository( $path );
    }

    public function testCommitGetParent()
    {
        $commit = new Commit( $this->repository, new \adrianclay\git\SHA( "95e4f60b09e7d6c60bd909f208b1e011c7507675" ) );
        $parents = $commit->getParents();
        $this->assertCount( 1, $parents );
        return $parents[0];
    }

    /**
     * @depends testCommitGetParent
     * @param Commit $commit
     * @return Commit
     */
    public function testCommit( Commit $commit )
    {
        $this->assertEquals( "Initial commit.\n", $commit->getMessage() );
        $this->assertEmpty( $commit->getParents() );
        return $commit;
    }

    /**
     * @depends testCommit
     * @param Commit $initialCommit
     */
    public function testCommitter( Commit $initialCommit )
    {
        $this->assertNotEmpty( $initialCommit->getCommitter() );
    }

    /**
     * @depends testCommit
     * @param Commit $initialCommit
     */
    public function testAuthor( Commit $initialCommit )
    {
        $this->assertNotEmpty( $initialCommit->getAuthor() );
    }

    /**
     * @depends testCommitGetParent
     * @param Commit $commit
     */
    public function testTree( Commit $commit )
    {
        $tree = $commit->getTree();
        $this->assertNotEmpty( $tree );
        $files = $tree->getFiles();
        $this->assertNotEmpty( $files );
        $this->assertCount( 4, $files );

        /** @var adrianclay\git\Tree\Entry $srcFolder */
        $srcFolder = array_pop( $files );
        $this->assertEquals( "src", $srcFolder->getFilename() );
        $this->assertTrue( $srcFolder->isTree() );
        $this->assertEquals( "8369676ef6a9f852f5fe041677b02152841f8106", $srcFolder->getSHA() );

        /** @var adrianclay\git\Tree\Entry $gitIgnore */
        $gitIgnore = array_shift( $files );
        $this->assertEquals( ".gitignore", $gitIgnore->getFilename() );
        $this->assertFalse( $gitIgnore->isTree() );
        $this->assertEquals( self::GIT_IGNORE_BLOB_SHA, $gitIgnore->getSHA() );
    }

    public function testBlob()
    {
        $gitIgnore = new Blob( $this->repository, new \adrianclay\git\SHA( self::GIT_IGNORE_BLOB_SHA ) );
        $this->assertEquals( "vendor/", $gitIgnore->getContents() );
    }

    public function testBadRef()
    {
        $badObject = $this->repository->getObject( new \adrianclay\git\SHA( "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa" ) );
        $this->assertNull( $badObject );
    }
}
