<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Database\Explorer;
use Nette\SmartObject;

abstract class Service
{
    use SmartObject;

    private string $tableName;
    public Explorer $explorer;
    private Mapper $mapper;
    private array $columns;

    final public function __construct(string $tableName, Explorer $explorer, Mapper $mapper)
    {
        $this->tableName = $tableName;
        $this->explorer = $explorer;
        $this->mapper = $mapper;
    }

    /**
     * @param mixed $key
     * @return Model|null
     */
    public function findByPrimary($key): ?Model
    {
        return isset($key) ? $this->getTable()->get($key) : null;
    }

    /**
     * @throws ModelException
     */
    public function disposeModel(Model $model): void
    {
        try {
            $this->checkType($model);
            $model->delete();
        } catch (\PDOException $exception) {
            throw new ModelException(
                'Error when deleting a model.',
                $exception->getCode(),
                $exception
            );
        }
    }

    final public function getTable(): TypedSelection
    {
        return new TypedSelection(
            $this->mapper,
            $this->explorer,
            $this->explorer->getConventions(),
            $this->tableName
        );
    }

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
            throw new ModelException('Error when storing model.', $exception->getCode(), $exception);
        }
    }

    /** @return string|Model */
    final public function getModelClassName(): string
    {
        return $this->mapper->getDefinition($this->tableName)['model'];
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function checkType(Model $model): void
    {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new ModelException('Service for class ' . $modelClassName . ' cannot store ' . get_class($model));
        }
    }

    /*
     * Omits array elements whose keys aren't columns in the table.
     */
    protected function filterData(array $data): array
    {
        $result = [];
        foreach ($this->getColumnMetadata() as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                $result[$name] = $data[$name];
            }
        }
        return $result;
    }

    protected function getColumnMetadata(): array
    {
        if (!isset($this->columns)) {
            $this->columns = $this->explorer->getConnection()->getDriver()->getColumns($this->tableName);
        }
        return $this->columns;
    }
}
