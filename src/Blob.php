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

    /**
     * @param Repository   $repo
     * @param SHAReference $reference
     */
    public function __construct( Repository $repo, SHAReference $reference )
    {
        $this->repo = $repo;
        $this->reference = $reference;
        $this->deconstructObject();
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->data;
    }

    private function deconstructObject()
    {
        $object = $this->repo->getObject( $this->reference );
        if ( $object->getType() != "blob" ) {
            throw new \InvalidArgumentException();
        }
        $this->data = $object->getData();
    }

}