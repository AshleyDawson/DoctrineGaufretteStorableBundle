<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\EventListener;

use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandlerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
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
     * @var EntityStorageHandlerInterface
     */
    private $storageHandler;

    /**
     * Constructor
     *
     * @param EntityStorageHandlerInterface $storageHandler
     */
    public function __construct(EntityStorageHandlerInterface $storageHandler)
    {
        $this->storageHandler = $storageHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
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
        if ( ! $this->isEntitySupported($args->getEmptyInstance())) {
            return;
        }

        $this->mapFields($args);
    }

    // todo: write persist, update and remove handlers, firing the $this->storageHandler methods

    /**
     * Returns TRUE if the passed entity is supported by this listener
     *
     * @param object $entity
     * @return bool
     */
    private function isEntitySupported($entity)
    {
        $clazz = get_class($this->storageHandler);
        return $clazz::isEntitySupported($entity);
    }

    /**
     * Map fields to entity
     *
     * @param LoadClassMetadataEventArgs $args
     */
    private function mapFields(LoadClassMetadataEventArgs $args)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadataInfo $meta */
        $meta = $args->getClassMetadata();

        $meta
            ->mapField([
                'fieldName' => 'fileName',
                'columnName' => 'file_name',
                'type' => 'string',
                'length' => 255,
            ])
        ;

        $meta
            ->mapField([
                'fieldName' => 'fileSize',
                'columnName' => 'file_size',
                'type' => 'integer',
            ])
        ;

        $meta
            ->mapField([
                'fieldName' => 'fileMimeType',
                'columnName' => 'file_mime_type',
                'type' => 'string',
                'length' => 130,
            ])
        ;
    }
}