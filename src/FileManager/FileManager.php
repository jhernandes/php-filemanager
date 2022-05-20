<?php

namespace FileManager;

use Slim\Http\UploadedFile;

class FileManager
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @var array
     */
    private $mimeTypes;

    /**
     * @var string
     */
    private $extension;

    /**
     * Constructor
     * @param int $filesizeLimit
     * @param array $mimeTypes
     */
    public function __construct(
        $filesizeLimit = 2097152,
        array $mimeTypes = array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
        )
    ) {
        $this->limit = $filesizeLimit;
        $this->mimeTypes = $mimeTypes;
    }

    /**
     * Save the file
     * @param file $file
     * @param string $path
     * @param string $filename
     *
     * @return bool
     */
    public function save($keyName, $path, $filename = null)
    {
        if (!isset($_FILES[$keyName])) {
            throw new \RuntimeException("Arquivo não enviado");
        }

        $file = $_FILES[$keyName];
        $uploadedFile = $this->parseUploadedFile($file);

        $this->validate($uploadedFile);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $filename = !is_null($filename) ? $filename : sha1_file($uploadedFile->file);
        $fullFileName = sprintf('%s/%s.%s',
            $path,
            $filename,
            $this->extension
        );

        $this->moveUploadedFile($uploadedFile->file, $fullFileName);

        return $fullFileName;
    }

    public function validate($file)
    {
        $uploadedFile = $this->parseUploadedFile($file);

        $this->checkErrors($uploadedFile);
        $this->checkFilesizeLimit($uploadedFile, $this->limit);
        $this->checkMimeType($uploadedFile, $this->mimeTypes);

        return true;
    }

    private function checkErrors(UploadedFile $file)
    {
        if (is_array($file->getError())) {
            throw new \RuntimeException('Parâmetros inválidos!');
        }

        switch ($file->getError()) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('Nenhum arquivo enviado.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Tamanho do arquivo excedido.');
            default:
                throw new \RuntimeException('Erro não identificado.');
        }

        return true;
    }

    private function checkFilesizeLimit(UploadedFile $file, $limit = 2097152)
    {
        if ($file->getSize() > $limit) {
            throw new \RuntimeException('Tamanho do arquivo excedido.');
        }

        return true;
    }

    private function checkMimeType(UploadedFile $file, array $mimeTypes = array())
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if (false === $this->extension = array_search(
            $finfo->file($file->file),
            $mimeTypes,
            true
        )) {
            throw new \RuntimeException('Formato do arquivo é inválido.');
        }

        return true;
    }

    protected function moveUploadedFile($filename, $destination)
    {
        if (!move_uploaded_file(
            $filename,
            $destination
        )) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return true;
    }

    /**
     * @param file|UploadedFile $file
     * @return UploadedFile
     */
    protected function parseUploadedFile($file)
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }

        return new UploadedFile(
            $file['tmp_name'],
            isset($file['name']) ? $file['name'] : null,
            isset($file['type']) ? $file['type'] : null,
            isset($file['size']) ? $file['size'] : null,
            $file['error'],
            true
        );
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     *
     * @return self
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }
}
