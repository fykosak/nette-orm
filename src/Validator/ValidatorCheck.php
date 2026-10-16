<?php

namespace Fykosak\NetteORM\Validator;

use Fykosak\NetteORM\Service\Service;
use Nette\Utils\Type;

/**
 * @phpstan-import-type ColumnMeta from Service
 */
interface ValidatorCheck
{
    /**
     * @phpstan-param ColumnMeta $metadata
     * @phpstan-param ValidatorError[] $errors
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
