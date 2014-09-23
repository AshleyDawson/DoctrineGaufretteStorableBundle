<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\EventListener;

use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandlerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
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
            Events::preFlush,
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

    public function prePersist(LifecycleEventArgs $args)
    {
        if ( ! $this->isEntitySupported($args->getEntity())) {
            return;
        }

        $this->storageHandler->writeUploadedFile($args->getEntity());
    }

    /**
     * Listen to preFlush events
     *
     * @param PreFlushEventArgs $args
     * @return void
     */
    public function preFlush(PreFlushEventArgs $args)
    {
        if ( ! $this->isEntitySupported($args->getEmptyInstance())) {
            return;
        }

        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        foreach ($unitOfWork->getIdentityMap() as $identity) {

            foreach ($identity as $entity) {

                if ( ! $this->isEntitySupported($entity)) {
                    continue;
                }

                if ($unitOfWork->isScheduledForUpdate($entity)) {

                    $this->storageHandler->deleteUploadedFile($entity);
                    $this->storageHandler->writeUploadedFile($entity);

                    $unitOfWork->propertyChanged($entity, 'fileName', $entity->getFileName(), $entity->getFileName());
                    $unitOfWork->scheduleForUpdate($entity);
                }
            }
        }
    }

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