<?php
namespace adrianclay\git;

interface GitObject
{
    const TYPE_COMMIT = 'commit';
    const TYPE_BLOB = 'blob';
    const TYPE_TREE = 'tree';

    const SHA_BIN_SIZE = 20;
    const SHA_HEX_SIZE = 40;

    /** @return string */
    public function getType();

    /** @return string */
    public function getData();
}