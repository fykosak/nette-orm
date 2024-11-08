<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Service;

use Fykosak\NetteORM\Mapper;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedSelection;
use Nette\Database\Explorer;

/**
 * @template TModel of Model
 */
abstract class Service
{
    /** @phpstan-var array<int,array<string,mixed>> */
    private array $columns;

    final public function __construct(
        private readonly string $tableName,
        public readonly Explorer $explorer,
        private readonly Mapper $mapper
    ) {
    }

    /**
     * @phpstan-return TModel|null
     */
    public function findByPrimary(string|int|null $key): ?Model
    {
        return isset($key) ? $this->getTable()->get($key) : null;
    }

    /**
     * @phpstan-param TModel $model
     * @throws \PDOException
     */
    public function disposeModel(Model $model): void
    {
        $this->checkType($model);
        $model->delete();
    }

    /**
     * @phpstan-return TypedSelection<TModel>
     */
    final public function getTable(): TypedSelection
    {
        /** @phpstan-var TypedSelection<TModel> $selection */
        $selection = new TypedSelection(
            $this->mapper,
            $this->explorer,
            $this->explorer->getConventions(),
            $this->tableName
        );
        return $selection;
    }

    /**
     * @phpstan-param TModel|null $model
     * @phpstan-param array<string,mixed> $data
     * @phpstan-return TModel
     * @throws \PDOException
     */
    public function storeModel(array $data, ?Model $model = null): Model
    {
        $dataSet = $this->filterData($data);
        if (isset($model)) {
            $this->checkType($model);
            $model->update($dataSet);
            return $model;
        }
        return $this->getTable()->insert($dataSet);
    }

    /** @phpstan-return class-string<TModel> */
    final public function getModelClassName(): string
    {
        return $this->mapper->getDefinition($this->tableName)['model'];
    }

    /**
     * @throws \InvalidArgumentException
     * @phpstan-param TModel $model
     */
    protected function checkType(Model $model): void
    {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new \TypeError('Service for class ' . $modelClassName . ' cannot store ' . get_class($model));
        }
    }

    /**
     * Omits array elements whose keys aren't columns in the table.
     * @phpstan-param array<string,mixed> $data
     * @phpstan-return array<string,mixed>
     */
    protected function filterData(array $data): array
    {
        $result = [];
        foreach ($this->getColumnMetadata() as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                if ($data[$name] instanceof \BackedEnum) {
                     $result[$name] = $data[$name]->value;
                } else {
                    $result[$name] = $data[$name];
                }
            }
        }
        return $result;
    }

    /**
     * @phpstan-return array<int,array<string,mixed>>
     */
    protected function getColumnMetadata(): array
    {
        if (!isset($this->columns)) {
            $this->columns = $this->explorer->getConnection()->getDriver()->getColumns($this->tableName);
        }
        return $this->columns;
    }
}
