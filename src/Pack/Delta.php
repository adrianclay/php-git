<?php
namespace adrianclay\git\Pack;


class Delta implements \adrianclay\git\GitObject
{
    /** @var \adrianclay\git\GitObject */
    protected $base;

    /** @var string */
    protected $delta;

    /** @var int */
    protected $deltaOffset;

    public function __construct( \adrianclay\git\GitObject $base, string $delta )
    {
        $this->base = $base;
        $this->delta = $delta;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getData(): string
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

    public function getType(): string
    {
        return $this->base->getType();
    }

    private function parseLength(): int
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

    private function consumeByte(): int
    {
        return ord( $this->delta[$this->deltaOffset++] );
    }

    private function isCopyCommand( int $deltaCommand ): bool
    {
        return (bool) ( $deltaCommand & 0b10000000 );
    }

    private function copyFromBaseData( int $deltaCommand, string $baseData ): string
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