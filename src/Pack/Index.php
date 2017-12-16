<?php
namespace adrianclay\git\Pack;

use adrianclay\git\GitObject;
use adrianclay\git\SHAReference;

class Index
{
    const FANOUT_TABLE_ENTRIES = 256;
    const FANOUT_TABLE_ENTRY_SIZE = 4;
    const CRC_TABLE_ENTRY_SIZE = 4;
    const OFFSET_TABLE_ENTRY_SIZE = 4;
    const EXTENDED_OFFSET_TABLE_ENTRY_SIZE = 8;

    const SIZE_HEADER = 8;
    const VERSION_TWO = "\000\000\000\002";
    const MAGIC_VALUE = "\377tOc";
    const EXTENDED_OFFSET_TABLE_FLAG = 0x80000000;

    /** @var int[] */
    private $fanoutTable;

    /** @var resource */
    private $fileResource;

    /**
     * @param string $idxPath
     */
    public function __construct( $idxPath )
    {
        $this->fileResource = fopen( $idxPath, 'r' );
        if ( self::MAGIC_VALUE . self::VERSION_TWO !== fread( $this->fileResource, self::SIZE_HEADER ) ) {
            throw new \InvalidArgumentException;
        }
        $this->fanoutTable = array_values( unpack( 'N*', fread( $this->fileResource, self::FANOUT_TABLE_ENTRIES * self::FANOUT_TABLE_ENTRY_SIZE ) ) );
    }

    /**
     * @param SHAReference $reference
     * @return int|null
     */
    public function getPackFileOffset( SHAReference $reference )
    {
        $tableIndex = $this->getTableIndexFromReference( $reference );
        if ( $tableIndex === null ) {
            return null;
        }

        $fileOffset = self::SIZE_HEADER + self::FANOUT_TABLE_ENTRIES * self::FANOUT_TABLE_ENTRY_SIZE
                + $this->getNumberSHATableEntries() * ( GitObject::SHA_BIN_SIZE + self::CRC_TABLE_ENTRY_SIZE )
                + $tableIndex * self::OFFSET_TABLE_ENTRY_SIZE;

        fseek( $this->fileResource, $fileOffset );
        list( , $offset ) = unpack( 'N', fread( $this->fileResource, self::OFFSET_TABLE_ENTRY_SIZE ) );

        if ( $offset & self::EXTENDED_OFFSET_TABLE_FLAG ) {
            throw new \Exception( "Not implemented yet." );
        }

        return $offset;
    }

    /**
     * @param SHAReference $reference
     * @return int|null
     */
    private function getTableIndexFromReference( SHAReference $reference )
    {
        $binSHA = hex2bin( $reference->getSHA() );
        $fanoutIndex = ord( $binSHA[0] );
        $linearSearchStart = $fanoutIndex == 0 ? 0 : $this->fanoutTable[$fanoutIndex - 1];
        $linearSearchStop = $this->fanoutTable[$fanoutIndex];
        for ( $i = $linearSearchStart; $i < $linearSearchStop; $i++ ) {

            $fileOffset = self::SIZE_HEADER + self::FANOUT_TABLE_ENTRIES * self::FANOUT_TABLE_ENTRY_SIZE
                    + $i * GitObject::SHA_BIN_SIZE;

            fseek( $this->fileResource, $fileOffset );
            $hashComparison = strcmp( fread( $this->fileResource, GitObject::SHA_BIN_SIZE ), $binSHA );
            if ( $hashComparison < 0 ) {
                continue;
            } elseif ( $hashComparison == 0 ) {
                return $i;
            } else {
                break;
            }
        }
        return null;
    }

    /**
     * @return int
     */
    private function getNumberSHATableEntries()
    {
        return $this->fanoutTable[self::FANOUT_TABLE_ENTRIES - 1];
    }
}