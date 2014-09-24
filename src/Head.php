<?php
namespace adrianclay\git;


class Head implements SHAReference
{
    /** @var string */
    private $shaHash;

    /**
     * @param Repository $repo
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function __construct( Repository $repo, $name = "" )
    {
        $d = DIRECTORY_SEPARATOR;
        if ( !$name ) {
            $headFile = $repo->getGitPath() . $d . "HEAD";
            sscanf( file_get_contents( $headFile ), "ref: %s", $referenceFile );
            $referenceFile = $repo->getGitPath() . $d . $referenceFile;
        } else {
            $referenceFile = $repo->getGitPath() . $d . "refs" . $d . "heads" . $d . $name;
        }
        if ( !file_exists( $referenceFile ) ) {
            throw new \InvalidArgumentException;
        }
        $shaHash = file_get_contents( $referenceFile );
        $this->shaHash = trim( $shaHash );
    }

    /**
     * @return string
     */
    public function getSHA()
    {
        return $this->shaHash;
    }
}