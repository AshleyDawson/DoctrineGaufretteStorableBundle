<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\Tests\Model;

use AshleyDawson\DoctrineGaufretteStorableBundle\Storage\EntityStorageHandlerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mock;

    const MOCK_FILESYSTEM_MAP_ID = 'mock_filesystem_map';

    protected function setUp()
    {
        $this->mock = $this->getMockForTrait(EntityStorageHandlerInterface::UPLOADED_FILE_TRAIT_NAME);
        $this
            ->mock
            ->expects($this->any())
            ->method('getFilesystemMapId')
            ->will($this->returnValue(self::MOCK_FILESYSTEM_MAP_ID))
        ;
    }

    public function testAbstractGetFilesystemMapIdMethod()
    {
        $this->assertEquals(self::MOCK_FILESYSTEM_MAP_ID, $this->mock->getFilesystemMapId());
    }

    public function testFileMimeTypeAccessorMutator()
    {
        $value = 'text/plain';

        $this->mock->setFileMimeType($value);

        $this->assertEquals($value, $this->mock->getFileMimeType());
    }

    public function testFileNameAccessorMutator()
    {
        $value = 'my-file.jpeg';

        $this->mock->setFileName($value);

        $this->assertEquals($value, $this->mock->getFileName());
    }

    public function testFileSizeAccessorMutator()
    {
        $value = 5600;

        $this->mock->setFileSize($value);

        $this->assertEquals($value, $this->mock->getFileSize());

        $this->assertTrue(is_int($this->mock->getFileSize()));
    }

    public function testUploadedFileAccessorMutator()
    {
        $path = __DIR__ . '/../Resources/fixtures/file/sample-image-one.gif';
        $originalName = 'sample-image-one.gif';
        $mimeType = 'image/gif';
        $size = filesize($path);

        $uploadedFileMock = new UploadedFile($path, $originalName, $mimeType, $size);

        $this->mock->setUploadedFile($uploadedFileMock);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $this->mock->getUploadedFile());

        $this->assertEquals($path, $this->mock->getUploadedFile()->getPathName());
        $this->assertEquals($originalName, $this->mock->getUploadedFile()->getFilename());
        $this->assertEquals($mimeType, $this->mock->getUploadedFile()->getMimeType());
        $this->assertEquals($size, $this->mock->getUploadedFile()->getSize());
    }
}