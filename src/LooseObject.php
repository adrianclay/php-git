<?php
namespace adrianclay\git;


class LooseObject implements GitObject
{
    /** @var string */
    private $data;

    /** @var string */
    private $type;

    /**
     * @throws \Exception
     */
    public function __construct( string $path )
    {
        $data = zlib_decode( file_get_contents( $path ) );
        $nullPos = strpos( $data, "\0" );
        list( $this->type, $bodyLength ) = explode( ' ', substr( $data, 0, $nullPos ) );
        $this->data = substr( $data, $nullPos + 1 );
        if ( $bodyLength != strlen( $this->data ) ) {
            throw new \Exception( "Invalid object length" );
        }
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getType(): string
    {
        return $this->type;
    }
}