<?php

namespace adrianclay\git;

use org\bovigo\vfs\vfsStream;

class ReferencesTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SHA = 'abababababababababababababababab';

    const REF_MASTER = 'refs/heads/master';

    public function testGetHead()
    {
        $this->assertRefsHasRef( $this->getReferences( [ References::HEAD => self::TEST_SHA ] ), References::HEAD, self::TEST_SHA );
    }

    public function testGetMaster()
    {
        $refs = $this->getReferences( [ 'refs' => [ 'heads' => [ 'master' => self::TEST_SHA ] ] ] );
        $this->assertRefsHasRef( $refs, self::REF_MASTER, self::TEST_SHA );
    }

    public function testGetMasterWithTrailingNewLine()
    {
        $refs = $this->getReferences( [ 'refs' => [ 'heads' => [ 'master' => self::TEST_SHA . "\n" ] ] ] );
        $this->assertRefsHasRef( $refs, self::REF_MASTER, self::TEST_SHA );
    }

    public function testSymbolicRefHead()
    {
        $refs = $this->getReferences( [ References::HEAD => 'ref: refs/heads/master', 'refs' => [ 'heads' => [ 'master' => self::TEST_SHA ] ] ] );
        $this->assertRefsHasRef( $refs, References::HEAD, self::TEST_SHA );
    }

    public function testSymbolicRef()
    {
        $refs = $this->getReferences( [ 'refs' => [ 'heads' => [ 'master' => self::TEST_SHA, 'test' => 'ref: refs/heads/master' ] ] ] );
        $this->assertRefsHasRef( $refs, 'refs/heads/test', self::TEST_SHA );
    }

    public function testPackedRef()
    {
        $refs = $this->getReferences( [ 'packed-refs' => self::TEST_SHA . ' ' . self::REF_MASTER ] );
        $this->assertRefsHasRef( $refs, self::REF_MASTER, self::TEST_SHA );
    }

    public function testStalePackedRef()
    {
        $dodgyRef = 'DEADDEADDEADDEADDEADDEADDEADDEAD';
        $refs = $this->getReferences( [ References::HEAD => self::TEST_SHA, 'packed-refs' => $dodgyRef . ' ' . References::HEAD ] );
        foreach( $refs as $ref ) {
            $this->assertNotEquals( $dodgyRef, $ref->getSHA() );
        }
    }

    public function testPackedRefFileWithComment()
    {
        $packedRefs  = "# pack-refs with: peeled\n";
        $packedRefs .= self::TEST_SHA . ' ' . self::REF_MASTER;
        $refs = $this->getReferences( [ 'packed-refs' => $packedRefs ] );
        $this->assertRefsHasRef( $refs, self::REF_MASTER, self::TEST_SHA );
        $this->assertCount( 1, $refs->getIterator() );
    }

    public function testPackedRefFileWithTrailingNewLine()
    {
        $refs = $this->getReferences( [ 'packed-refs' => self::TEST_SHA . ' ' . self::REF_MASTER . "\n" ] );
        $this->assertRefsHasRef( $refs, self::REF_MASTER, self::TEST_SHA );
        $this->assertCount( 1, $refs->getIterator() );
    }

    /**
     * @param array $gitDir
     * @return References
     */
    private function getReferences( array $gitDir )
    {
        vfsStream::setup( 'root', null, [ '.git' => $gitDir ] );
        $repo = new Repository( vfsStream::url( 'root' ) );
        $refs = new References( $repo );
        $this->assertInstanceOf( 'Traversable', $refs );
        return $refs;
    }

    /**
     * @param References   $refs
     * @param string $ref
     * @param string $sha
     */
    public function assertRefsHasRef( References $refs, $ref, $sha )
    {
        $refsArray = iterator_to_array( $refs, true );
        $this->assertArrayHasKey( $ref, $refsArray );
        $this->assertEquals( $sha, $refsArray[$ref]->getSHA() );
    }
}