<?php
namespace adrianclay\git;


class Repository {

    /** @var string */
    private $path;

    /**
     * @param string $path
     * @throws \InvalidArgumentException
     */
    public function __construct( $path )
    {
        $this->path = realpath( $path );
        if ( $this->path === false ) {
            throw new \InvalidArgumentException;
        }
    }

    /**
     * @return string
     */
    public function getGitPath()
    {
        return $this->path . '/.git';
    }

    /**
     * @param SHAReference $reference
     * @return \adrianclay\git\Object
     */
    public function getObject( SHAReference $reference )
    {
        if ( file_exists( $objPath = $this->getLooseObjectPath( $reference ) ) ) {
            return new LooseObject( $objPath );
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @param SHAReference $reference
     * @return string
     */
    private function getLooseObjectPath( SHAReference $reference )
    {
        $d = DIRECTORY_SEPARATOR;
        $hash = $reference->getSHA();
        return $this->getObjectDirectory() . $d . substr( $hash, 0, 2 ) . $d . substr( $hash, 2 );
    }

    /**
     * @return string
     */
    private function getObjectDirectory()
    {
        return $this->getGitPath() . DIRECTORY_SEPARATOR . 'objects';
    }

}