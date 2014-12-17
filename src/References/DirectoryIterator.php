<?php

namespace adrianclay\git\References;

use adrianclay\git\References;
use adrianclay\git\SHAReference;

class DirectoryIterator implements \Iterator
{
    /** @var \RecursiveIteratorIterator */
    private $directoryIterator;

    /** @var string */
    private $refsPath;

    /** @var References */
    private $refs;

    /**
     * @param References   $refs
     * @param string $refsPath
     */
    public function __construct( References $refs, $refsPath )
    {
        $this->refs = $refs;
        $this->refsPath = $refsPath;
        $this->directoryIterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $this->refsPath, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS ) );
    }

    /**
     * @return SHAReference
     */
    public function current()
    {
        return $this->refs->parseRefString( file_get_contents( $this->directoryIterator->current() ) );
    }

    public function next()
    {
        $this->directoryIterator->next();
    }

    /**
     * @return string
     */
    public function key()
    {
        return substr( $this->directoryIterator->key(), strlen( $this->refsPath ) - 4 );
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->directoryIterator->valid();
    }

    public function rewind()
    {
        $this->directoryIterator->rewind();
    }
}