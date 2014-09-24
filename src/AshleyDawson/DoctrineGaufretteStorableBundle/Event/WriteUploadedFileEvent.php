<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class WriteUploadedFileEvent
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\Event
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
class WriteUploadedFileEvent extends Event
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileContent;

    /**
     * @var int
     */
    private $fileSize;

    /**
     * @var string
     */
    private $fileMimeType;

    /**
     * @var object
     */
    private $entity;

    /**
     * @var string
     */
    private $fileStoragePath;

    /**
     * @var string
     */
    private $fileExtension;

    /**
     * Constructor
     *
     * @param object $entity
     * @param string $fileContent
     * @param string $fileMimeType
     * @param string $fileName
     * @param int $fileSize
     * @param string $fileStoragePath
     * @param string $fileExtension
     */
    public function __construct($entity, $fileContent, $fileMimeType, $fileName, $fileSize, $fileStoragePath, $fileExtension)
    {
        $this->entity = $entity;
        $this->fileContent = $fileContent;
        $this->fileMimeType = $fileMimeType;
        $this->fileName = $fileName;
        $this->fileSize = $fileSize;
        $this->fileStoragePath = $fileStoragePath;
        $this->fileExtension = $fileExtension;
    }

    /**
     * Get fileContent
     *
     * @return string
     */
    public function getFileContent()
    {
        return $this->fileContent;
    }

    /**
     * Set fileContent
     *
     * @param string $fileContent
     * @return $this
     */
    public function setFileContent($fileContent)
    {
        $this->fileContent = $fileContent;
        return $this;
    }

    /**
     * Get fileMimeType
     *
     * @return string
     */
    public function getFileMimeType()
    {
        return $this->fileMimeType;
    }

    /**
     * Set fileMimeType
     *
     * @param string $fileMimeType
     * @return $this
     */
    public function setFileMimeType($fileMimeType)
    {
        $this->fileMimeType = $fileMimeType;
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get fileSize
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Get entity
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get fileStoragePath
     *
     * @return string
     */
    public function getFileStoragePath()
    {
        return $this->fileStoragePath;
    }

    /**
     * Set fileStoragePath
     *
     * @param string $fileStoragePath
     * @return $this
     */
    public function setFileStoragePath($fileStoragePath)
    {
        $this->fileStoragePath = $fileStoragePath;
        return $this;
    }

    /**
     * Get fileExtension
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * Set fileExtension
     *
     * @param string $fileExtension
     * @return $this
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
        return $this;
    }
}