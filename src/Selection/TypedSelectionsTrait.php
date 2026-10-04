<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Selection;

use Fykosak\NetteORM\Mapper;
use Fykosak\NetteORM\Model\Model;
use Nette\InvalidStateException;

/**
 * @template TModel of Model
 */
trait TypedSelectionsTrait
{
    /**
     * @var Mapper<string,TModel>
     */
    protected Mapper $mapper;

    /**
     * @phpstan-return TypedGroupedSelection<Model>
     * @phpstan-ignore method.childReturnType
     */
    protected function createGroupedSelectionInstance(string $table, string $column): TypedGroupedSelection
    {
        /** @phpstan-ignore return.type */
        return new TypedGroupedSelection(
            $this->mapper,
            $this->explorer,
            $this->conventions,
            $table,
            $column,
            $this
        );
    }

    /**
     * @phpstan-return TypedSelection<Model>
     * @phpstan-ignore method.childReturnType
     */
    public function createSelectionInstance(?string $table = null): TypedSelection
    {
        /** @phpstan-ignore return.type */
        return new TypedSelection(
            $this->mapper,
            $this->explorer,
            $this->conventions,
            $table ?? $this->name
        );
    }

    /**
     * @return TModel
     * @phpstan-param array<mixed> $row
     * @phpstan-ignore method.childReturnType
     */
    protected function createRow(array $row): Model
    {
        $def = $this->mapper->getDefinition($this->name);
        $className = $def ['model'] ?? throw new InvalidStateException();
        return new $className($row, $this);
    }

    public function unsetRefCache(): void
    {
        $this->refCache = [];
    }
}
