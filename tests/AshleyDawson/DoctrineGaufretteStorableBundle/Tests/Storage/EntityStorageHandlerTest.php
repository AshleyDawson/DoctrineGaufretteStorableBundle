<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Storage;

use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandler;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\EntityWithoutUploadedFileTrait;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Model\UploadedFileTraitTest;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    protected function setUp()
    {
        $adapter = new LocalAdapter(TESTS_TEMP_DIR);
        $filesystem = new Filesystem($adapter);

        $filesystemMap = new FilesystemMap([
            UploadedFileTraitTest::MOCK_FILESYSTEM_MAP_ID => $filesystem,
        ]);

        $this->storageHandler = new EntityStorageHandler(
            $filesystemMap,
            $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
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

    public function tearDown()
    {
        @unlink(TESTS_TEMP_DIR . '/sample-image-one.gif');
        @unlink(TESTS_TEMP_DIR . '/sample-image-two.jpg');
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