<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Storage;

use AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException;
use AshleyDawson\DoctrineGaufretteStorableBundle\Exception\UploadedFileNotReadableException;
use Knp\Bundle\GaufretteBundle\FilesystemMap;

/**
 * Class EntityStorageHandler
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\Storage
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
class EntityStorageHandler implements EntityStorageHandlerInterface
{
    /**
     * @var FilesystemMap
     */
    private $filesystemMap;

    /**
     * Constructor
     *
     * @param FilesystemMap $filesystemMap
     */
    public function __construct(FilesystemMap $filesystemMap)
    {
        $this->filesystemMap = $filesystemMap;
    }

    /**
     * {@inheritdoc}
     */
    public function writeUploadedFile($entity)
    {
        $this->throwIfEntityNotSupported($entity);

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        $uploadedFile = $entity->getUploadedFile();
        if ( ! $uploadedFile->isReadable()) {
            throw new UploadedFileNotReadableException(
                sprintf('The uploaded file "%s" is not readable', $uploadedFile->getPath()));
        }

        $fileName = $uploadedFile->getClientOriginalName();
        $fileContent = file_get_contents($uploadedFile->getPath());
        $fileSize = filesize($uploadedFile->getPath());
        $fileMimeType = $uploadedFile->getMimeType();

        // todo: fire an event here (pre write)

        $this
            ->filesystemMap
            ->get($entity->getFilesystemMapId())
            ->write($fileName, $fileContent, true)
        ;

        // todo: fire an event here (post write)

        $entity
            ->setFileName($fileName)
            ->setFileSize($fileSize)
            ->setFileMimeType($fileMimeType)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUploadedFile($entity)
    {
        $this->throwIfEntityNotSupported($entity);

        // todo: fire an event here (pre delete)

        $this
            ->filesystemMap
            ->get($entity->getFilesystemMapId())
            ->delete($entity->getFileName())
        ;

        // todo: fire an event here (post delete)
    }

    /**
     * Throws an exception if the entity is not supported
     *
     * @param object $entity
     * @throws \AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException
     * @return void
     */
    private function throwIfEntityNotSupported($entity)
    {
        $traitNames = (new \ReflectionObject($entity))->getTraitNames();
        if ( ! in_array(EntityStorageHandlerInterface::UPLOADED_FILE_TRAIT_NAME, $traitNames)) {
            throw new EntityNotSupportedException();
        }
    }
}