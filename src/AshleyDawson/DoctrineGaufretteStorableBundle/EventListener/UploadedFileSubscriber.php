<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Class UploadedFileSubscriber
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\EventListener
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
class UploadedFileSubscriber implements EventSubscriber
{
    /**
     * UploadedFileTrait fully qualified class name
     */
    const UPLOADED_FILE_TRAIT_NAME
        = 'AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile\UploadedFileTrait';

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * Listen to loadClassMetadata events
     *
     * @param LoadClassMetadataEventArgs $args
     * @return void
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        if ( ! $this->isEntitySupported($args)) {
            return;
        }

        $this->mapFields($args);
    }

    /**
     * Returns TRUE if the passed entity is supported by this listener
     *
     * @param LoadClassMetadataEventArgs $args
     * @return bool
     */
    private function isEntitySupported(LoadClassMetadataEventArgs $args)
    {
        return in_array(self::UPLOADED_FILE_TRAIT_NAME,
            $args->getClassMetadata()->getReflectionClass()->getTraitNames());
    }

    /**
     * Map fields to entity
     *
     * @param LoadClassMetadataEventArgs $args
     */
    private function mapFields(LoadClassMetadataEventArgs $args)
    {
        $meta = $args->getClassMetadata();

        $meta
            ->mapField([
                'fieldName' => 'fileName',
                'columnName' => 'file_name',
                'type' => 'string',
                'length' => 255,
            ])
        ;

        $meta->mapField([
                'fieldName' => 'fileSize',
                'columnName' => 'file_size',
                'type' => 'integer',
            ])
        ;

        $meta->mapField([
                'fieldName' => 'fileMimeType',
                'columnName' => 'file_mime_type',
                'type' => 'string',
                'length' => 130,
            ])
        ;
    }
}