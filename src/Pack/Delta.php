<?php
namespace adrianclay\git\Pack;


class Delta implements \adrianclay\git\Object
{
    /** @var \adrianclay\git\Object */
    protected $base;

    /** @var string */
    protected $delta;

    /** @var int */
    protected $deltaOffset;

    /**
     * @param \adrianclay\git\Object $base
     * @param                        $delta
     */
    public function __construct( \adrianclay\git\Object $base, $delta )
    {
        $this->base = $base;
        $this->delta = $delta;
    }

    /**
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getData()
    {
        $this->deltaOffset = 0;
        $baseData = $this->base->getData();
        $baseDataLength = $this->parseLength();
        if ( $baseDataLength != strlen( $baseData ) ) {
            throw new \InvalidArgumentException;
        }
        $decompressedDataLength = $this->parseLength();
        $decompressedData = '';
        while ( $this->deltaOffset < strlen( $this->delta ) ) {
            $deltaCommand = $this->consumeByte();
            if ( $this->isCopyCommand( $deltaCommand ) ) {
                $decompressedData = $decompressedData . $this->copyFromBaseData( $deltaCommand, $baseData );
            } elseif ( $deltaCommand ) {
                $decompressedData = $decompressedData . substr( $this->delta, $this->deltaOffset, $deltaCommand );
                $this->deltaOffset += $deltaCommand;
            } else {
                throw new \InvalidArgumentException;
            }
        }
        if ( $decompressedDataLength != strlen( $decompressedData ) ) {
            throw new \InvalidArgumentException;
        }
        return $decompressedData;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->base->getType();
    }

    /**
     * @return int
     */
    private function parseLength()
    {
        $shift = 0;
        $length = 0;
        do {
            $consumed = $this->consumeByte();
            $length += ( $consumed & 0b01111111 ) << $shift;
            $shift += 7;
        } while ( $consumed & 0b10000000 );
        return $length;
    }

    /**
     * @return int
     */
    private function consumeByte()
    {
        return ord( $this->delta[$this->deltaOffset++] );
    }

    /**
     * @param $deltaCommand
     * @return bool
     */
    private function isCopyCommand( $deltaCommand )
    {
        return (bool) ( $deltaCommand & 0b10000000 );
    }

    /**
     * @param int    $deltaCommand
     * @param string $baseData
     * @return string
     */
    private function copyFromBaseData( $deltaCommand, $baseData )
    {
        $copyOffset = 0;
        for ( $i = 0; $i < 4; $i++ ) {
            if ( $deltaCommand & ( 0b0001 << $i ) ) {
                $copyOffset += $this->consumeByte() << ( $i * 8 );
            }
        }
        $copySize = 0;
        for ( $i = 0; $i < 3; $i++ ) {
            if ( $deltaCommand & ( 0b00010000 << $i ) ) {
                $copySize += $this->consumeByte() << ( $i * 8 );
            }
        }
        if ( !$copySize ) {
            $copySize = 0x10000;
        }
        $copiedData = substr( $baseData, $copyOffset, $copySize );
        return $copiedData;
    }

}