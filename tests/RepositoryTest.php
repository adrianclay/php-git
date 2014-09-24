<?php
use adrianclay\git\Repository;

class RepositoryTest extends PHPUnit_Framework_TestCase {

    /**
     * Trying to open the repository of this project should not raise an exception
     */
    public function testConstructor()
    {
        $path = __DIR__ . '/../';
        new Repository( $path );
    }
}
