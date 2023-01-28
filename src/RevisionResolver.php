<?php

namespace adrianclay\git;

class RevisionResolver implements SHAReference
{
    /** @var string */
    private $revision;

    /** @var References */
    private $references;

    public function __construct( References $references, string $revision )
    {
        $this->references = $references;
        $this->revision = $revision;
    }

    public function getSHA(): string
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