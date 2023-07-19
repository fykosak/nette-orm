<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Tests\ORM\EventModel;

/**
 * @phpstan-template M of Model
 * @phpstan-method M|null fetch()
 * @phpstan-method M|null get($key)
 * @phpstan-method M insert(iterable $data)
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
