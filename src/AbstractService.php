<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\ModelException;
use InvalidArgumentException;
use Nette\Database\Explorer;
use Nette\SmartObject;
use PDOException;

abstract class AbstractService
{
    use SmartObject;

    private array $columns;

    final public function __construct(
        private readonly string $tableName,
        private readonly string $modelClassName,
        public Explorer $explorer,
    ) {
    }

    public function findByPrimary(string|int|null $key): ?AbstractModel
    {
        if (!isset($key)) {
            return null;
        }
        /** @var AbstractModel|null $result */
        $result = $this->getTable()->get($key);
        return $result;
    }

    /**
     * @throws ModelException
     */
    public function createNewModel(array $data): AbstractModel
    {
        $modelClassName = $this->getModelClassName();
        $data = $this->filterData($data);
        try {
            $result = $this->getTable()->insert($data);
            return ($modelClassName)::createFromActiveRow($result);
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
    }

    /**
     * @throws ModelException
     */
    public function updateModel(AbstractModel $model, array $data): bool
    {
        try {
            $this->checkType($model);
            $data = $this->filterData($data);
            return $model->update($data);
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
    }

    /**
     * @throws ModelException
     * @deprecated
     */
    public function dispose(AbstractModel $model): void
    {
        $this->disposeModel($model);
    }

    /**
     * @throws ModelException
     */
    public function disposeModel(AbstractModel $model): void
    {
        $this->checkType($model);
        try {
            $model->delete();
        } catch (PDOException $exception) {
            $code = $exception->getCode();
            throw new ModelException("$code: Error when deleting a model.");
        }
    }

    public function getTable(): TypedTableSelection
    {
        return new TypedTableSelection(
            $this->getModelClassName(),
            $this->tableName,
            $this->explorer,
            $this->explorer->getConventions(),
        );
    }

    public function storeModel(array $data, ?AbstractModel $model = null): AbstractModel
    {
        if (isset($model)) {
            $this->updateModel($model, $data);
            return $model;
        }
        return $this->createNewModel($data);
    }

    /** @return string|AbstractModel */
    final public function getModelClassName(): string
    {
        return $this->modelClassName;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function checkType(AbstractModel $model): void
    {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException(
                sprintf('Service for class %s cannot store %s', $this->getModelClassName(), get_class($model))
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
