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
    public function run(): bool
    {
        return (
            $this->createConfig() &&
            $this->generateRepo() &&
            $this->deleteConfig()
        );
    }

    /**
     * Writes a configuration file for Satis.
     */
    protected function createConfig(): bool
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
            trigger_error('Failed to write Satis config.');
            http_response_code(500);
            return false;
        }

        return true;
    }

    /**
     * Generates the repository.
     */
    protected function generateRepo(): bool
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
            trigger_error('Failed to run Satis build.');
            http_response_code(500);
            return false;
        }

        return true;
    }

    /**
     * Removes the configuration file for Satis.
     */
    protected function deleteConfig(): bool
    {
        $status = unlink(WPR_CONFIG);

        if (!$status) {
            trigger_error('Failed to remove Satis config.');
        }

        return true;
    }
}
