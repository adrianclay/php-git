GIT
====

[![PHP Composer](https://github.com/adrianclay/php-git/actions/workflows/php.yml/badge.svg)](https://github.com/adrianclay/php-git/actions/workflows/php.yml)


Usage
-----

First register your repository path against a hostname.

```php
StreamWrapper::registerRepository( $pathToRepository, $hostname );
```

That hostname then refers to your local git repository, which can be used when constructing a URL to reference files within the repo.

```php
$filePath = "git://branch@hostname/path/under/version/control";
```

Example extracting all files within the src folder pointed to by the main branch:

```php
use adrianclay\git\StreamWrapper;
StreamWrapper::registerRepository( $phpGitRepo, 'adrianclay.php-git' );
var_dump( scandir( 'git://main@adrianclay.php-git/src' ) );

array(9) {
  [0] =>
  string(10) "Commit.php"
  [1] =>
  string(8) "Head.php"
  [2] =>
  string(10) "Object.php"
  [3] =>
  string(14) "Repository.php"
  [4] =>
  string(7) "SHA.php"
  [5] =>
  string(16) "SHAReference.php"
  [6] =>
  string(17) "StreamWrapper.php"
  [7] =>
  string(4) "Tree"
  [8] =>
  string(8) "Tree.php"
}
```