<?php

namespace WPRepo;

use FileUpload\FileSystem;
use FileUpload\FileUpload;
use FileUpload\PathResolver;
use FileUpload\Validator;

/**
 * Receives uploaded files.
 */
class Receiver
{
    const MAX_SIZE = '64M';
    const MIME_TYPES = [
        'application/tar',
        'application/zip',
    ];

    protected $uploader;

    /**
     * Configures the uploader.
     */
    public function __construct()
    {
        $srv = new FileUpload($_FILES['files'], $_SERVER);

        $srv->setFileNameGenerator(new Slugifier());
        $srv->setFileSystem(new FileSystem\Simple());
        $srv->setPathResolver(new PathResolver\Simple(WPR_SOURCE));
        $srv->addValidator(new Validator\SizeValidator(self::MAX_SIZE));
        $srv->addValidator(new Validator\MimeTypeValidator(self::MIME_TYPES));

        $this->uploader = $srv;
    }

    /**
     * Processes all files.
     */
    public function run(): bool
    {
        $changed = false;

        [$files, $headers] = $this->uploader->processAll();

        foreach ($files as $file) {
            $changed |= $file->completed;
            $name = $file->getBasename();
            $error = $file->error;

            if ($error) {
                trigger_error("File $name: $error");
            }
        }

        if (!$changed) {
            trigger_error('Received no files.');
            http_response_code(400);
            return false;
        }

        return true;
    }
}
