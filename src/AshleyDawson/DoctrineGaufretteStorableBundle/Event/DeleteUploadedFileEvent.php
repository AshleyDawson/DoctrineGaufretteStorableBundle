<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DeleteUploadedFileEvent
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\Event
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
class DeleteUploadedFileEvent extends Event
{
    /**
     * @var object
     */
    private $entity;

    /**
     * Constructor
     *
     * @param object $entity
     */
    public function __construct($entity)
    {
        // Dereference entity to avoid changes that might affect deletion
        $this->entity = clone $entity;
    }

    /**
     * Get entity clone (de-referenced)
     *
     * @return \AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFileTrait
     */
    public function getEntityClone()
    {
        return $this->entity;
    }
}