<?php
namespace adrianclay\git;


class LooseObject implements Object
{
    /** @var string */
    private $data;

    /** @var string */
    private $type;

    /**
     * @param string $path
     * @throws \Exception
     */
    public function __construct( $path )
    {
        $data = zlib_decode( file_get_contents( $path ) );
        $nullPos = strpos( $data, "\0" );
        list( $this->type, $bodyLength ) = explode( ' ', substr( $data, 0, $nullPos ) );
        $this->data = substr( $data, $nullPos + 1 );
        if ( $bodyLength != strlen( $this->data ) ) {
            throw new \Exception( "Invalid object length" );
        }
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