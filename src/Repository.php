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

}