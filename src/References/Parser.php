<?php

namespace adrianclay\git\References;

use adrianclay\git\References;
use adrianclay\git\Repository;
use adrianclay\git\SHA;
use adrianclay\git\SHAReference;

class Parser implements \IteratorAggregate, References
{
    /** @var Repository */
    private $repo;

    /**
     * @param Repository $repo
     */
    public function __construct( Repository $repo )
    {
        $this->repo = $repo;
    }

    public function getIterator()
    {
        $iterator = new \AppendIterator();
        $headFile = $this->repo->getGitPath() . DIRECTORY_SEPARATOR . self::HEAD;
        if ( \file_exists( $headFile ) ) {
            $headReference = [ self::HEAD => $this->parseRefString( \file_get_contents( $headFile ) ) ];
            $iterator->append( new \ArrayIterator( $headReference ) );
        }
        $refsDir = $this->repo->getGitPath() . DIRECTORY_SEPARATOR . 'refs';
        if ( \file_exists( $refsDir ) ) {
            $iterator->append( new DirectoryIterator( $this, $refsDir ) );
        }
        $looseObjects = \iterator_to_array( $iterator, true );
        $packedRefsFile = $this->repo->getGitPath() . DIRECTORY_SEPARATOR . 'packed-refs';
        if ( \file_exists( $packedRefsFile ) ) {
            foreach( \explode( "\n", \file_get_contents( $packedRefsFile ) ) as $line ) {
                if ( $this->isLineAComment( $line ) ) {
                    continue;
                }
                list( $sha, $name ) = \explode( ' ', $line );
                if ( !\array_key_exists( $name, $looseObjects ) ) {
                    $looseObjects[$name] = new SHA( $sha );
                }
            }
        };
        return new \ArrayIterator( $looseObjects );
    }

    /**
     * @param string $name
     * @return SHAReference
     */
    public function getReference( $name )
    {
        return \iterator_to_array( $this )[$name];
    }

    /**
     * @param string $refString
     * @return SHAReference
     */
    public function parseRefString( $refString )
    {
        $refString = \trim( $refString );
        if ( \preg_match( '@^ref: (?<ref>.+)$@', $refString, $matches ) ) {
            return new SymbolicReference( $this, $matches['ref'] );
        }
        return new SHA( $refString );
    }

    /**
     * @param $line
     * @return bool
     */
    private function isLineAComment( $line )
    {
        return empty( $line ) || $line[0] == '#';
    }
}