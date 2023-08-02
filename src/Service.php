<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Database\Explorer;
use Nette\SmartObject;

/**
 * @template M of Model
 */
abstract class Service
{
    use SmartObject;

    private string $tableName;
    public Explorer $explorer;
    private Mapper $mapper;
    /** @phpstan-var array<string,mixed> */
    private array $columns;

    final public function __construct(string $tableName, Explorer $explorer, Mapper $mapper)
    {
        $this->tableName = $tableName;
        $this->explorer = $explorer;
        $this->mapper = $mapper;
    }

    /**
     * @phpstan-return M|null
     */
    public function findByPrimary(int|string|null $key): ?Model
    {
        return isset($key) ? $this->getTable()->get($key) : null;
    }

    /**
     * @throws ModelException
     * @phpstan-param M $model
     */
    public function disposeModel(Model $model): void
    {
        try {
            $this->checkType($model);
            $model->delete();
        } catch (\PDOException $exception) {
            throw new ModelException(
                'Error when deleting a model.',
                (int)$exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @phpstan-return TypedSelection<M>
     */
    final public function getTable(): TypedSelection
    {
        /** @phpstan-var TypedSelection<M> $selection */
        $selection = new TypedSelection(
            $this->mapper,
            $this->explorer,
            $this->explorer->getConventions(),
            $this->tableName
        );
        return $selection;
    }

    /**
     * @phpstan-param M|null $model
     * @phpstan-param array<string,mixed> $data
     * @phpstan-return M
     */
    public function storeModel(array $data, ?Model $model = null): Model
    {
        try {
            $dataSet = $this->filterData($data);
            if (isset($model)) {
                $this->checkType($model);
                $model->update($dataSet);
                return $model;
            }
            return $this->getTable()->insert($dataSet);
        } catch (\PDOException $exception) {
            throw new ModelException('Error when storing model.', (int)$exception->getCode(), $exception);
        }
    }

    /** @phpstan-return class-string<M> */
    final public function getModelClassName(): string
    {
        return $this->mapper->getDefinition($this->tableName)['model'];
    }

    /**
     * @throws \InvalidArgumentException
     * @phpstan-param M $model
     */
    protected function checkType(Model $model): void
    {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new ModelException('Service for class ' . $modelClassName . ' cannot store ' . get_class($model));
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
               /* if ($data[$name] instanceof \BackedEnum) {
                    $result[$name] = $data[$name]->value;
                } else {*/
                    $result[$name] = $data[$name];
                // }
            }
        }
        return $result;
    }

    /**
     * @phpstan-return array<string,mixed>
     */
    protected function getColumnMetadata(): array
    {
        if (!isset($this->columns)) {
            $this->columns = $this->explorer->getConnection()->getDriver()->getColumns($this->tableName);
        }
        return $this->columns;
    }
}
