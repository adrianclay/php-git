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
        $object = $this->repo->getObject( $this->reference );
        if ( $object->getType() != Object::TYPE_TREE ) {
            throw new \InvalidArgumentException();
        }
        $dataBuffer = $object->getData();

        while ( strlen( $dataBuffer ) ) {
            list( $mode, $dataBuffer ) = explode( ' ', $dataBuffer, 2 );
            list( $filename, $dataBuffer ) = explode( chr( 0 ), $dataBuffer, 2 );
            $shaHash = bin2hex( substr( $dataBuffer, 0, Object::SHA_BIN_SIZE ) );
            $dataBuffer = substr( $dataBuffer, Object::SHA_BIN_SIZE );

            $this->files[] = new Entry( $mode, $filename, new SHA( $shaHash ) );
        }
    }
}