<?php

namespace WPRepo;

/**
 * Drives the application.
 */
class Application
{
    public function run(): void
    {
        header('Content-Type: text/plain; charset=UTF-8');

        try {
            (new Authorizer())->run();
            (new Receiver())->run();
            (new Generator())->run();
        } catch (WprError $e) {
            http_response_code($e->getCode());
            echo $e->getMessage(), PHP_EOL;
        }
    }
}
