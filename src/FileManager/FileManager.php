<?php

namespace FileManager;

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
            throw new \RuntimeException("File not found!");
        }

        $file = $_FILES[$keyName];

        $this->validate($file);

        if (!is_dir($path)) {
            mkdir($path, 0, true);
        }

        $filename = !is_null($filename) ? $filename : sha1_file($file['tmp_name']);
        $fullFileName = sprintf('%s/%s.%s',
            $path,
            $filename,
            $this->extension
        );

        $this->moveUploadedFile($file['tmp_name'], $fullFileName);

        return true;
    }

    private function validate($file)
    {
        $this->checkErrors($file);
        $this->checkFilesizeLimit($file, $this->limit);
        $this->checkMimeType($file, $this->mimeTypes);

        return true;
    }

    private function checkErrors($file)
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new \RuntimeException('Invalid parameters.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Exceeded filesize limit.');
            default:
                throw new \RuntimeException('Unknown errors.');
        }

        return true;
    }

    private function checkFilesizeLimit($file, $limit = 2097152)
    {
        if ($file['size'] > $limit) {
            throw new \RuntimeException('Exceeded filesize limit.');
        }

        return true;
    }

    private function checkMimeType($file, array $mimeTypes = array())
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if (false === $this->extension = array_search(
            $finfo->file($file['tmp_name']),
            $mimeTypes,
            true
        )) {
            throw new \RuntimeException('Invalid file format.');
        }

        return true;
    }

    protected function moveUploadedFile(string $filename, string $destination)
    {
        if (!move_uploaded_file(
            $filename,
            $destination
        )) {
            throw new \RuntimeException('Failed to move uploaded file.');
        }

        return true;
    }
}
