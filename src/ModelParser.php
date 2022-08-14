<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Utils\Reflection;
use Nette\Utils\Type;

class ModelParser
{
    /**
     * @param \ReflectionClass $modelReflection
     * @return Type[][]|string[][]|null
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
                    '/\*\s+@property-read\s+([A-Za-z0-9_>|]+)\s+\$?([A-Za-z0-9_]+)/',
                    $line,
                    $matches
                )
            ) {
                [, $returnString, $property] = $matches;
                $returnType = Type::fromString($returnString);
                $properties[$property] = [
                    'type' => $returnType,
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
        $properties = ModelParser::parseModelDoc($model);
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
}
