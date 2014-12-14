<?php

namespace adrianclay\git;

class Reflog implements \IteratorAggregate
{
    /** @var resource */
    private $filePointer;

    /**
     * @param string $filePath
     */
    public function __construct( $filePath )
    {
        $this->filePointer = fopen( $filePath, 'r' );
    }

    /**
     * @return Reflog\Entry[]
     */
    public function getIterator()
    {
        \fseek( $this->filePointer, 0 );
        $entries = [];
        while( $line = \fgets( $this->filePointer ) ) {
            $line = trim( $line, "\n" );
            list( $from, $to, $end ) = explode( ' ', $line, 3 );
            $from = new SHA( $from );
            $to = new SHA( $to );
            list( $signature, $message ) = explode( "\t", $end, 2 );
            $signature = Signature::parseSignature( $signature );
            $entries[] = new Reflog\Entry( $from, $to, $signature, $message );
        }
        return new \ArrayIterator( \array_reverse( $entries ) );
    }
}