<?php

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use ReflectionClass;

final class ReferencedAccessor
{

    /**
     * @param AbstractModel $model
     * @param string $modelClassName
     * @return AbstractModel
     * @throws CannotAccessModelException
     */
    public static function accessModel(AbstractModel $model, string $modelClassName): AbstractModel
    {
        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }
        $modelReflection = new ReflectionClass($model);
        $candidates = 0;
        $newModel = null;
        foreach ($modelReflection->getMethods() as $method) {
            $name = (string)$method->getName();
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
