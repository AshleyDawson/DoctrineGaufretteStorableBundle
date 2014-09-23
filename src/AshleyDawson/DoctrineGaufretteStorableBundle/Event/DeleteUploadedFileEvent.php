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
        $this->entity = $entity;
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