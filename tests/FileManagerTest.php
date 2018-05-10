<?php

use FileManager\FileManager;
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class FileManagerTest extends TestCase
{
    /**
     * @var FileManager
     */
    protected $_object;

    protected function setUp()
    {
        $_FILES = array(
            'test'         => array(
                'name'     => 'test.png',
                'type'     => 'image/png',
                'size'     => 542,
                'tmp_name' => __DIR__.'/_files/source-test.png',
                'error'    => 0,
            ),
            'test_error'   => array(
                'name'     => 'test.png',
                'type'     => 'image/png',
                'size'     => 542000000,
                'tmp_name' => __DIR__.'/_files/source-test.png',
                'error'    => UPLOAD_ERR_FORM_SIZE,
            ),
            'test_error_2' => array(
                'name'     => 'test.png',
                'type'     => 'image/png',
                'size'     => 542,
                'tmp_name' => __DIR__.'/_files/source-test.png',
                'error'    => UPLOAD_ERR_NO_FILE,
            ),
            'test_error_3' => array(
                'name'     => 'test.png',
                'type'     => 'image/png',
                'size'     => 542000000,
                'tmp_name' => __DIR__.'/_files/source-test.png',
                'error'    => 0,
            ),
            'test_error_4' => array(
                'name'     => 'test.txt',
                'type'     => 'text/plain',
                'size'     => 10000,
                'tmp_name' => __DIR__.'/_files/test.txt',
                'error'    => 0,
            ),
        );

        $this->_object = new FileManagerMock();
    }

    protected function tearDown()
    {
        unset($_FILES);
        unset($this->_object);
        @unlink(__DIR__.'/_files/upload/test.jpg');
    }

    /**
     * @covers FileManager::save
     */
    public function testSaveSucessfully()
    {
        $this->assertNotEmpty($this->_object->save('test', __DIR__.'/_files/upload', 'test'));
    }

    public function testFileNotFound()
    {
        $this->expectException(\RuntimeException::class);

        $this->assertTrue($this->_object->save('new', __DIR__.'/_files/upload', 'test'));
    }

    public function testFileExceededFilesize()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tamanho do arquivo excedido.');

        $this->assertTrue($this->_object->save('test_error', __DIR__.'/_files/upload', 'test_error'));
    }

    public function testFileHasErrorNoFile()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Nenhum arquivo enviado.');

        $this->assertTrue($this->_object->save('test_error_2', __DIR__.'/_files/upload', 'test_error'));
    }

    public function testFileExceededFilesizeDefault()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tamanho do arquivo excedido.');

        $this->assertTrue($this->_object->save('test_error_3', __DIR__.'/_files/upload', 'test_error'));
    }

    public function testFileHasWrongMimeType()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Formato do arquivo é inválido.');

        $this->assertTrue($this->_object->save('test_error_4', __DIR__.'/_files/upload', 'test_error'));
    }

    public function testFailToMoveUploadedFile()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to move uploaded file.');

        $this->_object = new FileManager();

        $this->assertTrue($this->_object->save('test', __DIR__.'/_files/upload', 'test'));
    }
}

class FileManagerMock extends FileManager
{
    public function moveUploadedFile($filename, $destination)
    {
        return copy($filename, $destination);
    }
}
