<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Validator;

use Fykosak\NetteORM\ValidatorException;
use Nette\Utils\Type;

class CheckClass implements ValidatorCheck
{
    public function validate(
        Type $dbType,
        Type $docType,
        array $metadata,
        string $table,
        string $column,
        array &$errors
    ): void {
        if (!$dbType->isBuiltin() && !$docType->isBuiltin()) {
            if (
                $docType->getSingleName() !== $dbType->getSingleName() &&
                /** @phpstan-ignore argument.type,argument.type */
                !new \ReflectionClass($docType->getSingleName())->isSubclassOf($dbType->getSingleName())
            ) {
                $errors[] = new ValidatorException(
                    $table,
                    $column,
                    sprintf(
                        'Database type `%s` does not accept PHPDoc type `%s`',
                        $dbType,
                        $docType,
                    )
                );
            }
        }
    }
}
