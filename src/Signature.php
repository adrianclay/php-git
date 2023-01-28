<?php

namespace adrianclay\git;

class Signature
{
    /** @var string */
    private $name;
    /** @var string */
    private $email;
    /** @var \DateTime */
    private $dateTime;

    public function __construct( string $name, string $email, \DateTime $dateTime )
    {
        $this->name = $name;
        $this->email = $email;
        $this->dateTime = $dateTime;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public static function parseSignature( string $oneLiner ): Signature
    {
        preg_match( '@^(?P<name>.*) (<(?P<email>.*)>)? (?P<timestamp>[0-9]+) (?<offset>[\+|-][0-9]{4})$@', $oneLiner, $matches );
        $dateTime = new \DateTime( '@' . $matches['timestamp'] );
        // Workaround for https://bugs.php.net/bug.php?id=45528
        $offsetTimezone = \DateTime::createFromFormat( 'O', $matches['offset'] )->getTimezone();
        $dateTime->setTimezone( $offsetTimezone );
        return new Signature( $matches['name'], $matches['email'], $dateTime );
    }
}