<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Exceptions;

use Fykosak\NetteORM\Model\Model;
use RuntimeException;

class CannotAccessModelException extends RuntimeException
{
    /**
     * @phpstan-param class-string<Model> $modelClassName
     * @phpstan-param Model $model
     */
    public function __construct(
        public readonly string $modelClassName,
        public readonly Model $model,
    ) {
        parent::__construct(
            sprintf(
                'Can not access model %s from %s',
                $modelClassName,
                get_class($model)
            )
        );
    }
}
