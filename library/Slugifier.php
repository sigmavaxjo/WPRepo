<?php

namespace WPRepo;

use FileUpload\FileNameGenerator\Slug;

/**
 * Slug generator, without overwrite protection.
 */
class Slugifier extends Slug
{
    protected function getUniqueFilename($name, $type, $index, $content_range)
    {
        return $name;
    }
}
