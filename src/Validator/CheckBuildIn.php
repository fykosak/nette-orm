<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Validator;

use Fykosak\NetteORM\ValidatorException;
use Nette\Utils\Type;

class CheckBuildIn implements ValidatorCheck
{
    public function validate(
        Type $dbType,
        Type $docType,
        array $metadata,
        string $table,
        string $column,
        array &$errors
    ): void {
        if ($dbType->isBuiltin() || $docType->isBuiltin()) {
            if ($dbType->getSingleName() !== $docType->getSingleName()) {
                $errors[] = new ValidatorException(
                    $table,
                    $column,
                    sprintf(
                        'Database type `%s` does not match PHPDoc type `%s`',
                        $dbType,
                        $docType,
                    )
                );
            }
        }
    }
}
