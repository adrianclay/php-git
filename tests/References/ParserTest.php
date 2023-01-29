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
        $parser = $this->createParserFromGitDir( [ References::HEAD => self::TEST_SHA ] );
        $this->assertParserHasRef( $parser, Parser::HEAD, self::TEST_SHA );
    }

    public function testGetMain()
    {
        $parser = $this->createParserFromGitDir( [ 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA ] ] ] );
        $this->assertParserHasRef( $parser, self::REF_MAIN, self::TEST_SHA );
    }

    public function testGetMainWithTrailingNewLine()
    {
        $parser = $this->createParserFromGitDir( [ 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA . "\n" ] ] ] );
        $this->assertParserHasRef( $parser, self::REF_MAIN, self::TEST_SHA );
    }

    public function testSymbolicRefHead()
    {
        $parser = $this->createParserFromGitDir( [ References::HEAD => 'ref: refs/heads/main', 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA ] ] ] );
        $this->assertParserHasRef( $parser, References::HEAD, self::TEST_SHA );
    }

    public function testSymbolicRef()
    {
        $refs = $this->createParserFromGitDir( [ 'refs' => [ 'heads' => [ 'main' => self::TEST_SHA, 'test' => 'ref: refs/heads/main' ] ] ] );
        $this->assertParserHasRef( $refs, self::REF_MAIN, self::TEST_SHA );
    }

    public function testGetReferencesReturnsNull()
    {
        $parser = $this->createParserFromGitDir( [ 'refs' => [] ] );
        $this->assertNull($parser->getReference('fake_ref'));
    }

    public function testPackedRef()
    {
        $parser = $this->createParserFromGitDir( [ 'packed-refs' => self::TEST_SHA . ' ' . self::REF_MAIN ] );
        $this->assertParserHasRef( $parser, self::REF_MAIN, self::TEST_SHA );
    }

    public function testDoesNotUsePackedRefValueIfDefinedWithinReferences()
    {
        $packedRefValue = 'DEADDEADDEADDEADDEADDEADDEADDEAD';
        $parser = $this->createParserFromGitDir( [ References::HEAD => self::TEST_SHA, 'packed-refs' => $packedRefValue . ' ' . References::HEAD ] );
        foreach( $parser as $ref ) {
            $this->assertNotEquals( $packedRefValue, $ref->getSHA() );
        }
    }

    public function testPackedRefFileWithComment()
    {
        $packedRefs  = "# pack-refs with: peeled\n";
        $packedRefs .= self::TEST_SHA . ' ' . self::REF_MAIN;
        $parser = $this->createParserFromGitDir( [ 'packed-refs' => $packedRefs ] );
        $this->assertParserHasRef( $parser, self::REF_MAIN, self::TEST_SHA );
        $this->assertCount( 1, $parser->getIterator() );
    }

    public function testPackedRefFileWithTrailingNewLine()
    {
        $parser = $this->createParserFromGitDir( [ 'packed-refs' => self::TEST_SHA . ' ' . self::REF_MAIN . "\n" ] );
        $this->assertParserHasRef( $parser, self::REF_MAIN, self::TEST_SHA );
        $this->assertCount( 1, $parser->getIterator() );
    }

    private function createParserFromGitDir( array $gitDir ): Parser
    {
        vfsStream::setup( 'root', null, [ '.git' => $gitDir ] );
        $repo = new Repository( vfsStream::url( 'root' ) );
        $refs = new Parser( $repo );
        $this->assertInstanceOf( 'Traversable', $refs );
        return $refs;
    }

    public function assertParserHasRef( Parser $refs, string $ref, string $sha )
    {
        $refsArray = iterator_to_array( $refs, true );
        $this->assertArrayHasKey( $ref, $refsArray );
        $this->assertEquals( $sha, $refsArray[$ref]->getSHA() );
    }
}