<?php

namespace adrianclay\git;

use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    public function testParseSignature()
    {
        $signature = Signature::parseSignature( "Linus Torvalds <torvalds@linux-foundation.org> 1417894045 -0800" );
        $this->assertEquals( "Linus Torvalds", $signature->getName() );
        $this->assertEquals( "torvalds@linux-foundation.org", $signature->getEmail() );
        $this->assertEquals( 1417894045, $signature->getDateTime()->getTimestamp() );
        $this->assertEquals( -8 * 60 * 60, $signature->getDateTime()->getOffset() );
    }

}