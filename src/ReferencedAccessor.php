<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;

final class ReferencedAccessor
{

    /**
     * @throws CannotAccessModelException
     */
    public static function accessModel(Model $model, string $modelClassName): Model
    {
        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }
        $modelReflection = new \ReflectionClass($model);
        $candidates = 0;
        $newModel = null;
        foreach ($modelReflection->getMethods() as $method) {
            $name = $method->getName();
            if ((string)$method->getReturnType() === $modelClassName) {
                $candidates++;
                $newModel = $model->{$name}();
            }
        }
        if ($candidates !== 1) {
            throw new CannotAccessModelException($modelClassName, $model);
        }
        return $newModel;
    }
}
