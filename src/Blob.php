<?php
namespace adrianclay\git;

class Blob
{

    /** @var \adrianclay\git\Repository */
    private $repo;

    /** @var \adrianclay\git\SHAReference */
    private $reference;

    /** @var string */
    private $data;

    public function __construct( Repository $repo, SHAReference $reference )
    {
        $this->repo = $repo;
        $this->reference = $reference;
        $this->deconstructObject();
    }

    public function getContents(): string
    {
        return $this->data;
    }

    private function deconstructObject()
    {
        $object = $this->repo->getObject( $this->reference );
        if ( $object->getType() != GitObject::TYPE_BLOB ) {
            throw new \InvalidArgumentException();
        }
        $this->data = $object->getData();
    }

}