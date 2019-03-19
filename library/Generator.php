<?php

namespace WPRepo;

use Composer\Satis\Console\Application as Satis;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Generates a Composer repository using Satis.
 */
class Generator
{
    /**
     * Runs the generator.
     */
    public function run(): void
    {
        $this->createConfig();
        $this->purgeSource();
        $this->generateRepo();
        $this->purgeTarget();
        $this->deleteConfig();
    }

    /**
     * Writes a configuration file for Satis.
     */
    protected function createConfig(): void
    {
        $config = [
            'name' => 'sigmavaxjo/wprepo',
            'homepage' => WPR_HOST,
            'output-dir' => WPR_TARGET,
            'require-all' => true,
            'repositories' => [
                [
                    'type' => 'artifact',
                    'url' => WPR_SOURCE,
                ],
            ],
            'archive' => [
                'directory' => 'artifacts',
            ],
        ];

        $encoded = json_encode($config);
        $file = fopen(WPR_CONFIG, 'w');
        $result = fwrite($file, $encoded);

        fclose($file);

        if (!$result) {
            throw new WprError('Failed to write Satis config.', 500);
        }
    }

    /**
     * Removes expired source files.
     */
    protected function purgeSource(): void
    {
        $expire = time() - WPR_EXPIRE;
        $directory = opendir(WPR_SOURCE);

        if (!$directory) {
            throw new WprError('Failed to open source directory.', 500);
        }

        while (false !== ($entry = readdir($directory))) {
            $path = realpath(WPR_SOURCE . "/$entry");

            if (is_file($path) && filemtime($path) < $expire) {
                unlink($path);
            }
        }
    }

    /**
     * Generates the repository.
     */
    protected function generateRepo(): void
    {
        $args = [
            'command' => 'build',
            'file' => WPR_CONFIG,
        ];

        $status = $this->callSatis($args);

        if ($status !== 0) {
            throw new WprError('Failed to run Satis build.', 500);
        }
    }

    /**
     * Removes expired target files.
     */
    protected function purgeTarget(): void
    {
        $args = [
            'command' => 'purge',
            'file' => WPR_CONFIG,
            'output-dir' => WPR_TARGET,
        ];

        /*
         * Ignore the status code for now.
         *
         * Satis sometimes crashes when removing directories since there
         * may still be unexpired files in them. This resolves itself in
         * subsequent calls. The issue must be fixed in Satis before we
         * can start verifying the status code.
         */
        $this->callSatis($args);
    }

    /**
     * Removes the configuration file for Satis.
     */
    protected function deleteConfig(): void
    {
        $status = unlink(WPR_CONFIG);

        if (!$status) {
            throw new WprError('Failed to remove Satis config.', 500);
        }
    }

    /**
     * Calls the Satis application.
     */
    protected function callSatis($args): int
    {
        $input = new ArrayInput($args);
        $output = new ConsoleOutput();
        $satis = new Satis();

        $satis->setAutoExit(false);

        return $satis->run($input, $output);
    }
}
