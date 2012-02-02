<?php
/**
 * Version number to revision number.
 *
 * Usage: php v2r.php NOTES_FILE VERSION
 */

class FileLineReader
{
    public $lines;
    public $index;
    protected $_filePath;
    
    function __construct($filePath)
    {
        $this->_filePath = $filePath;
    }
    
    function nextLine()
    {
        if (!$this->isOpen()) {
            $this->_open();
            return $this->currentLine();
        } else if ($this->eof()) {
            return false;
        } else {
            $this->index++;
            return $this->currentLine();
        }
    }
    
    function currentLine()
    {
        $this->_open();
        if ($this->eof()) {
            return false;
        } else {
            return preg_replace('#\r?\n?$#', '', $this->lines[$this->index]);
        }
    }
    
    function eof()
    {
        return $this->isOpen() && $this->index >= count($this->lines);
    }
    
    function isOpen()
    {
        return isset($this->lines);
    }
    
    protected function _open()
    {
        if (!$this->isOpen()) {
            if (!file_exists($this->_filePath)) {
                throw new Exception("File not found: {$this->_filePath}");
            }
            $this->lines = file($this->_filePath);
            $this->index = 0;
        }        
    }
}

class VersionToRevisionParser
{
    public $reader;
    
    function __construct($filePath)
    {
        $this->reader = new FileLineReader($filePath);
    }
    
    function parse()
    {
        $versionsToRevisions = array();
        while (($line = $this->reader->currentLine()) !== false) {
            if (preg_match('#^Version (\d.*)$#', $line)) {
                $versionInfo = $this->parseVersion();
                $versionsToRevisions[$versionInfo['version']] = $versionInfo['revision'];
            }
        }
        return $versionsToRevisions;
    }
    
    function parseVersion()
    {
        /*
         * Parse the version number.
         */
        preg_match('#^Version (\d.*)$#', $this->reader->currentLine(), $match);
        $versionInfo = array('version' => $match[1], 'revision' => null);
        
        /*
         * Look for the Revision number in the second line.
         */
        if ($this->reader->nextLine() !== false) {
            if (preg_match('#^Revision: (.*)$#', $this->reader->currentLine(), $match)) {
                $versionInfo['revision'] = $match[1];
            }
        }

        /*
         * Skip remaining lines in this release version.
         */
        while (true) {
            $line = $this->reader->nextLine();
            if ($line === false) {
                break;
            } else if (!trim($line)) {
                $this->skipBlankLines();
                break;
            }
        }
        
        return $versionInfo;
    }
    
    function skipBlankLines()
    {
        while (true) {
            $line = $this->reader->currentLine();
            if ($line === false) {
                break;
            } else if (trim($line)) {
                break;
            } else {
                $this->reader->nextLine();
            }
        }
        return $line;
    }
}

if (empty($_SERVER['argv']) || empty($_SERVER['argv'][1]) || empty($_SERVER['argv'][2])) {
    $stderr = fopen('php://stderr', 'w');
    fputs($stderr, "Usage: php v2r.php NOTES_FILE VERSION\n");
    exit(2);
}
$version = $_SERVER['argv'][1];
$notes_file = $_SERVER['argv'][2];
$parser = new VersionToRevisionParser($notes_file);
$versionsToRevisions = $parser->parse();
if (isset($versionsToRevisions[$version])) {
    echo $versionsToRevisions[$version];
} else {
    exit(1);
}
