<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Utils\Reflection;
use Nette\Utils\Type;

class ModelRelationsParser
{
    /**
     * @param \ReflectionClass $modelReflection
     * @return Type[][]|string[][]|null
     * @phpstan-return array<string,array{'type':\Nette\Utils\Type,'reflection':?\ReflectionClass,'property':string}>
     * @throws \ReflectionException
     */
    public static function parseModelDoc(\ReflectionClass $modelReflection): ?array
    {
        $doc = $modelReflection->getDocComment();
        if ($doc === false) {
            return null;
        }
        $properties = [];
        foreach (explode("\n", $doc) as $line) {
            if (
                preg_match(
                    '/\*\s+@property-read\s+([A-Za-z0-9_>|]+)\s+\$([A-Za-z0-9_]+)/',
                    $line,
                    $matches
                )
            ) {
                [, $returnString, $property] = $matches;
                $returnType = Type::fromString($returnString);
                $properties[$property] = [
                    'type' => $returnType,
                    'reflection' => $returnType->isClass()
                        ? new \ReflectionClass(
                            Reflection::expandClassName($returnType->getSingleName(), $modelReflection)
                        )
                        : null,
                    'property' => $property,
                ];
            }
        }
        return $properties;
    }

    /**
     * @throws \ReflectionException
     */
    public static function resolveReferencedMethods(\ReflectionClass $model): array
    {
        $items = [];
        foreach ($model->getMethods() as $method) {
            $name = $method->getName();
            /** @var \ReflectionNamedType|null $returnType */
            $returnType = $method->getReturnType();
            if (
                !$returnType
                || count($method->getParameters())
                || in_array($returnType->getName(), ['self', 'static', 'parent'])
            ) {
                continue;
            }
            $type = Type::fromString($returnType->getName());
            if (!$type->isClass()) {
                continue;
            }
            $itemReflection = new \ReflectionClass($type->getSingleName());
            if (isset($items[$itemReflection->name])) {
                continue;
            }
            if ($itemReflection->isSubclassOf(Model::class)) {
                $items[$itemReflection->name] = [
                    'type' => 'method',
                    'accessor' => $name,
                    'reflection' => $itemReflection,
                    'nullable' => $returnType->allowsNull(),
                ];
            }
        }
        return $items;
    }

    /**
     * @throws \ReflectionException
     */
    public static function resolveReferencedProperties(\ReflectionClass $model): array
    {
        $properties = ModelRelationsParser::parseModelDoc($model);
        $items = [];
        if ($properties) {
            foreach ($properties as $item) {
                $property = $item['property'];
                $type = $item['type'];
                if (!$type->isClass()) {
                    continue;
                }
                $itemReflection = new \ReflectionClass(
                    Reflection::expandClassName($type->getSingleName(), $model)
                );
                if ($itemReflection->isSubclassOf(Model::class)) {
                    $items[$itemReflection->name] = [
                        'type' => 'property',
                        'accessor' => $property,
                        'reflection' => $itemReflection,
                        'nullable' => $type->allows('null'),
                    ];
                }
            }
        }
        return $items;
    }

    /**
     * @return \ReflectionClass[][]|null|string[][]
     * @throws \ReflectionException
     */
    public static function getPath(
        \ReflectionClass $model,
        \ReflectionClass $requestedModel,
        array $classPath
    ): ?array {
        $items = array_merge(
            self::resolveReferencedProperties($model),
            self::resolveReferencedMethods($model)
        );

        if (isset($items[$requestedModel->getName()])) {
            return [$items[$requestedModel->getName()]];
        }
        $classPath[] = $model->getName();
        foreach ($items as $key => $item) {
            if (in_array($key, $classPath)) {
                continue;
            }
            $path = self::getPath($item['reflection'], $requestedModel, $classPath);
            if ($path) {
                return [$item, ...$path];
            }
        }
        return null;
    }
}
