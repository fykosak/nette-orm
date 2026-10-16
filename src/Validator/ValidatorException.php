<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Validator;

use Nette\InvalidStateException;

class ValidatorException extends InvalidStateException
{
    /**
     * @param ValidatorError[] $errors
     */
    public function __construct(
        public readonly array $errors
    ) {
        parent::__construct(\sprintf('Found %d errors.', count($errors)));
    }
}
