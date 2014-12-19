<?php

namespace adrianclay\git;

class RevisionResolver implements SHAReference
{
    /** @var string */
    private $revision;

    /** @var References */
    private $references;

    /**
     * @param References $references
     * @param string     $revision
     */
    public function __construct( References $references, $revision )
    {
        $this->references = $references;
        $this->revision = $revision;
    }

    /**
     * @return string
     */
    public function getSHA()
    {
        if ( ctype_xdigit( $this->revision ) && strlen( $this->revision ) == 40 ) {
            return $this->revision;
        }
        $refsToTry = [ $this->revision, 'refs/heads/' . $this->revision, 'refs/remotes/' .$this->revision ];
        foreach( $refsToTry as $refToTry ) {
            if ( ( $reference = $this->references->getReference( $refToTry ) ) ) {
                return $reference->getSHA();
            }
        }
    }
}