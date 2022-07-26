<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\Database\Explorer;
use Nette\SmartObject;

abstract class Service
{
    use SmartObject;

    private string $modelClassName;
    private string $tableName;
    public Explorer $explorer;
    public Mapper $mapper;
    private array $columns;

    final public function __construct(string $tableName, string $modelClassName, Explorer $explorer, Mapper $mapper)
    {
        $this->tableName = $tableName;
        $this->modelClassName = $modelClassName;
        $this->explorer = $explorer;
        $this->mapper = $mapper;
    }

    /**
     * @param mixed $key
     * @return Model|null
     */
    public function findByPrimary($key): ?Model
    {
        if (is_null($key)) {
            return null;
        }
        /** @var Model|null $result */
        $result = $this->getTable()->get($key);
        return $result;
    }

    /**
     * @throws ModelException
     */
    public function createNewModel(array $data): Model
    {
        $modelClassName = $this->getModelClassName();
        $data = $this->filterData($data);
        try {
            $result = $this->getTable()->insert($data);
            return ($modelClassName)::createFromActiveRow($result, $this->mapper);
        } catch (\PDOException $exception) {
            throw new ModelException('Error when storing model.', 0, $exception);
        }
    }

    /**
     * @throws ModelException
     */
    public function updateModel(Model $model, array $data): bool
    {
        try {
            $this->checkType($model);
            $data = $this->filterData($data);
            return $model->update($data);
        } catch (\PDOException $exception) {
            throw new ModelException('Error when storing model.', 0, $exception);
        }
    }

    /**
     * @throws ModelException
     * @deprecated
     */
    public function dispose(Model $model): void
    {
        $this->disposeModel($model);
    }

    /**
     * @throws ModelException
     */
    public function disposeModel(Model $model): void
    {
        $this->checkType($model);
        try {
            $model->delete();
        } catch (\PDOException $exception) {
            $code = $exception->getCode();
            throw new ModelException("$code: Error when deleting a model.");
        }
    }

    final public function getTable(): TypedSelection
    {
        return new TypedSelection(
            $this->getModelClassName(),
            $this->tableName,
            $this->explorer,
            $this->explorer->getConventions(),
            $this->mapper,
        );
    }

    public function storeModel(array $data, ?Model $model = null): Model
    {
        if (isset($model)) {
            $this->updateModel($model, $data);
            return $model;
        }
        return $this->createNewModel($data);
    }

    /** @return string|Model */
    final public function getModelClassName(): string
    {
        return $this->modelClassName;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function checkType(Model $model): void
    {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new \InvalidArgumentException(
                'Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model)
            );
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
