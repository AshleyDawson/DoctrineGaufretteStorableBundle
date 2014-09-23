<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EventListener;

use AshleyDawson\DoctrineGaufretteStorableBundle\EventListener\UploadedFileSubscriber;
use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandler;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EntityManagerProvider;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity;
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
            'test_filesystem_map_id' => $filesystem,
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

        $this->assertTrue(in_array('AshleyDawson\DoctrineGaufretteStorableBundle\Model\UploadedFile\UploadedFileTrait', $reflection->getTraitNames()));
    }

    public function testPersistEntity()
    {
        $em = $this->getEntityManager();

        $entity = (new UploadedFileEntity())
            ->setName('Entity Name')
            ->setUploadedFile($this->getTestUploadedFile())
        ;

        $em->persist($entity);
        $em->flush();
        $em->refresh($entity);

        $this->assertEquals('Entity Name', $entity->getName());
    }

    /**
     * @return UploadedFile
     */
    private function getTestUploadedFile()
    {
        $path = __DIR__ . '/../Resources/fixtures/file/sample-image.gif';

        return new UploadedFile($path, 'sample-image.gif', 'image/gif', filesize($path));
    }
}