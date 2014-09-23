<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EventListener;

use AshleyDawson\DoctrineGaufretteStorableBundle\EventListener\UploadedFileSubscriber;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\EntityManagerProvider;
use AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Fixtures\UploadedFileEntity;
use Doctrine\Common\EventManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        $em->addEventSubscriber(
            new UploadedFileSubscriber()
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