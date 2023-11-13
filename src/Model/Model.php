<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Model;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\ModelRelationsParser;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\MemberAccessException;

abstract class Model extends ActiveRow
{
    /**
     * @phpstan-param array<string,mixed> $data
     * @param TypedGroupedSelection|TypedSelection|Selection $table
     */
    final public function __construct(array $data, Selection $table)
    {
        if (!$table instanceof TypedGroupedSelection && !$table instanceof TypedSelection) {
            throw new \TypeError(
                '$selection must be a instance of TypedSelection or TypedGroupedSelection'
            );
        }
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
     * @return Model|mixed
     * @throws MemberAccessException|\ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        $selfReflection = new \ReflectionClass(static::class);
        $docs = ModelRelationsParser::parseModelDoc($selfReflection);
        if (!is_null($value) && isset($docs[$key])) {
            $item = $docs[$key];
            if ($item['type']->isClass()) {
                $returnType =  $item['reflection'];
                if ($value instanceof ActiveRow) {
                    if ($returnType->isSubclassOf(self::class)) {
                        $value = $returnType->newInstance($value->toArray(), $value->getTable());
                    }
                }/* elseif ($returnType->isSubclassOf(\BackedEnum::class)) {
                    $value = $returnType->getMethod('tryFrom')->invoke($returnType, $value);
                }*/
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

    /**
     * @return static
     */
    public static function createFromActiveRow(ActiveRow $row): self
    {
        if ($row instanceof static) {
            return $row;
        }
        return new static($row->toArray(), $row->getTable());
    }
}
