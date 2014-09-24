<?php
namespace adrianclay\git;


class Object
{
    /** @var string */
    private $data;

    /** @var string */
    private $type;

    /**
     * @param Repository $repository
     * @param SHAReference $reference
     */
    public function __construct( Repository $repository, SHAReference $reference )
    {
        $path = $this->getObjectPath( $repository, $reference );
        $data = zlib_decode( file_get_contents( $path ) );
        $nullPos = strpos( $data, "\0" );
        list( $this->type, $bodyLength ) = explode( ' ', substr( $data, 0, $nullPos ) );
        $this->data = substr( $data, $nullPos + 1 );
        if ( $bodyLength != strlen( $this->data ) ) {
            throw new \Exception( "Invalid object length" );
        }
    }

    /**
     * @param Repository $repository
     * @param SHAReference $reference
     * @return string
     */
    private function getObjectPath( Repository $repository, SHAReference $reference )
    {
        $d = DIRECTORY_SEPARATOR;
        $hash = $reference->getSHA();
        return $repository->getGitPath() . $d . 'objects' . $d . substr( $hash, 0, 2 ) . $d . substr( $hash, 2 );
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}