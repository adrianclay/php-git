<?php

namespace adrianclay\git\References;

use adrianclay\git\SHAReference;

class DirectoryIterator implements \Iterator
{
    /** @var \RecursiveIteratorIterator */
    private $directoryIterator;

    /** @var string */
    private $refsPath;

    /** @var Parser */
    private $refs;

    public function __construct( Parser $refs, string $refsPath )
    {
        $this->refs = $refs;
        $this->refsPath = $refsPath;
        $this->directoryIterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $this->refsPath, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS ) );
    }

    public function current(): SHAReference
    {
        return $this->refs->parseRefString( file_get_contents( $this->directoryIterator->current() ) );
    }

    public function next(): void
    {
        $this->directoryIterator->next();
    }

    public function key(): string
    {
        return substr( $this->directoryIterator->key(), strlen( $this->refsPath ) - 4 );
    }

    public function valid(): bool
    {
        return $this->directoryIterator->valid();
    }

    public function rewind(): void
    {
        $this->directoryIterator->rewind();
    }
}