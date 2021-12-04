<?php

namespace adrianclay\git\References;

use adrianclay\git\References;
use adrianclay\git\Repository;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    const TEST_SHA = 'abababababababababababababababab';

    const REF_MAIN = 'refs/heads/main';

    public function testGetHead()
    {
        $this->assertRefsHasRef( $this->getReferences( [ References::HEAD => self::TEST_SHA ] ), Parser::HEAD, self::TEST_SHA );
    }

    public function testGetMain()
    {
        $refs = $this->getReferences( [ 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA ] ] ] );
        $this->assertRefsHasRef( $refs, self::REF_MAIN, self::TEST_SHA );
    }

    public function testGetMainWithTrailingNewLine()
    {
        $refs = $this->getReferences( [ 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA . "\n" ] ] ] );
        $this->assertRefsHasRef( $refs, self::REF_MAIN, self::TEST_SHA );
    }

    public function testSymbolicRefHead()
    {
        $refs = $this->getReferences( [ References::HEAD => 'ref: refs/heads/main', 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA ] ] ] );
        $this->assertRefsHasRef( $refs, References::HEAD, self::TEST_SHA );
    }

    public function testSymbolicRef()
    {
        $refs = $this->getReferences( [ 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA, 'test' => 'ref: refs/heads/main' ] ] ] );
        $this->assertRefsHasRef( $refs, 'refs/heads/test', self::TEST_SHA );
    }

    public function testPackedRef()
    {
        $refs = $this->getReferences( [ 'packed-refs' => self::TEST_SHA . ' ' . self::REF_MAIN ] );
        $this->assertRefsHasRef( $refs, self::REF_MAIN, self::TEST_SHA );
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
        $packedRefs .= self::TEST_SHA . ' ' . self::REF_MAIN;
        $refs = $this->getReferences( [ 'packed-refs' => $packedRefs ] );
        $this->assertRefsHasRef( $refs, self::REF_MAIN, self::TEST_SHA );
        $this->assertCount( 1, $refs->getIterator() );
    }

    public function testPackedRefFileWithTrailingNewLine()
    {
        $refs = $this->getReferences( [ 'packed-refs' => self::TEST_SHA . ' ' . self::REF_MAIN . "\n" ] );
        $this->assertRefsHasRef( $refs, self::REF_MAIN, self::TEST_SHA );
        $this->assertCount( 1, $refs->getIterator() );
    }

    /**
     * @param array $gitDir
     * @return Parser
     */
    private function getReferences( array $gitDir )
    {
        vfsStream::setup( 'root', null, [ '.git' => $gitDir ] );
        $repo = new Repository( vfsStream::url( 'root' ) );
        $refs = new Parser( $repo );
        $this->assertInstanceOf( 'Traversable', $refs );
        return $refs;
    }

    /**
     * @param Parser $refs
     * @param string $ref
     * @param string $sha
     */
    public function assertRefsHasRef( Parser $refs, $ref, $sha )
    {
        $refsArray = iterator_to_array( $refs, true );
        $this->assertArrayHasKey( $ref, $refsArray );
        $this->assertEquals( $sha, $refsArray[$ref]->getSHA() );
    }
}