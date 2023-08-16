<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Exceptions;

use Fykosak\NetteORM\Model;
use RuntimeException;
use Throwable;

class CannotAccessModelException extends RuntimeException
{
    /**
     * @phpstan-param class-string<Model> $modelClassName
     * @phpstan-param Model $model
     */
    public function __construct(string $modelClassName, Model $model, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                _('Can not access model %s from %s'),
                $modelClassName,
                get_class($model)
            ),
            $code,
            $previous
        );
    }
}
