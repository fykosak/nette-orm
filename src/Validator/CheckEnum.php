<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Validator;

use Nette\Utils\Type;

class CheckEnum implements ValidatorCheck
{
    public function validate(
        Type $dbType,
        Type $docType,
        array $metadata,
        string $table,
        string $column,
        array &$errors
    ): void {
        if ($metadata['nativetype'] === 'ENUM') {
            try {
                /** @phpstan-ignore argument.type */
                $docReflection = new \ReflectionEnum($docType->getSingleName());
            } catch (\ReflectionException $exception) {
                $errors[] = new ValidatorError(
                    $table,
                    $column,
                    $exception->getMessage()
                );
                return;
            }

            /** @var class-string<\BackedEnum> $enumClassName */
            $enumClassName = $docReflection->getName();
            $enumCases = $enumClassName::cases();
            /** @phpstan-ignore argument.type */
            foreach (explode(',', substr($metadata['vendor']['type'], 5, -1)) as $raw) {
                $trimmedCase = substr($raw, 1, -1);
                try {
                    $case = $enumClassName::from($trimmedCase);
                    $enumCases = array_filter($enumCases, fn($c) => $c === $case);
                } catch (\ValueError $e) {
                    $errors[] = new ValidatorError(
                        $metadata['table'],
                        $metadata['name'],
                        $e->getMessage(),
                    );
                }
            }
            foreach ($enumCases as $missing) {
                $errors[] = new ValidatorError(
                    $metadata['table'],
                    $metadata['name'],
                    sprintf(
                        'Case `%s` is missing in DB.',
                        $missing->value,
                    )
                );
            }
        }
    }
}
