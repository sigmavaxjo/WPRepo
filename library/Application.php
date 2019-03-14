<?php

namespace WPRepo;

/**
 * Drives the application.
 */
class Application
{
    public function run(): bool
    {
        return (
            (new Authorizer())->run() &&
            (new Receiver())->run() &&
            (new Generator())->run()
        );
    }
}
