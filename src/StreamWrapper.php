<?php
namespace adrianclay\git;

use webignition\NormalisedUrl\NormalisedUrl;

class StreamWrapper {

    const PROTOCOL = "git";

    /** @var \adrianclay\git\Repository[] */
    private static $repositoryMappings = array();

    /**
     * @param string $path
     * @param string $hostname
     * @throws \InvalidArgumentException
     */
    public static function registerRepository( $path, $hostname )
    {
        if ( array_key_exists( $hostname, self::$repositoryMappings ) ) {
            throw new \InvalidArgumentException( "$hostname is already registered." );
        }
        $repo = new Repository( $path );
        self::$repositoryMappings[$hostname] = $repo;
        $registered = stream_wrapper_register( self::PROTOCOL, __CLASS__ );
    }

    /**
     * @param string $hostname
     * @return \adrianclay\git\Repository
     */
    private static function getRepository( $hostname )
    {
        return self::$repositoryMappings[$hostname];
    }


    /** @var \adrianclay\git\Repository */
    private $repository;

    /**
     * Returns the blob or tree corresponding to a path
     *
     * @param string $path
     * @return SHAReference|null
     */
    private function getSHAOfPath( $path )
    {
        $url = new NormalisedUrl( $path );
        $repo = self::getRepository( $url->getHost()->get() );
        if ( !$repo ) {
            return null;
        }
        $this->repository = $repo;
        $reference = new RevisionResolver( new References\Parser( $repo ), $url->getUser() );
        $commit = new Commit( $repo, $reference );
        $nextChild = $currentTree = $commit->getTree();
        $remainingParts = array_filter( explode( '/', $url->getPath()->get() ) );
        while( $remainingParts ) {
            $currentPart = array_shift( $remainingParts );
            $matchingChildren = array_filter( $currentTree->getFiles(), function( Tree\Entry $file ) use ( $currentPart ) {
                return $file->getFilename() == $currentPart;
            } );
            if ( !$matchingChildren ) {
                return null;
            }
            /** @var Tree\Entry $nextChild */
            $nextChild = array_shift( $matchingChildren );
            if ( $remainingParts ) {
                if ( !$nextChild->isTree() ) {
                    return null;
                } else {
                    $currentTree = new Tree( $repo, $nextChild );
                }
            }
        }
        return $nextChild;
    }

    #region Stream

    /** @var \adrianclay\git\Blob */
    private $blob;

    /** @var int */
    private $position = 0;

    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string $opened_path
     * @return bool
     */
    public function stream_open( $path, $mode, $options, &$opened_path )
    {
        $blobSha = $this->getSHAOfPath( $path );
        if ( !$blobSha ) {
            return false;
        }
        if ( $mode != "r" && $mode != "rb" ) {
            return false;
        }
        $this->blob = new Blob( $this->repository, $blobSha );
        return true;
    }

    /**
     * @param int $count
     * @return string
     */
    public function stream_read( $count )
    {
        $read = substr( $this->blob->getContents(), $this->position, $count );
        $this->position += strlen( $read );
        return $read;
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return $this->position >= strlen( $this->blob->getContents() );
    }

    /**
     * @return array
     */
    public function stream_stat()
    {
        return array();
    }

    #endregion

    #region Dir

    /** @var Tree */
    private $tree;

    public function dir_closedir()
    {

    }

    /**
     * @param string $path
     * @return bool
     */
    public function dir_opendir( $path )
    {
        $treeSha = $this->getSHAOfPath( $path );
        if ( !$treeSha ) {
            return false;
        }
        $this->tree = new Tree( $this->repository, $treeSha );
        $this->dir_rewinddir();
        return true;
    }

    /** @var Tree\Entry[] */
    private $fileListing;

    /**
     * @return bool|string
     */
    public function dir_readdir()
    {
        /** @var Tree\Entry $entry */
        $entry = current( $this->fileListing );
        if ( $entry === false ) {
            return false;
        }
        next( $this->fileListing );
        return $entry->getFilename();
    }

    public function dir_rewinddir()
    {
        $this->fileListing = $this->tree->getFiles();
    }

    #endregion
}