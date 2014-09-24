<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Storage;

use AshleyDawson\DoctrineGaufretteStorableBundle\Event\DeleteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\StorageEvents;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\WriteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException;
use AshleyDawson\DoctrineGaufretteStorableBundle\Exception\UploadedFileNotReadableException;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    public function writeUploadedFile($entity, $canDeletePreviousFile = false)
    {
        $this->throwIfEntityNotSupported($entity);

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        $uploadedFile = $entity->getUploadedFile();

        if ( ! ($uploadedFile instanceof UploadedFile)) {
            return;
        }

        if ( ! $uploadedFile->isReadable()) {
            throw new UploadedFileNotReadableException(
                sprintf('The uploaded file "%s" is not readable', $uploadedFile->getPath()));
        }

        if ($canDeletePreviousFile && $entity->getFileStoragePath()) {

            if ($this->getFilesystemForEntity($entity)->has($entity->getFileStoragePath())) {

                try {
                    $this
                        ->getFilesystemForEntity($entity)
                        ->delete($entity->getFileStoragePath())
                    ;
                }
                catch (\RuntimeException $e) {
                    // todo: should we care if this fails? Maybe log the incident
                }
            }
        }

        $fileContent = file_get_contents($uploadedFile->getPathname());
        $fileName = $fileStoragePath = (string) $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $fileMimeType = $uploadedFile->getMimeType();
        $fileExtension = $uploadedFile->getExtension();

        $this->eventDispatcher->dispatch(StorageEvents::PRE_WRITE, new WriteUploadedFileEvent(
            $entity,
            $fileContent,
            $fileMimeType,
            $fileName,
            $fileSize,
            $fileStoragePath,
            $fileExtension
        ));

        $this
            ->getFilesystemForEntity($entity)
            ->write($fileStoragePath, $fileContent, true)
        ;

        $this->eventDispatcher->dispatch(StorageEvents::POST_WRITE, new WriteUploadedFileEvent(
            $entity,
            $fileContent,
            $fileMimeType,
            $fileName,
            $fileSize,
            $fileStoragePath,
            $fileExtension
        ));

        $entity
            ->setFileName($fileName)
            ->setFileStoragePath($fileStoragePath)
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

        if ($entity->getFileStoragePath()) {

            try {
                $this
                    ->getFilesystemForEntity($entity)
                    ->delete($entity->getFileStoragePath())
                ;
            }
            catch (\RuntimeException $e) {
                // todo: should we care if this fails? Maybe log the incident
            }
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_DELETE, new DeleteUploadedFileEvent(
            $entity
        ));

        $entity
            ->setFileName(null)
            ->setFileStoragePath(null)
            ->setFileSize(null)
            ->setFileMimeType(null)
        ;
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
        if ( ! self::isEntitySupported($entity)) {
            throw new EntityNotSupportedException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function isEntitySupported($entity)
    {
        $traitNames = (new \ReflectionObject($entity))->getTraitNames();
        return in_array(EntityStorageHandlerInterface::UPLOADED_FILE_TRAIT_NAME, $traitNames);
    }

    /**
     * Try to get the filesystem for the entity passed
     *
     * @param object $entity
     * @return \Gaufrette\Filesystem
     */
    private function getFilesystemForEntity($entity)
    {
        return $this
            ->filesystemMap
            ->get($entity->getFilesystemMapId())
        ;
    }
}