<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EventListener;

use AshleyDawson\DoctrineGaufretteStorableBundle\Event\DeleteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\StorageEvents;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\WriteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\EventListener\UploadedFileSubscriber;
use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandler;
use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandlerInterface;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EntityManagerProvider;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\EntityWithoutUploadedFileTrait;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Model\UploadedFileTraitTest;
use Doctrine\Common\EventManager;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherSingleton
{
    private static $instance;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    private function __construct()
    {
        $this->eventDispatcher = new EventDispatcher();
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public static function getInstance()
    {
        if ( ! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

class UploadedFileSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity',
            'AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\EntityWithoutUploadedFileTrait',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager();

        $adapter = new LocalAdapter(TESTS_TEMP_DIR);
        $filesystem = new Filesystem($adapter);

        $filesystemMap = new FilesystemMap([
            UploadedFileTraitTest::MOCK_FILESYSTEM_MAP_ID => $filesystem,
        ]);

        $storageHandler = new EntityStorageHandler(
            $filesystemMap,
            EventDispatcherSingleton::getInstance()->getEventDispatcher()
        );

        $uploadFileSubscriber = new UploadedFileSubscriber(
            $storageHandler
        );

        $em->addEventSubscriber(
            $uploadFileSubscriber
        );

        return $em;
    }

    public function testTraitInclusion()
    {
        $reflection = new \ReflectionClass('AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity');

        $this->assertTrue(in_array(EntityStorageHandlerInterface::UPLOADED_FILE_TRAIT_NAME, $reflection->getTraitNames()));
    }

    public function testPersistEntity()
    {
        $em = $this->getEntityManager();

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name')
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Entity Name', $entity->getName());
        $this->assertNotNull($entity->getFileName());
        $this->assertNotNull($entity->getFileStoragePath());
        $this->assertNotNull($entity->getFileMimeType());
        $this->assertNotNull($entity->getFileSize());
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');
    }

    public function testUpdateEntity()
    {
        $em = $this->getEntityManager();

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name Two')
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $fileNameBeforeUpdate = $entity->getFileName();
        $fileStoragePathBeforeUpdate = $entity->getFileStoragePath();
        $fileMimeTypeBeforeUpdate = $entity->getFileMimeType();
        $fileSizeBeforeUpdate = $entity->getFileSize();

        $this->assertEquals('Entity Name Two', $entity->getName());
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $entity->setUploadedFile($this->getTestUploadedFileTwo());

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Entity Name Two', $entity->getName());
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-two.jpg');
        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $this->assertNotNull($entity->getFileName());
        $this->assertNotNull($entity->getFileStoragePath());
        $this->assertNotNull($entity->getFileMimeType());
        $this->assertNotNull($entity->getFileSize());

        $this->assertNotEquals($fileNameBeforeUpdate, $entity->getFileName());
        $this->assertNotEquals($fileStoragePathBeforeUpdate, $entity->getFileStoragePath());
        $this->assertNotEquals($fileMimeTypeBeforeUpdate, $entity->getFileMimeType());
        $this->assertNotEquals($fileSizeBeforeUpdate, $entity->getFileSize());
    }

    public function testPersistEntityWithoutUploadedFile()
    {
        $em = $this->getEntityManager();

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name Three')
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Entity Name Three', $entity->getName());
        $this->assertNull($entity->getFileName());
        $this->assertNull($entity->getFileStoragePath());
        $this->assertNull($entity->getFileMimeType());
        $this->assertNull($entity->getFileSize());
    }

    public function testUpdateEntityWithoutUploadedFile()
    {
        $em = $this->getEntityManager();

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name Two')
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $fileNameBeforeUpdate = $entity->getFileName();
        $fileStoragePathBeforeUpdate = $entity->getFileStoragePath();
        $fileMimeTypeBeforeUpdate = $entity->getFileMimeType();
        $fileSizeBeforeUpdate = $entity->getFileSize();

        $this->assertEquals('Entity Name Two', $entity->getName());
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $entity->setUploadedFile(null);

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Entity Name Two', $entity->getName());
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $this->assertNotNull($entity->getFileName());
        $this->assertNotNull($entity->getFileStoragePath());
        $this->assertNotNull($entity->getFileMimeType());
        $this->assertNotNull($entity->getFileSize());

        $this->assertEquals($fileNameBeforeUpdate, $entity->getFileName());
        $this->assertEquals($fileStoragePathBeforeUpdate, $entity->getFileStoragePath());
        $this->assertEquals($fileMimeTypeBeforeUpdate, $entity->getFileMimeType());
        $this->assertEquals($fileSizeBeforeUpdate, $entity->getFileSize());
    }

    public function testRemoveEntity()
    {
        $em = $this->getEntityManager();

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name')
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Entity Name', $entity->getName());
        $this->assertNotNull($entity->getFileName());
        $this->assertNotNull($entity->getFileStoragePath());
        $this->assertNotNull($entity->getFileMimeType());
        $this->assertNotNull($entity->getFileSize());
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $em->remove($entity);
        $em->flush();

        $this->assertFalse($em->contains($entity));
        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-image-one.gif');
    }

    public function testRemoveEntityWithoutUploadedFile()
    {
        $em = $this->getEntityManager();

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name')
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Entity Name', $entity->getName());
        $this->assertNull($entity->getFileName());
        $this->assertNull($entity->getFileStoragePath());
        $this->assertNull($entity->getFileMimeType());
        $this->assertNull($entity->getFileSize());

        $em->remove($entity);
        $em->flush();

        $this->assertFalse($em->contains($entity));
    }

    public function testUnsupportedEntityPersist()
    {
        $em = $this->getEntityManager();

        $entity = (new EntityWithoutUploadedFileTrait())
            ->setName('Unsupported Entity Name One')
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Unsupported Entity Name One', $entity->getName());
    }

    public function testUnsupportedEntityUpdate()
    {
        $em = $this->getEntityManager();

        $entity = (new EntityWithoutUploadedFileTrait())
            ->setName('Unsupported Entity Name One')
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Unsupported Entity Name One', $entity->getName());

        $entity->setName('Unsupported Entity Name Two');

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Unsupported Entity Name Two', $entity->getName());
    }

    public function testUnsupportedEntityRemove()
    {
        $em = $this->getEntityManager();

        $entity = (new EntityWithoutUploadedFileTrait())
            ->setName('Unsupported Entity Name One')
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Unsupported Entity Name One', $entity->getName());

        $em->remove($entity);
        $em->flush();

        $this->assertFalse($em->contains($entity));
    }

    public function testEntityStorageHandlerWriteEvents()
    {
        $em = $this->getEntityManager();

        EventDispatcherSingleton::getInstance()->getEventDispatcher()->addListener(StorageEvents::PRE_WRITE, function (WriteUploadedFileEvent $event) {
            $this->assertEquals('sample-image-one.gif', $event->getFileName());
            $this->assertEquals('sample-image-one.gif', $event->getFileStoragePath());
            $event->setFileStoragePath('foob-schmidt.gif');
        });

        EventDispatcherSingleton::getInstance()->getEventDispatcher()->addListener(StorageEvents::POST_WRITE, function (WriteUploadedFileEvent $event) {
            $this->assertEquals('sample-image-one.gif', $event->getFileName());
            $this->assertEquals('foob-schmidt.gif', $event->getFileStoragePath());
        });

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name')
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('sample-image-one.gif', $entity->getFileName());
        $this->assertEquals('foob-schmidt.gif', $entity->getFileStoragePath());
        $this->assertFileExists(TESTS_TEMP_DIR . '/foob-schmidt.gif');
    }

    public function testEntityStorageHandlerDeleteEvents()
    {
        $em = $this->getEntityManager();

        EventDispatcherSingleton::getInstance()->getEventDispatcher()->addListener(StorageEvents::PRE_DELETE, function (DeleteUploadedFileEvent $event) {
            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileName());
            $this->assertEquals('foob-schmidt.gif', $event->getEntityClone()->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getEntityClone()->getFileMimeType());
            $this->assertGreaterThan(0, $event->getEntityClone()->getFileSize());
        });

        EventDispatcherSingleton::getInstance()->getEventDispatcher()->addListener(StorageEvents::POST_DELETE, function (DeleteUploadedFileEvent $event) {
            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileName());
            $this->assertEquals('foob-schmidt.gif', $event->getEntityClone()->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getEntityClone()->getFileMimeType());
            $this->assertGreaterThan(0, $event->getEntityClone()->getFileSize());
        });

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name Two')
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        // As the event listener is a singleton, these tests are inherited from the test @see self::testEntityStorageHandlerWriteEvents()
        // todo: don't use singleton event listener
        $this->assertEquals('foob-schmidt.gif', $entity->getFileStoragePath());
        $this->assertFileExists(TESTS_TEMP_DIR . '/foob-schmidt.gif');

        $em->remove($entity);
        $em->flush();

        $this->assertFalse($em->contains($entity));

        $this->assertNull($entity->getFileName());
        $this->assertNull($entity->getFileStoragePath());
        $this->assertNull($entity->getFileMimeType());
        $this->assertNull($entity->getFileSize());
    }

    public function tearDown()
    {
        @unlink(TESTS_TEMP_DIR . '/sample-image-one.gif');
        @unlink(TESTS_TEMP_DIR . '/sample-image-two.jpg');
        @unlink(TESTS_TEMP_DIR . '/foob-schmidt.gif');
    }

    /**
     * @return UploadedFile
     */
    private function getTestUploadedFileOne()
    {
        $path = __DIR__ . '/../Resources/fixtures/file/sample-image-one.gif';

        return new UploadedFile($path, 'sample-image-one.gif', 'image/gif', filesize($path));
    }

    /**
     * @return UploadedFile
     */
    private function getTestUploadedFileTwo()
    {
        $path = __DIR__ . '/../Resources/fixtures/file/sample-image-two.jpg';

        return new UploadedFile($path, 'sample-image-two.jpg', 'image/jpg', filesize($path));
    }
}