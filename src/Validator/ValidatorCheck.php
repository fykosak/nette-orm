<?php

namespace Fykosak\NetteORM\Validator;

use Fykosak\NetteORM\Service\Service;
use Fykosak\NetteORM\ValidatorException;
use Nette\Utils\Type;

/**
 * @phpstan-import-type ColumnMeta from Service
 */
interface ValidatorCheck
{
    /**
     * @phpstan-param ColumnMeta $metadata
     * @phpstan-param ValidatorException[] $errors
     */
    public function validate(
        Type $dbType,
        Type $docType,
        array $metadata,
        string $table,
        string $column,
        array &$errors
    ): void;
}
