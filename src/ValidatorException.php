<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\InvalidStateException;

class ValidatorException extends InvalidStateException
{
    public function __construct(string $table, string $column, string $message)
    {
        parent::__construct(\sprintf('Field `%s.%s`: %s', $table, $column, $message));
    }
}
