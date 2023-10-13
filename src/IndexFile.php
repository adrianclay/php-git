<?php

namespace adrianclay\git;

/**
 * @see https://git-scm.com/docs/index-format
 * @todo Support more functionality + extensions
 */
class IndexFile implements \Countable, \Iterator
{
    /**
     * @var false|resource
     */
    private $indexFile;

    /**
     * @var int
     */
    private $iteratorIndex;

    /**
     * @var int
     */
    private $count;

    /**
     * @var IndexEntry
     */
    private $current;

    public function __construct( string $string )
    {
        $this->indexFile = \fopen($string, 'r');
        $this->rewind();
    }

    private function _count(): void
    {
        \fseek($this->indexFile, 0);
        $headerString = \fread($this->indexFile, 12);
        $header = \unpack(\implode('/', ['Nversion', 'NindexEntries']), $headerString, 4);
        $this->count = $header['indexEntries'];
    }

    public function count(): int
    {
        return $this->count;
    }

    private function readIndexEntry(): IndexEntry
    {
        // 32-bit ctime seconds, the last time a file's metadata changed
        // 32-bit ctime nanosecond fractions
        // 32-bit mtime seconds, the last time a file's data changed
        // 32-bit mtime nanosecond fractions
        // 32-bit dev
        // 32-bit ino
        // 32-bit mode, split into (high to low bits)
        // 4-bit object type
        // 3-bit unused
        // 9-bit unix permission. Only 0755 and 0644 are valid for regular files.
        // 32-bit uid
        // 32-bit gid
        // 32-bit file size
        // Object name for the represented object (20 bytes)
        // A 16-bit 'flags' field split into (high to low bits)
        //
        \fseek($this->indexFile, 40, SEEK_CUR);
        $objectName = fread($this->indexFile, GitObject::SHA_BIN_SIZE);
        \fseek($this->indexFile, 2, SEEK_CUR);
        $name = $this->read_null_terminated_name_string();
        return new IndexEntry($name, bin2hex($objectName));
    }

    private function read_null_terminated_name_string(): string
    {
        $name = '';
        while(($char = fgetc($this->indexFile)) !== false) {
            if(!ord($char)) {
                break;
            }
            $name .= $char;
        }
        $padding = 8 - (6 + strlen($name) + 1) % 8;
        if($padding == 8) {
            $padding = 0;
        }
        \fseek($this->indexFile, $padding, SEEK_CUR);
        return $name;
    }

    public function next(): void
    {
        $this->iteratorIndex++;
        $this->current = $this->readIndexEntry();
    }

    public function key(): int
    {
        return $this->iteratorIndex;
    }

    public function valid(): bool
    {
        return $this->iteratorIndex < $this->count();
    }

    public function rewind(): void
    {
        $this->iteratorIndex = 0;
        $this->_count();
        $this->current = $this->readIndexEntry();
    }

    public function current(): IndexEntry
    {
        return $this->current;
    }
}

class IndexEntry implements SHAReference {
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $sha;

    public function __construct(string $name, string $sha)
    {
        $this->name = $name;
        $this->sha = $sha;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function getSHA(): string
    {
        return $this->sha;
    }
}