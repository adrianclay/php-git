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


    public function __construct( Repository $repo, SHAReference $reference )
    {
        $this->repo = $repo;
        $this->reference = $reference;
        $this->deconstructObject();
    }

    /**
     * @return Tree\Entry[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getSHA(): string
    {
        return $this->reference->getSHA();
    }

    private function deconstructObject()
    {
        $object = $this->repo->getObject( $this->reference );
        if ( $object->getType() != GitObject::TYPE_TREE ) {
            throw new \InvalidArgumentException();
        }
        $dataBuffer = $object->getData();

        while ( strlen( $dataBuffer ) ) {
            list( $mode, $dataBuffer ) = explode( ' ', $dataBuffer, 2 );
            list( $filename, $dataBuffer ) = explode( chr( 0 ), $dataBuffer, 2 );
            $shaHash = bin2hex( substr( $dataBuffer, 0, GitObject::SHA_BIN_SIZE ) );
            $dataBuffer = substr( $dataBuffer, GitObject::SHA_BIN_SIZE );

            $this->files[] = new Entry( $mode, $filename, new SHA( $shaHash ) );
        }
    }
}