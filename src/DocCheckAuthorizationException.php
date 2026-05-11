<?php

namespace RedSnapper\SocialiteProviders\DocCheck;

use RuntimeException;

class DocCheckAuthorizationException extends RuntimeException
{
    public function __construct(
        public readonly string $error,
        public readonly ?string $errorDescription = null,
    ) {
        parent::__construct(
            $errorDescription !== null
                ? "DocCheck authorization failed: {$error} — {$errorDescription}"
                : "DocCheck authorization failed: {$error}"
        );
    }
}
