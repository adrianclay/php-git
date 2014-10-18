<?php

class StreamWrapperTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $repoDirectory = __DIR__ . '/../';
        \adrianclay\git\StreamWrapper::registerRepository( $repoDirectory, 'adrianclay.php-git' );
    }

    public function testListDirectory()
    {
        $directory = scandir( 'git://80f3de79356430966c66559828d553ff1ba5a76b@adrianclay.php-git' );
        $this->assertSame( array( '.gitignore', 'LICENSE.txt', 'composer.json', 'src' ), $directory );
    }

    public function testFileRead()
    {
        $gitIgnore = file_get_contents( 'git://80f3de79356430966c66559828d553ff1ba5a76b@adrianclay.php-git/.gitignore' );
        $this->assertEquals( "vendor/", $gitIgnore );
    }
}