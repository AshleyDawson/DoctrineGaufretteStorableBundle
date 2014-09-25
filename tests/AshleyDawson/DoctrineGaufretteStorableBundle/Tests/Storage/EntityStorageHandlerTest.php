<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Storage;

use AshleyDawson\DoctrineGaufretteStorableBundle\Event\DeleteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\StorageEvents;
use AshleyDawson\DoctrineGaufretteStorableBundle\Event\WriteUploadedFileEvent;
use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandler;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\EntityWithoutUploadedFileTrait;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Model\UploadedFileTraitTest;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class EntityStorageHandlerTest
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Storage
 *
 * @author Ashley Dawson
 */
class EntityStorageHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityStorageHandler
     */
    private $storageHandler;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    protected function setUp()
    {
        $this->eventDispatcher = new EventDispatcher();

        $adapter = new LocalAdapter(TESTS_TEMP_DIR);
        $filesystem = new Filesystem($adapter);

        $filesystemMap = new FilesystemMap([
            UploadedFileTraitTest::MOCK_FILESYSTEM_MAP_ID => $filesystem,
        ]);

        $this->storageHandler = new EntityStorageHandler(
            $filesystemMap,
            $this->eventDispatcher
        );
    }

    public function testWriteUploadedFile()
    {
        $entity = (new UploadedFileEntity())
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $this->storageHandler->writeUploadedFile($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $this->assertNotNull($entity->getFileName());
        $this->assertNotNull($entity->getFileStoragePath());
        $this->assertNotNull($entity->getFileMimeType());
        $this->assertNotNull($entity->getFileSize());
    }

    public function testDeleteUploadedFile()
    {
        $entity = (new UploadedFileEntity())
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $this->storageHandler->writeUploadedFile($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $this->storageHandler->deleteUploadedFile($entity);

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $this->assertNull($entity->getFileName());
        $this->assertNull($entity->getFileStoragePath());
        $this->assertNull($entity->getFileMimeType());
        $this->assertNull($entity->getFileSize());
    }

    public function testOverwriteUploadedFile()
    {
        $entity = (new UploadedFileEntity())
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $this->storageHandler->writeUploadedFile($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');

        $this->assertNotNull($oldFileName = $entity->getFileName());
        $this->assertNotNull($oldFileStoragePath = $entity->getFileStoragePath());
        $this->assertNotNull($oldFileMimeType = $entity->getFileMimeType());
        $this->assertNotNull($oldFileSize = $entity->getFileSize());

        $entity
            ->setUploadedFile($this->getTestUploadedFileTwo())
        ;

        $this->storageHandler->writeUploadedFile($entity, true);

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-image-one.gif');
        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-two.jpg');

        $this->assertNotNull($entity->getFileName());
        $this->assertNotNull($entity->getFileStoragePath());
        $this->assertNotNull($entity->getFileMimeType());
        $this->assertNotNull($entity->getFileSize());

        $this->assertNotEquals($oldFileName, $entity->getFileName());
        $this->assertNotEquals($oldFileStoragePath, $entity->getFileStoragePath());
        $this->assertNotEquals($oldFileMimeType, $entity->getFileMimeType());
        $this->assertNotEquals($oldFileSize, $entity->getFileSize());
    }

    public function testUnsupportedEntityWrite()
    {
        $this->setExpectedException('AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException');

        $entity = new EntityWithoutUploadedFileTrait();

        $this->storageHandler->writeUploadedFile($entity);
    }

    public function testUnsupportedEntityDelete()
    {
        $this->setExpectedException('AshleyDawson\DoctrineGaufretteStorableBundle\Exception\EntityNotSupportedException');

        $entity = new EntityWithoutUploadedFileTrait();

        $this->storageHandler->deleteUploadedFile($entity);
    }

    public function testWriteEvents()
    {
        $this->eventDispatcher->addListener(StorageEvents::PRE_WRITE, function (WriteUploadedFileEvent $event) {

            $this->assertEquals('sample-image-one.gif', $event->getFileName());
            $this->assertEquals('sample-image-one.gif', $event->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getFileMimeType());
            $this->assertGreaterThan(0, $event->getFileSize());
            $this->assertEquals('gif', $event->getFileExtension());
            $this->assertInstanceOf('AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity', $event->getEntity());

            $event->setFileName('foo-bar.gif');
            $event->setFileStoragePath('fib-baz.gif');
        });

        $this->eventDispatcher->addListener(StorageEvents::POST_WRITE, function (WriteUploadedFileEvent $event) {

            $this->assertEquals('foo-bar.gif', $event->getFileName());
            $this->assertEquals('fib-baz.gif', $event->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getFileMimeType());
            $this->assertGreaterThan(0, $event->getFileSize());
            $this->assertEquals('gif', $event->getFileExtension());
            $this->assertInstanceOf('AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity', $event->getEntity());
        });

        $entity = (new UploadedFileEntity())
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $this->storageHandler->writeUploadedFile($entity);

        $this->assertEquals('foo-bar.gif', $entity->getFileName());

        $this->assertFileExists(TESTS_TEMP_DIR . '/fib-baz.gif');
    }

    public function testDeleteEvents()
    {
        $this->eventDispatcher->addListener(StorageEvents::PRE_DELETE, function (DeleteUploadedFileEvent $event) {

            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileName());
            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getEntityClone()->getFileMimeType());
            $this->assertGreaterThan(0, $event->getEntityClone()->getFileSize());
            $this->assertInstanceOf('AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity', $event->getEntityClone());
        });

        $this->eventDispatcher->addListener(StorageEvents::POST_DELETE, function (DeleteUploadedFileEvent $event) {

            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileName());
            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getEntityClone()->getFileMimeType());
            $this->assertGreaterThan(0, $event->getEntityClone()->getFileSize());
            $this->assertInstanceOf('AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity', $event->getEntityClone());
        });

        $entity = (new UploadedFileEntity())
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $this->storageHandler->writeUploadedFile($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');
        $this->assertEquals('sample-image-one.gif', $entity->getFileName());
        $this->assertEquals('sample-image-one.gif', $entity->getFileStoragePath());

        $this->storageHandler->deleteUploadedFile($entity);

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-image-one.gif');
    }

    public function testDeleteEventsChangeProperties()
    {
        $this->eventDispatcher->addListener(StorageEvents::PRE_DELETE, function (DeleteUploadedFileEvent $event) {

            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileName());
            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getEntityClone()->getFileMimeType());
            $this->assertGreaterThan(0, $event->getEntityClone()->getFileSize());
            $this->assertInstanceOf('AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity', $event->getEntityClone());

            $event->getEntityClone()->setFileName('FOOB.jpeg');
        });

        $this->eventDispatcher->addListener(StorageEvents::POST_DELETE, function (DeleteUploadedFileEvent $event) {

            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileName());
            $this->assertEquals('sample-image-one.gif', $event->getEntityClone()->getFileStoragePath());
            $this->assertEquals('image/gif', $event->getEntityClone()->getFileMimeType());
            $this->assertGreaterThan(0, $event->getEntityClone()->getFileSize());
            $this->assertInstanceOf('AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity', $event->getEntityClone());
        });

        $entity = (new UploadedFileEntity())
            ->setUploadedFile($this->getTestUploadedFileOne())
        ;

        $this->storageHandler->writeUploadedFile($entity);

        $this->assertFileExists(TESTS_TEMP_DIR . '/sample-image-one.gif');
        $this->assertEquals('sample-image-one.gif', $entity->getFileName());
        $this->assertEquals('sample-image-one.gif', $entity->getFileStoragePath());

        $this->storageHandler->deleteUploadedFile($entity);

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/sample-image-one.gif');
    }

    public function tearDown()
    {
        @unlink(TESTS_TEMP_DIR . '/sample-image-one.gif');
        @unlink(TESTS_TEMP_DIR . '/sample-image-two.jpg');
        @unlink(TESTS_TEMP_DIR . '/fib-baz.gif');
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