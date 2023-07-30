<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

/**
 * @template M of Model
 * @phpstan-implements \Iterator<M>
 */
trait TypedSelectionsTrait
{
    protected Mapper $mapper;

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
     */
    protected function createRow(array $row): Model
    {
        $className = $this->mapper->getDefinition($this->name)['model'];
        return new $className($row, $this);
    }
}
