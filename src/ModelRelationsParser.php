<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Attributes\ReferencedFollow;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Reflection;
use Nette\Utils\Type;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

class ModelRelationsParser
{
    /**
     * @template TModel of Model
     * @phpstan-param ReflectionClass<TModel> $modelReflection
     * @phpstan-return array<string,array{
     *     type:Type,
     *     reflection:ReflectionClass<object>|null,
     *     property:string
     * }>|null
     * @throws ReflectionException
     */
    public static function parseModelDoc(ReflectionClass $modelReflection): ?array
    {
        $doc = $modelReflection->getDocComment();
        if ($doc === false) {
            return null;
        }
        $properties = [];
        foreach (explode("\n", $doc) as $line) {
            $m = preg_match(
                '/\*\s+@property-read\s+([A-Za-z0-9_>|]+)\s+\$([A-Za-z0-9_]+)/',
                $line,
                $matches
            );
            if ($m !== false && $m !== 0) {
                [, $returnString, $property] = $matches;
                $returnType = Type::fromString($returnString);
                $properties[$property] = [
                    'type' => $returnType,
                    'reflection' => $returnType->isClass()
                        ? new ReflectionClass(
                        /** @phpstan-ignore argument.type */
                            Reflection::expandClassName(
                            /** @phpstan-ignore argument.type */
                                $returnType->getSingleName(),
                                /** @phpstan-ignore argument.type */
                                $modelReflection
                            )
                        )
                        : null,
                    'property' => $property,
                ];
            }
        }
        return $properties;
    }

    /**
     * @throws ReflectionException
     * @template TModel of Model
     * @phpstan-param ReflectionClass<TModel> $model
     * @phpstan-return array<string,array{
     *     type:string,
     *     accessor:string,
     *     reflection:ReflectionClass<Model>,
     *     nullable:bool
     * }>
     */
    public static function resolveReferencedMethods(ReflectionClass $model): array
    {
        $items = [];
        foreach ($model->getMethods() as $method) {
            $follow = false;
            foreach ($method->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
                if ($instance instanceof ReferencedFollow) {
                    $follow = true;
                }
            }
            if (!$follow) {
                continue;
            }
            /** @var ReflectionNamedType|null $returnType */
            $returnType = $method->getReturnType();
            if (is_null($returnType)) {
                continue;
            }
            foreach ($method->getParameters() as $parameter) {
                if (!$parameter->isOptional()) {
                    continue 2;
                }
            }

            if (in_array($returnType->getName(), ['self', 'static', 'parent'], true)) {
                continue;
            }

            $type = Type::fromString($returnType->getName());
            if (!$type->isClass()) {
                continue;
            }
            /** @phpstan-ignore argument.type */
            $itemReflection = new ReflectionClass($type->getSingleName());
            if (isset($items[$itemReflection->name])) {
                continue;
            }
            if ($itemReflection->isSubclassOf(Model::class)) {
                $items[$itemReflection->name] = [
                    'type' => 'method',
                    'accessor' => $method->getName(),
                    'reflection' => $itemReflection,
                    'nullable' => $returnType->allowsNull(),
                ];
            }
        }
        return $items;
    }

    /**
     * @throws ReflectionException
     * @template TModel of Model
     * @phpstan-param ReflectionClass<TModel> $model
     * @phpstan-return array<string,array{
     *     type:string,
     *     accessor:string,
     *     reflection:ReflectionClass<Model>,
     *     nullable:bool,
     *     }>
     */
    public static function resolveReferencedProperties(ReflectionClass $model): array
    {
        $properties = ModelRelationsParser::parseModelDoc($model);
        $items = [];
        if (isset($properties)) {
            foreach ($properties as $item) {
                $property = $item['property'];
                $type = $item['type'];
                if (!$type->isClass()) {
                    continue;
                }
                $itemReflection = new ReflectionClass(
                /** @phpstan-ignore argument.type,argument.type,argument.type */
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
     * @throws ReflectionException
     * @template TModel of Model
     * @template TRequestedModel of Model
     * @phpstan-param ReflectionClass<TModel> $model
     * @phpstan-param ReflectionClass<TRequestedModel> $requestedModel
     * @phpstan-param array<int,mixed> $classPath
     * @phpstan-return array<int,array{
     *     type:string,
     *     accessor:string,
     *     reflection:ReflectionClass<Model>,
     *     nullable:bool,
     * }>
     */
    public static function getPath(
        ReflectionClass $model,
        ReflectionClass $requestedModel,
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
            if (in_array($key, $classPath, true)) {
                continue;
            }
            $path = self::getPath($item['reflection'], $requestedModel, $classPath);
            if (isset($path)) {
                return [$item, ...$path];
            }
        }
        return null;
    }
}
