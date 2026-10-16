<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Service;

use Fykosak\NetteORM\Mapper;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\NetteORM\Types\WGS84Point;
use Fykosak\NetteORM\Validator\Validator;
use Fykosak\NetteORM\Validator\ValidatorException;
use Nette\Database\Explorer;
use Nette\InvalidStateException;

/**
 * @template TModel of Model
 * @phpstan-type ColumnMeta array{
 *      name: string,
 *      table: string,
 *      nativetype: string,
 *      size: ?int,
 *      nullable: bool,
 *      default: mixed,
 *      autoincrement: bool,
 *      primary: bool,
 *      comment: ?string,
 *      vendor: array<string, mixed>
 *  }
 */
abstract class Service
{
    /** @phpstan-var list<ColumnMeta> */
    private array $columns;

    /**
     * @param Mapper<string,TModel> $mapper
     * @throws \ReflectionException
     */
    final public function __construct(
        private readonly string $tableName,
        public readonly Explorer $explorer,
        private readonly Mapper $mapper
    ) {
        if ($this->mapper->validate) {
            $validator = new Validator();
            $errors = $validator($this);
            if (count($errors) > 0) {
                throw new ValidatorException($errors);
            }
        }
    }

    /**
     * @phpstan-return Model|null
     */
    public function findByPrimary(string|int|null $key): ?Model
    {
        return isset($key) ? $this->getTable()->get($key) : null;
    }

    /**
     * @phpstan-param Model $model
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
     * @phpstan-param Model|null $model
     * @phpstan-param array<string,mixed> $data
     * @phpstan-return Model
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
        /** @phpstan-ignore return.type */
        return $this->getTable()->insert($dataSet);
    }

    /** @phpstan-return class-string<TModel> */
    final public function getModelClassName(): string
    {
        $def = $this->mapper->getDefinition($this->tableName);
        return $def['model'] ?? throw new InvalidStateException();
    }

    /**
     * @throws \InvalidArgumentException
     * @phpstan-param Model $model
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
            /** @var string $name */
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                if ($data[$name] instanceof \BackedEnum) {
                    $result[$name] = $data[$name]->value;
                } elseif ($data[$name] instanceof WGS84Point) {
                    $result[$name] = $data[$name]->toString();
                } else {
                    $result[$name] = $data[$name];
                }
            }
        }
        return $result;
    }

    /**
     * @return list<ColumnMeta>
     */
    public function getColumnMetadata(): array
    {
        if (!isset($this->columns)) {
            $this->columns = $this->explorer->getConnection()->getDriver()->getColumns($this->tableName);
        }
        return $this->columns;
    }
}
