<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile;

use Symfony\Component\HttpFoundation\File\UploadedFile as HTTPUploadedFile;

/**
 * Trait UploadedFileTrait
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
trait UploadedFileTrait
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var int Size in bytes
     */
    private $fileSize;

    /**
     * @var string
     */
    private $fileMimeType;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $uploadedFile;

    /**
     * Get the Gaufrette filesystem map id as
     * configured in https://github.com/KnpLabs/KnpGaufretteBundle#configuring-the-filesystems
     *
     * @return string
     */
    abstract public function getFilesystemMapId();

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
     * Get uploadedFile
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * Set uploadedFile
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|null $uploadedFile
     * @return $this
     */
    public function setUploadedFile(HTTPUploadedFile $uploadedFile = null)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }
}