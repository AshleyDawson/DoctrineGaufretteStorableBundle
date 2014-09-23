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
     * Constructor
     *
     * @param object $entity
     * @param string $fileContent
     * @param string $fileMimeType
     * @param string $fileName
     * @param int $fileSize
     */
    public function __construct($entity, $fileContent, $fileMimeType, $fileName, $fileSize)
    {
        $this->entity = $entity;
        $this->fileContent = $fileContent;
        $this->fileMimeType = $fileMimeType;
        $this->fileName = $fileName;
        $this->fileSize = $fileSize;
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
     * Set fileSize
     *
     * @param int $fileSize
     * @return $this
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
        return $this;
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
     * Set entity
     *
     * @param object $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }
}