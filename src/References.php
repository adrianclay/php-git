<?php

namespace adrianclay\git;

class References implements \IteratorAggregate
{
    const HEAD = 'HEAD';

    /** @var \adrianclay\git\Repository */
    private $repo;

    public function __construct( Repository $repo )
    {
        $this->repo = $repo;
    }

    public function getIterator()
    {
        $iterator = new \AppendIterator();
        $headFile = $this->repo->getGitPath() . DIRECTORY_SEPARATOR . self::HEAD;
        if ( file_exists( $headFile ) ) {
            $headReference = [ self::HEAD => $this->parseRefString( file_get_contents( $headFile ) ) ];
            $iterator->append( new \ArrayIterator( $headReference ) );
        }
        $refsDir = $this->repo->getGitPath() . DIRECTORY_SEPARATOR . 'refs';
        if ( file_exists( $refsDir ) ) {
            $iterator->append( new References\DirectoryIterator( $this, $refsDir ) );
        }
        $looseObjects = iterator_to_array( $iterator, true );
        $packedRefsFile = $this->repo->getGitPath() . DIRECTORY_SEPARATOR . 'packed-refs';
        if ( file_exists( $packedRefsFile ) ) {
            foreach( explode( "\n", file_get_contents( $packedRefsFile ) ) as $line ) {
                if ( $this->isLineAComment( $line ) ) {
                    continue;
                }
                list( $sha, $name ) = explode( ' ', $line );
                if ( !array_key_exists( $name, $looseObjects ) ) {
                    $looseObjects[$name] = new SHA( $sha );
                }
            }
        };
        return new \ArrayIterator( $looseObjects );
    }

    /**
     * @param string $refString
     * @return SHAReference
     */
    public function parseRefString( $refString )
    {
        $refString = trim( $refString );
        if ( preg_match( '@^ref: (?<ref>.+)$@', $refString, $matches ) ) {
            return new References\SymbolicReference( $this, $matches['ref'] );
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