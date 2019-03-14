<?php

namespace WPRepo;

/**
 * Handles authorization.
 */
class Authorizer
{
    const CONSTANTS = [
        'WPR_KEY',
        'WPR_HOST',
        'WPR_CONFIG',
        'WPR_SOURCE',
        'WPR_TARGET',
    ];

    /**
     * Runs all checks.
     */
    public function run(): void
    {
        $this->verifyMethod();
        $this->verifyConstants();
        $this->verifyAuthorization();
    }

    /**
     * Verifies the request method.
     */
    protected function verifyMethod(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new WprError('Invalid request method.', 400);
        }
    }

    /**
     * Verifies all constants are set.
     */
    protected function verifyConstants(): void
    {
        foreach (self::CONSTANTS as $constant) {
            if (!defined($constant)) {
                throw new WprError("Missing constant: $constant", 500);
            }
        }
    }

    /**
     * Verifies the authorization header.
     */
    protected function verifyAuthorization(): void
    {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $parts = explode(' ', $authorization, 2);

        if (!$parts || sizeof($parts) < 2) {
            throw new WprError('Missing HTTP Authorization.', 401);
        } elseif ($parts[0] !== 'Key' || $parts[1] !== WPR_KEY) {
            throw new WprError('Invalid HTTP Authorization.', 403);
        }
    }
}
