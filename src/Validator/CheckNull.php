<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Validator;

use Fykosak\NetteORM\ValidatorException;
use Nette\Utils\Type;

class CheckNull implements ValidatorCheck
{
    public function validate(
        Type $dbType,
        Type $docType,
        array $metadata,
        string $table,
        string $column,
        array &$errors
    ): void {
        if ($dbType->allows('null') xor $docType->allows('null')) {
            $errors[] = new ValidatorException(
                $table,
                $column,
                sprintf(
                    'Database %s NULL but PHPDoc %s NULL',
                    $dbType->allows('null') ? 'allows' : 'doesnt allow',
                    $docType->allows('null') ? 'allows' : 'doesnt allow',
                )
            );
        }
    }
}
