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
    public static function accessModel(Model $model, string $modelClassName): Model
    {
        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }
        $modelReflection = new \ReflectionClass($model);
        $candidates = 0;
        $newModel = null;
        $doc = $modelReflection->getDocComment();

        if ($doc !== false) {
            foreach (explode("\n", $doc) as $line) {
                if (preg_match('/\*\s+@property-read\s+([A-Z][A-Za-z0-9_]+)\s+\$([A-Za-z0-9_]+)/', $line, $matches)) {
                    [, $returnType, $property] = $matches;
                    $returnType = Reflection::expandClassName($returnType, $modelReflection);

                    if ($returnType === $modelClassName) {
                        $candidates++;
                        $newModel = $model->{$property};
                    }
                }
            }
        }

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
