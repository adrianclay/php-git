<?php
namespace adrianclay\git;

use adrianclay\git\Tree\Entry;

class Tree implements SHAReference
{
    /** @var \adrianclay\git\Repository */
    private $repo;

    /** @var \adrianclay\git\SHAReference */
    private $reference;

    /** @var \adrianclay\git\Tree\Entry[] */
    private $files = array();


    /**
     * @param Repository $repo
     * @param SHAReference $reference
     */
    public function __construct( Repository $repo, SHAReference $reference )
    {
        $this->repo = $repo;
        $this->reference = $reference;
        $this->deconstructObject();
    }

    /**
     * @return Tree\Entry[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function getSHA()
    {
        return $this->reference->getSHA();
    }

    private function deconstructObject()
    {
        $object = new Object( $this->repo, $this->reference );
        if ( $object->getType() != "tree" ) {
            throw new \InvalidArgumentException();
        }
        $dataBuffer = $object->getData();

        $HASH_LENGTH = 20;
        while ( strlen( $dataBuffer ) ) {
            list( $mode, $dataBuffer ) = explode( ' ', $dataBuffer, 2 );
            list( $filename, $dataBuffer ) = explode( chr( 0 ), $dataBuffer, 2 );
            $shaHash = bin2hex( substr( $dataBuffer, 0, $HASH_LENGTH ) );
            $dataBuffer = substr( $dataBuffer, $HASH_LENGTH );

            $this->files[] = new Entry( $mode, $filename, new SHA( $shaHash ) );
        }
    }
}