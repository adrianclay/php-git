<?php
namespace adrianclay\git;

use PHPUnit\Framework\TestCase;

class IndexFileTest extends TestCase
{
    public function testCountForZeroEntries()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/zero-entries");
        $this->assertCount(0, $indexFile );
    }

    public function testCountForOneEntry()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/one-entry");
        $this->assertCount(1, $indexFile );
    }

    public function testCountCanBeCalledTwiceWithSameResult()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/one-entry");
        $this->assertCount(1, $indexFile );
        $this->assertCount(1, $indexFile );
    }

    public function testNameForOneEntry()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/one-entry");
        $this->assertEquals('a', $indexFile->current()->name());
    }

    public function testValidIsFalseForZeroEntries()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/zero-entries");
        $this->assertFalse($indexFile->valid());
    }

    public function testValidBecomesFalseAfterOneCallToNextForOneEntry()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/one-entry");
        $this->assertTrue($indexFile->valid());
        $indexFile->next();
        $this->assertFalse($indexFile->valid());
    }

    public function testGetShaForOneEntry()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/one-entry");
        $this->assertEquals('e69de29bb2d1d6434b8b29ae775ad8c2e48c5391', $indexFile->current()->getSHA());
    }

    public function testShaForSecondEntry()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/two-entries-with-names-of-length-two");
        $indexFile->current();
        $indexFile->next();
        $this->assertEquals('3b18e512dba79e4c8300dd08aeb37f8e728b8dad', $indexFile->current()->getSHA());
    }

    public function testTwoNamesEachOfLengthTwo()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/two-entries-with-names-of-length-two");
        $this->assertEquals('00', $indexFile->current()->name());
        $indexFile->next();
        $this->assertEquals('ab', $indexFile->current()->name());
        $indexFile->next();
        $this->assertFalse($indexFile->valid());
    }

    public function testCallingNextAdvancesToSecondEntry()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/two-entries-with-names-of-length-two");
        $indexFile->next();
        $this->assertEquals('ab', $indexFile->current()->name());
    }

    public function testFetchesNameContainingAZero()
    {
        // Need to be careful to handle '0' and '\0' characters differently
        // within the name field.
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/two-entries-with-names-of-length-two");
        $this->assertEquals('00', $indexFile->current()->name());
    }

    public function testIterationIsDeterministic()
    {
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/two-entries-with-names-of-length-two");
        $firstIteration = iterator_to_array($indexFile, false);
        $secondIteration = iterator_to_array($indexFile, false);
        $this->assertEquals($firstIteration, $secondIteration);
    }

    public function testNameHandlesZeroLengthNamePadding()
    {
        // Entries have padding added depending on their name length.
        // Checks that the padding calculation works correctly in the case that there is zero padding.
        $indexFile = new IndexFile(__DIR__ . "/fixtures/index-files/two-entries-with-zero-length-name-padding");
        $indexFile->next();
        // Padding calculation only observable when looking at the second value retrieved.
        $this->assertEquals("1", $indexFile->current()->name());
        $this->assertEquals("e69de29bb2d1d6434b8b29ae775ad8c2e48c5391", $indexFile->current()->getSHA());
    }
}