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

    /**
     * @param string    $name
     * @param string    $email
     * @param \DateTime $dateTime
     */
    public function __construct( $name, $email, $dateTime )
    {
        $this->name = $name;
        $this->email = $email;
        $this->dateTime = $dateTime;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param string $oneLiner
     * @return Signature
     */
    public static function parseSignature( $oneLiner )
    {
        preg_match( '@^(?P<name>.*) (<(?P<email>.*)>)? (?P<timestamp>[0-9]+) (?<offset>[\+|-][0-9]{4})$@', $oneLiner, $matches );
        $dateTime = new \DateTime( '@' . $matches['timestamp'] );
        $dateTime->setTimezone( new \DateTimeZone( $matches['offset'] ) );
        return new Signature( $matches['name'], $matches['email'], $dateTime );
    }
}