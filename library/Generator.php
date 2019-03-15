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
        $this->generateRepo();
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
     * Generates the repository.
     */
    protected function generateRepo(): void
    {
        $args = [
            'command' => 'build',
            'file' => WPR_CONFIG,
        ];

        $input = new ArrayInput($args);
        $output = new ConsoleOutput();
        $satis = new Satis();

        $satis->setAutoExit(false);

        $status = $satis->run($input, $output);

        if ($status !== 0) {
            throw new WprError('Failed to run Satis build.', 500);
        }
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
}
