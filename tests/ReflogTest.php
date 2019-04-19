<?php
namespace adrianclay\git;

use PHPUnit\Framework\TestCase;

class ReflogTest extends TestCase
{
    /** @var Reflog */
    private $reflog;

    public function setUp(): void
    {
        $this->reflog = new Reflog( __DIR__ . '/fixtures/reflog-head' );
    }

    public function testTraversable()
    {
        $this->assertInstanceOf( 'Traversable', $this->reflog );
        $this->assertCount( 5, $this->reflog->getIterator() );
    }

    public function testFirstEntry()
    {
        /** @var Reflog\Entry[] $entries */
        $entries = iterator_to_array( $this->reflog );
        $firstEntry = $entries[0];
        $this->assertEquals( 'edb8f373a0875217ed1190dad0e636640c03fdf6', $firstEntry->getFrom()->getSHA() );
        $this->assertEquals( '4a87e8bcbe4272529f449ccaf36bfa41c1c844b3', $firstEntry->getTo()->getSHA() );
        $this->assertEquals( 'commit: Refactored Object model. Added some unit tests.', $firstEntry->getMessage() );

        $firstEntrySignature = $firstEntry->getSignature();
        $this->assertEquals( 'adieclay@gmail.com', $firstEntrySignature->getEmail() );
        $this->assertEquals( 'Adrian Clay', $firstEntrySignature->getName() );
        $this->assertEquals( 1413662995, $firstEntrySignature->getDateTime()->getTimestamp() );
        $this->assertEquals( 1 * 60 * 60, $firstEntrySignature->getDateTime()->getOffset() );
    }
}