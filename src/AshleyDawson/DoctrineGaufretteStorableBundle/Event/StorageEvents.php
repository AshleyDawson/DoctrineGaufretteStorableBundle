<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Event;

/**
 * Class StorageEvents
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\Event
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
class StorageEvents
{
    /**
     * Pre write event name
     * @see \AshleyDawson\DoctrineGaufretteStorableBundle\Event\WriteUploadedFileEvent
     */
    const PRE_WRITE = 'ad_doctrine_gaufrette_storable.pre_write';

    /**
     * Post write event name
     * @see \AshleyDawson\DoctrineGaufretteStorableBundle\Event\WriteUploadedFileEvent
     */
    const POST_WRITE = 'ad_doctrine_gaufrette_storable.post_write';

    /**
     * Pre delete event name
     * @see \AshleyDawson\DoctrineGaufretteStorableBundle\Event\DeleteUploadedFileEvent
     */
    const PRE_DELETE = 'ad_doctrine_gaufrette_storable.pre_delete';

    /**
     * Post delete event name
     * @see \AshleyDawson\DoctrineGaufretteStorableBundle\Event\DeleteUploadedFileEvent
     */
    const POST_DELETE = 'ad_doctrine_gaufrette_storable.post_delete';
}