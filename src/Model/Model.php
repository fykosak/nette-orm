<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Model;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\ModelRelationsParser;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\NetteORM\Types\WGS84Point;
use Nette\Database\Table\ActiveRow;
use Nette\MemberAccessException;
use ReflectionException;

abstract class Model extends ActiveRow
{
    /**
     * @phpstan-param array<string,mixed> $data
     * @phpstan-param TypedGroupedSelection<static>|TypedSelection<static> $selection
     */
    final public function __construct(array $data, TypedGroupedSelection|TypedSelection $selection)
    {
        /** @phpstan-ignore argument.type */
        parent::__construct($data, $selection);
    }

    /**
     * @template TModel of Model
     * @phpstan-return TypedGroupedSelection<TModel>
     * @phpstan-ignore method.childReturnType,method.templateTypeNotInParameter
     */
    public function related(string $key, ?string $throughColumn = null): TypedGroupedSelection
    {
        $selection = parent::related($key, $throughColumn);
        if ($selection instanceof TypedGroupedSelection) {
            return $selection;
        }
        throw new \TypeError(
            '$selection must be a instance of TypedGroupedSelection'
        );
    }

    /**
     * @throws MemberAccessException
     * @throws ReflectionException
     */
    public function &__get(string $key): mixed //phpcs:ignore
    {
        $value = parent::__get($key);
        $selfReflection = new \ReflectionClass(static::class);
        $docs = ModelRelationsParser::parseModelDoc($selfReflection);
        if (!is_null($value) && isset($docs[$key])) {
            $item = $docs[$key];
            if ($item['type']->isClass()) {
                $returnType = $item['reflection'];
                if (isset($returnType)) {
                    if ($value instanceof ActiveRow) {
                        if ($returnType->isSubclassOf(self::class)) {
                            $value = $returnType->newInstance($value->toArray(), $value->getTable());
                        }
                    } elseif ($returnType->isSubclassOf(\BackedEnum::class)) {
                        $value = $returnType->getMethod('tryFrom')->invoke($returnType, $value);
                    } elseif ($returnType->name === WGS84Point::class) {
                        $value = $returnType->getMethod('fromBytes')->invoke($returnType, $value);
                    }
                }
            }
        }
        return $value;
    }

    /**
     * @template TModel of Model
     * @phpstan-param class-string<TModel> $requestedModel
     * @phpstan-return TModel|null
     * @throws CannotAccessModelException|ReflectionException
     */
    public function getReferencedModel(string $requestedModel): ?self
    {
        if ($this instanceof $requestedModel) {
            /** @var TModel $this */
            return $this;
        }

        $path = ModelRelationsParser::getPath(
            new \ReflectionClass($this),
            new \ReflectionClass($requestedModel),
            []
        );
        $newModel = $this;
        if ($path !== null) {
            foreach ($path as $item) {
                $nextModel = $item['type'] === 'property'
                    /** @phpstan-ignore property.dynamicName  */
                    ? $newModel->{$item['accessor']}
                    /** @phpstan-ignore method.dynamicName  */
                    : $newModel->{$item['accessor']}();
                if (is_null($nextModel)) {
                    if ($item['nullable']) {
                        return null;
                    }
                    throw new CannotAccessModelException($requestedModel, $this);
                }
                $newModel = $nextModel;
            }
            /** @var TModel|null $newModel */
            return $newModel;
        }
        throw new CannotAccessModelException($requestedModel, $this);
    }
}
