<?php
namespace adrianclay\git;

class Commit
{
    /** @var \adrianclay\git\Repository */
    private $repo;

    /** @var \adrianclay\git\SHAReference */
    private $reference;

    /** @var string */
    private $message;

    /** @var string */
    private $tree;

    /** @var SHA[] */
    private $parents = array();

    /** @var string */
    private $author;

    /** @var string */
    private $committer;

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
     * @return Tree
     */
    public function getTree()
    {
        return new Tree( $this->repo, new SHA( $this->tree ) );
    }

    /**
     * @return Commit[]
     */
    public function getParents()
    {
        return array_map( function ( $parentReference ) {
            return new Commit( $this->repo, $parentReference );
        }, $this->parents );
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    private function deconstructObject()
    {
        $object = $this->repo->getObject( $this->reference );
        if ( $object->getType() != Object::TYPE_COMMIT ) {
            throw new \InvalidArgumentException();
        }
        $data = $object->getData();

        $bodyDelimiter = "\n\n";
        $commitHeaderEndPos = strpos( $data, $bodyDelimiter );
        $commitMessageStartPos = $commitHeaderEndPos + strlen( $bodyDelimiter );
        $this->message = substr( $data, $commitMessageStartPos );
        $headers = explode( "\n", substr( $data, 0, $commitHeaderEndPos ) );
        $headerKeyValues = array_map( function ( $header ) {
            return explode( " ", $header, 2 );
        }, $headers );
        foreach ( $headerKeyValues as $headerKeyValue ) {
            list( $key, $value ) = $headerKeyValue;
            switch ( $key ) {
                case "tree":
                    $this->tree = $value;
                    break;
                case "parent":
                    $this->parents[] = new SHA( $value );
                    break;
                case "author":
                    $this->author = $value;
                    break;
                case "committer":
                    $this->committer = $value;
                    break;
            }
        }
    }

}