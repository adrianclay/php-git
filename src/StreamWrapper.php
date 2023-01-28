<?php
namespace adrianclay\git;

class StreamWrapper {

    const PROTOCOL = "git";

    /** @var \adrianclay\git\Repository[] */
    private static $repositoryMappings = array();

    /**
     * @throws \InvalidArgumentException
     */
    public static function registerRepository( string $path, string $hostname )
    {
        if ( array_key_exists( $hostname, self::$repositoryMappings ) ) {
            throw new \InvalidArgumentException( "$hostname is already registered." );
        }
        $repo = new Repository( $path );
        self::$repositoryMappings[$hostname] = $repo;
        $registered = stream_wrapper_register( self::PROTOCOL, __CLASS__ );
    }

    private static function getRepository( string $hostname ): Repository
    {
        return self::$repositoryMappings[$hostname];
    }


    /** @var \adrianclay\git\Repository */
    private $repository;

    /**
     * Returns the blob or tree corresponding to a path
     */
    private function getSHAOfPath( string $path ): ?SHAReference
    {
        $parts = \parse_url( $path );
        $repo = self::getRepository( $parts['host'] );
        if ( !$repo ) {
            return null;
        }
        $this->repository = $repo;
        $reference = new RevisionResolver( new References\Parser( $repo ), $parts['user'] );
        $commit = new Commit( $repo, $reference );
        $nextChild = $currentTree = $commit->getTree();
        $remainingParts = array_filter( explode( '/', $parts['path'] ?? '' ) );
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

    public function stream_open( string $path, string $mode, int $options, ?string &$opened_path ): bool
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

    public function stream_read( int $count ): string
    {
        $read = substr( $this->blob->getContents(), $this->position, $count );
        $this->position += strlen( $read );
        return $read;
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen( $this->blob->getContents() );
    }

    public function stream_stat(): array
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

    public function dir_opendir( string $path ): bool
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