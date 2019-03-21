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
        'WPR_EXPIRE',
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

        if (!$authorization) {
            throw new WprError('Missing authorization header.', 401);
        }

        $parts = explode(' ', $authorization, 2);

        if (count($parts) < 2 || $parts[0] !== 'Key') {
            throw new WprError('Invalid authorization header.', 401);
        } elseif ($parts[1] !== WPR_KEY) {
            throw new WprError('Invalid authorization key.', 403);
        }
    }
}
