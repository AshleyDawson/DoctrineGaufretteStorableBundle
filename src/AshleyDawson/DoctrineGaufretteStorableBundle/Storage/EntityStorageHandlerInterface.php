<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Storage;

use AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile\UploadedFileTrait;

/**
 * Interface EntityStorageHandlerInterface
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\Storage
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
interface EntityStorageHandlerInterface
{
    /**
     * UploadedFileTrait fully qualified class name
     */
    const UPLOADED_FILE_TRAIT_NAME
        = 'AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile\UploadedFileTrait';

    /**
     * Write the uploaded file from an entity using the
     * trait: @see AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile\UploadedFileTrait
     *
     * @param object $entity
     * @throws \AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException
     * @throws \AshleyDawson\DoctrineGaufretteStorableBundle\Exception\UploadedFileNotReadableException
     * @return void
     */
    public function writeUploadedFile($entity);

    /**
     * Delete the uploaded file from an entity using the
     * trait: @see AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile\UploadedFileTrait
     *
     * @param object $entity
     * @throws \AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException
     * @return void
     */
    public function deleteUploadedFile($entity);

    /**
     * Returns TRUE if the entity passed is supported by this handler
     *
     * @param object $entity
     * @return bool
     */
    public static function isEntitySupported($entity);
}