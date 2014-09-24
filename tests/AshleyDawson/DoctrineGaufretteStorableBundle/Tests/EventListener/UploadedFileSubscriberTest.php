<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EventListener;

use AshleyDawson\DoctrineGaufretteStorableBundle\EventListener\UploadedFileSubscriber;
use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandler;
use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandlerInterface;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EntityManagerProvider;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Model\UploadedFileTraitTest;
use Doctrine\Common\EventManager;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;

class UploadedFileSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity'
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

        $em->addEventSubscriber(
            new UploadedFileSubscriber(
                new EntityStorageHandler(
                    $filesystemMap,
                    $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
                )
            )
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

    public function testUpdateEntityWithoutUploadedFile()
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