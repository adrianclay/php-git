<?php
namespace adrianclay\git;


class Pack
{
    const TYPE_COMMIT = 1;
    const TYPE_TREE = 2;
    const TYPE_BLOB = 3;
    const TYPE_TAG = 4;
    const TYPE_OFFSET_DELTA = 6;
    const TYPE_REF_DELTA = 7;

    const MAGIC_HEADER = "PACK";

    /** @var \adrianclay\git\Pack\Index */
    private $index;

    /** @var resource */
    private $fileResource;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct( string $path )
    {
        $this->index = new Pack\Index( preg_replace( '@.pack$@', '.idx', $path ) );
        $this->fileResource = fopen( $path, 'r' );
        if ( self::MAGIC_HEADER != fread( $this->fileResource, strlen( self::MAGIC_HEADER ) ) ) {
            throw new \InvalidArgumentException;
        }
        list( , $version, $this->noEntries ) = unpack( 'N*', fread( $this->fileResource, 8 ) );
        if ( $version != 2 ) {
            throw new \InvalidArgumentException;
        }
    }

    /**
     * @throws \Exception
     */
    public function getObject( SHAReference $reference ): ?GitObject
    {
        $packOffset = $this->index->getPackFileOffset( $reference );
        if ( $packOffset === null ) {
            return null;
        }
        return $this->readObjectAtOffset( $packOffset );
    }

    private function readObjectAtOffset( int $packOffset ): GitObject
    {
        fseek( $this->fileResource, $packOffset );
        list( $size, $type ) = $this->readSizeAndTypeOfEntry();
        if ( $type == self::TYPE_OFFSET_DELTA ) {
            $baseOffset = $this->readDeltaBaseOffset();
            $encodedDelta = $this->readZlibData( $size );
            $parent = $this->readObjectAtOffset( $packOffset - $baseOffset );
            return new Pack\Delta( $parent, $encodedDelta );
        } else {
            return new Pack\CompleteObject( $this->readZlibData( $size ), $type );
        }
    }

    private function readSizeAndTypeOfEntry(): array
    {
        $encodedByte = ord( fgetc( $this->fileResource ) );
        $size = $encodedByte & 0b00001111;
        $type = ( $encodedByte & 0b01110000 ) >> 4;
        $shift = 4;
        while ( $encodedByte & 0b10000000 ) {
            $encodedByte = ord( fgetc( $this->fileResource ) );
            $size += ( $encodedByte & 0b01111111 ) << $shift;
            $shift += 7;
        }
        return array( $size, $type );
    }

    private function readZlibData( int $decompressedLength ): string
    {
        $compressedLength = max( $decompressedLength * 2, 100 );
        $compressedData = fread( $this->fileResource, $compressedLength );
        return zlib_decode( $compressedData, $decompressedLength );
    }

    private function readDeltaBaseOffset(): int
    {
        $encodedOffset = ord( fgetc( $this->fileResource ) );
        $baseOffset = $encodedOffset & 0b01111111;
        while ( $encodedOffset & 0b10000000 ) {
            $encodedOffset = ord( fgetc( $this->fileResource ) );
            $baseOffset++;
            $baseOffset <<= 7;
            $baseOffset += $encodedOffset & 0b01111111;
        }
        return $baseOffset;
    }
}