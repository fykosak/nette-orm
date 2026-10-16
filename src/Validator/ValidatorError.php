<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Validator;

readonly class ValidatorError
{
    public function __construct(
        public string $table,
        public string $column,
        public string $message,
    ) {
    }

    public function __toString(): string
    {
        return \sprintf('Field `%s.%s`: %s', $this->table, $this->column, $this->message);
    }
}
