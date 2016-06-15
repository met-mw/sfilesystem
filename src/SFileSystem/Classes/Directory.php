<?php
namespace SFileSystem\Classes;


use Exception;
use SFileSystem\Interfaces\InterfaceIODirectory;

class Directory extends IO implements InterfaceIODirectory
{

    /** @var Directory[] */
    protected $Directories = [];
    /** @var File[] */
    protected $Files = [];

    /**
     * @param InterfaceIODirectory $Directory
     * @return bool
     */
    public function copyTo(InterfaceIODirectory $Directory)
    {
        if (!$Directory->exists()) {
            return false;
        }

        $NewDirectory = new Directory($Directory->getPath() . DIRECTORY_SEPARATOR . $this->getName());
        $this->scan();
        foreach ($this->getFiles() as $File) {
            if (!$File->copyTo($NewDirectory)) {
                return false;
            }
        }

        foreach ($this->getDirectories() as $Directory) {
            if (!$Directory->copyTo($NewDirectory)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function create()
    {
        if ($this->exists()) {
            throw new Exception("Директория \"{$this->getPath()}\" уже существует.");
        }

        mkdir($this->getPath());
        return $this;
    }

    /**
     * @param string $directoryName
     * @return null|Directory
     * @throws Exception
     */
    public function createDirectory($directoryName)
    {
        if (!$this->exists()) {
            return null;
        }

        $NewDirectory = new Directory($this->getPath() . DIRECTORY_SEPARATOR . $directoryName);
        return $NewDirectory->create();
    }

    /**
     * @param string $fileName
     * @return null|File
     * @throws Exception
     */
    public function createFile($fileName)
    {
        if (!$this->exists()) {
            return null;
        }

        $NewFile = new File($this->getPath() . DIRECTORY_SEPARATOR . $fileName);
        return $NewFile->create();
    }

    /**
     * @return $this
     */
    public function delete()
    {
        if (!$this->exists()) {
            return $this;
        }

        $this->scan();
        foreach ($this->getDirectories() as $Directory) {
            $Directory->delete();
        }

        foreach ($this->getFiles() as $File) {
            $File->delete();
        }

        rmdir($this->path);
        return $this;
    }

    /**
     * @param string $directoryName
     * @return null|Directory
     */
    public function getDirectory($directoryName)
    {
        if (!is_dir($this->getPath() . DIRECTORY_SEPARATOR . $directoryName)) {
            return null;
        }

        return new Directory($this->getPath() . DIRECTORY_SEPARATOR . $directoryName);
    }

    /**
     * @return Directory[]
     */
    public function getDirectories()
    {
        return $this->Directories;
    }

    /**
     * @param string $fileName
     * @return null|File
     */
    public function getFile($fileName)
    {
        if (!is_file($this->getPath() . DIRECTORY_SEPARATOR . $fileName)) {
            return null;
        }

        return new File($this->getPath() . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        return $this->Files;
    }

    /**
     * @param InterfaceIODirectory $Directory
     * @return bool
     */
    public function moveTo(InterfaceIODirectory $Directory)
    {
        if (!$Directory->exists()) {
            return false;
        }

        $success = false;
        if ($this->copyTo($Directory)) {
            $this->delete();
            $success = true;
        }

        return $success;
    }

    /**
     * @param bool|false $recursive
     * @return $this
     */
    public function scan($recursive = false)
    {
        $this->Directories = [];
        $this->Files = [];

        $elements = scandir($this->getPath());
        $elements = array_diff($elements, ['.', '..']);

        foreach ($elements as $element) {
            $elementPath = $this->path . DIRECTORY_SEPARATOR . $element;
            if (is_dir($elementPath)) {
                $Directory = new Directory($elementPath);
                $this->Directories[] = $Directory;
                if ($recursive) {
                    $Directory->scan($recursive);
                }
            } else {
                $this->Files[] = new File($elementPath);
            }
        }

        return $this;
    }

}