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

    public function __construct( Repository $repo, SHAReference $reference )
    {
        $this->repo = $repo;
        $this->reference = $reference;
        $this->deconstructObject();
    }

    public function getTree(): Tree
    {
        return new Tree( $this->repo, new SHA( $this->tree ) );
    }

    /**
     * @return Commit[]
     */
    public function getParents(): array
    {
        return array_map( function ( $parentReference ) {
            return new Commit( $this->repo, $parentReference );
        }, $this->parents );
    }

    public function getAuthor(): Signature
    {
        return Signature::parseSignature( $this->author );
    }

    public function getCommitter(): Signature
    {
        return Signature::parseSignature( $this->committer );
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    private function deconstructObject(): void
    {
        $object = $this->repo->getObject( $this->reference );
        if ( $object->getType() != GitObject::TYPE_COMMIT ) {
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