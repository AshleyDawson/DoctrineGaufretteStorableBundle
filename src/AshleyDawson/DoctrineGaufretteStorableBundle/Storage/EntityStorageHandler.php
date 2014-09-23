<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Storage;

use AshleyDawson\DoctrineGaufretteStorableBundle\Event\DeleteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\StorageEvents;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\WriteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException;
use AshleyDawson\DoctrineGaufretteStorableBundle\Exception\UploadedFileNotReadableException;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor
     *
     * @param FilesystemMap $filesystemMap
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(FilesystemMap $filesystemMap, EventDispatcherInterface $eventDispatcher)
    {
        $this->filesystemMap = $filesystemMap;
        $this->eventDispatcher = $eventDispatcher;
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

        $this->eventDispatcher->dispatch(StorageEvents::PRE_WRITE, new WriteUploadedFileEvent(
            $entity,
            $fileContent,
            $fileMimeType,
            $fileName,
            $fileSize
        ));

        $this
            ->filesystemMap
            ->get($entity->getFilesystemMapId())
            ->write($fileName, $fileContent, true)
        ;

        $this->eventDispatcher->dispatch(StorageEvents::POST_WRITE, new WriteUploadedFileEvent(
            $entity,
            $fileContent,
            $fileMimeType,
            $fileName,
            $fileSize
        ));

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

        $this->eventDispatcher->dispatch(StorageEvents::PRE_DELETE, new DeleteUploadedFileEvent(
            $entity
        ));

        $this
            ->filesystemMap
            ->get($entity->getFilesystemMapId())
            ->delete($entity->getFileName())
        ;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_DELETE, new DeleteUploadedFileEvent(
            $entity
        ));
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