<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

/**
 * @template M of Model
 */
trait TypedSelectionsTrait
{
    protected Mapper $mapper;

    /**
     * @phpstan-return TypedGroupedSelection<Model>
     */
    protected function createGroupedSelectionInstance(string $table, string $column): TypedGroupedSelection
    {
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
     */
    public function createSelectionInstance(?string $table = null): TypedSelection
    {
        return new TypedSelection(
            $this->mapper,
            $this->explorer,
            $this->conventions,
            $table ?: $this->name
        );
    }

    /**
     * @phpstan-return M
     * @phpstan-param array<string,mixed> $row
     */
    protected function createRow(array $row): Model
    {
        $className = $this->mapper->getDefinition($this->name)['model'];
        return new $className($row, $this);
    }
}
