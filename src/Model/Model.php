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

abstract class Model extends ActiveRow
{
    /**
     * @phpstan-param array<string,mixed> $data
     * @phpstan-param TypedGroupedSelection<Model>|TypedSelection<Model> $table
     */
    final public function __construct(array $data, TypedGroupedSelection|TypedSelection $table)
    {
        parent::__construct($data, $table);
    }

    /**
     * @phpstan-return TypedGroupedSelection<Model>
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
     * @throws MemberAccessException|\ReflectionException
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
        return $value;
    }

    /**
     * @template TModel of Model
     * @phpstan-param class-string<TModel> $requestedModel
     * @phpstan-return TModel|null
     * @throws CannotAccessModelException|\ReflectionException
     */
    public function getReferencedModel(string $requestedModel): ?self
    {
        // model is already instance of desired model
        if ($this instanceof $requestedModel) {
            return $this;
        }

        $path = ModelRelationsParser::getPath(
            new \ReflectionClass($this),
            new \ReflectionClass($requestedModel),
            []
        );
        $newModel = $this;
        if ($path) {
            foreach ($path as $item) {
                $newModel = $item['type'] === 'property'
                    ? $newModel->{$item['accessor']}
                    : $newModel->{$item['accessor']}();
                if (!$newModel) {
                    if ($item['nullable']) {
                        return null;
                    }
                    throw new CannotAccessModelException($requestedModel, $this);
                }
            }
            return $newModel;
        }
        throw new CannotAccessModelException($requestedModel, $this);
    }

    public static function createFromActiveRow(ActiveRow $row): static
    {
        if ($row instanceof static) {
            return $row;
        }
        return new static($row->toArray(), $row->getTable());
    }
}
