<?php
namespace adrianclay\git;


class Repository {

    /** @var string */
    private $path;

    /** @var Pack[] */
    private $packs = [];

    /**
     * @param string $path
     * @throws \InvalidArgumentException
     */
    public function __construct( string $path )
    {
        $this->path = $path;
        foreach( glob( $this->getObjectDirectory() . DIRECTORY_SEPARATOR . 'pack' . DIRECTORY_SEPARATOR .'pack-*.pack' ) as $pack ) {
            $this->packs[] = new Pack( $pack );
        }
    }

    public function getGitPath(): string
    {
        return $this->path . '/.git';
    }

    public function getObject( SHAReference $reference ): ?GitObject
    {
        if ( file_exists( $objPath = $this->getLooseObjectPath( $reference ) ) ) {
            return new LooseObject( $objPath );
        }
        foreach( $this->packs as $pack ) {
            if ( $packObject = $pack->getObject( $reference ) ) {
                return $packObject;
            }
        }
        return null;
    }

    private function getLooseObjectPath( SHAReference $reference ): string
    {
        $d = DIRECTORY_SEPARATOR;
        $hash = $reference->getSHA();
        return $this->getObjectDirectory() . $d . substr( $hash, 0, 2 ) . $d . substr( $hash, 2 );
    }

    private function getObjectDirectory(): string
    {
        return $this->getGitPath() . DIRECTORY_SEPARATOR . 'objects';
    }

}