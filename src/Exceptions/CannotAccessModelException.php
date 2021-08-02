<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Exceptions;

use RuntimeException;
use Throwable;

class CannotAccessModelException extends RuntimeException
{

    public function __construct(string $modelClassName, object $model, int $code = 0, ?Throwable $previous = null)
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
