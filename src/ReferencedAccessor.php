<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Utils\Reflection;

final class ReferencedAccessor
{

    /**
     * @throws CannotAccessModelException
     */
    public static function accessModel(Model $model, string $modelClassName): ?Model
    {
        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }
        $modelReflection = new \ReflectionClass($model);

        $properties = ModelDocParser::parseModelDoc($modelReflection);
        if ($properties) {
            foreach ($properties as $item) {
                $property = $item['property'];
                $type = $item['type'];
                if ($type->isClass()) {
                    if (Reflection::expandClassName($type->getSingleName(), $modelReflection) === $modelClassName) {
                        $newModel = $model->{$property};
                        if ($newModel) {
                            return $newModel;
                        }
                        if ($type->allows('null')) {
                            return null;
                        }
                        throw new CannotAccessModelException($modelClassName, $model);
                    }
                }
            }
        }
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
