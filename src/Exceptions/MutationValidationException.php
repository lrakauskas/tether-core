<?php

namespace Tether\Core\Exceptions;

use RuntimeException;

/**
 * Thrown when a mutation cannot be applied because the target model fails
 * validation. Carries the full validation message bag so the server can
 * surface per-field errors back to the client.
 */
class MutationValidationException extends RuntimeException
{
    /**
     * @param  array<string, list<string>>  $messages  Validator::errors()->toArray() shape.
     */
    public function __construct(
        public readonly array $messages,
        string $mutationId = '',
    ) {
        parent::__construct("Mutation [{$mutationId}] failed validation.");
    }
}
