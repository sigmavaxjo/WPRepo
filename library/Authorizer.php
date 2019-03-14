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
    public function run(): bool
    {
        return (
            $this->verifyMethod() &&
            $this->verifyConstants() &&
            $this->verifyAuthorization()
        );
    }

    /**
     * Verifies the request method.
     */
    protected function verifyMethod(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            trigger_error('Invalid request method.');
            http_response_code(400);
            return false;
        }

        return true;
    }

    /**
     * Verifies all constants are set.
     */
    protected function verifyConstants(): bool
    {
        foreach (self::CONSTANTS as $constant) {
            if (defined($constant)) {
                continue;
            }

            trigger_error("Missing constant: $constant");
            http_response_code(500);
            return false;
        }

        return true;
    }

    /**
     * Verifies the authorization header.
     */
    protected function verifyAuthorization(): bool
    {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        $parts = explode(' ', $authorization, 2);

        if (!$parts || sizeof($parts) < 2) {
            trigger_error('Missing HTTP Authorization.');
            http_response_code(401);
            return false;
        } elseif ($parts[0] !== 'Key' || $parts[1] !== WPR_KEY) {
            trigger_error('Invalid HTTP Authorization.');
            http_response_code(403);
            return false;
        }

        return true;
    }
}
