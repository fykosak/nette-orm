<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;

final class ReferencedAccessor
{

    /**
     * @throws CannotAccessModelException|\ReflectionException
     */
    public static function accessModel(Model $model, string $modelClassName): ?Model
    {
        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }

        $path = self::resolveModelRecursive(new \ReflectionClass($model), new \ReflectionClass($modelClassName), []);
        $newModel = $model;
        if ($path) {
            foreach ($path as $item) {
                if ($item['type'] === 'property') {
                    $newModel = $newModel->{$item['accessor']};
                } else {
                    $newModel = $newModel->{$item['accessor']}();
                }
                if (!$newModel) {
                    if ($item['nullable']) {
                        return null;
                    }
                    throw new CannotAccessModelException($modelClassName, $model);
                }
            }
            return $newModel;
        }
        throw new CannotAccessModelException($modelClassName, $model);
    }

    /**
     * @return \ReflectionClass[][]|null|string[][]
     * @throws \ReflectionException
     */
    public static function resolveModelRecursive(
        \ReflectionClass $model,
        \ReflectionClass $requestedModel,
        array $classPath
    ): ?array {
        $items = array_merge(
            ModelParser::resolveReferencedProperties($model),
            ModelParser::resolveReferencedMethods($model)
        );

        if (isset($items[$requestedModel->getName()])) {
            return [$items[$requestedModel->getName()]];
        }
        $classPath[] = $model->getName();
        foreach ($items as $key => $item) {
            if (in_array($key, $classPath)) {
                continue;
            }
            $path = self::resolveModelRecursive($item['reflection'], $requestedModel, $classPath);
            if ($path) {
                return [$item, ...$path];
            }
        }
        return null;
    }
}
