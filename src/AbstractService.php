<?php

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Exceptions\ModelException;
use InvalidArgumentException;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\SmartObject;
use PDOException;

/**
 * Service class to high-level manipulation with ORM objects.
 * Use singleton descendant implementations.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňak <miso@fykos.cz>
 */
abstract class AbstractService {

    use SmartObject;

    private string $modelClassName;
    private string $tableName;
    private Explorer $explorer;
    private Conventions $conventions;

    public function __construct(string $tableName, string $modelClassName, Explorer $explorer, Conventions $conventions) {
        $this->tableName = $tableName;
        $this->modelClassName = $modelClassName;
        $this->explorer = $explorer;
        $this->conventions = $conventions;
    }

    /**
     * @param array $data
     * @return AbstractModel
     * @throws ModelException
     */
    public function createNewModel(array $data): AbstractModel {
        $modelClassName = $this->getModelClassName();
        $data = $this->filterData($data);
        try {
            $result = $this->getTable()->insert($data);
            if ($result !== false) {
                return ($modelClassName)::createFromActiveRow($result);
            }
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
        $code = $this->explorer->getConnection()->getPdo()->errorCode();
        throw new ModelException("$code: Error when storing a model.");
    }

    /**
     * Syntactic sugar.
     *
     * @param mixed $key
     * @return AbstractModel|null
     */
    public function findByPrimary($key): ?AbstractModel {
        if (is_null($key)) {
            return null;
        }
        /** @var AbstractModel|null $result */
        $result = $this->getTable()->get($key);
        return $result;
    }

    /**
     * @param AbstractModel $model
     * @return AbstractModel|null
     */
    public function refresh(AbstractModel $model): AbstractModel {
        return $this->findByPrimary($model->getPrimary(true));
    }

    /**
     * @param AbstractModel $model
     * @param array $data
     * @return bool
     * @throws ModelException
     */
    public function updateModel2(AbstractModel $model, array $data): bool {
        try {
            $this->checkType($model);
            $data = $this->filterData($data);
            return $model->update($data);
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     *
     * @param AbstractModel $model
     * @throws ModelException
     */
    public function dispose(AbstractModel $model): void {
        $this->checkType($model);
        try {
            $model->delete();
        } catch (PDOException $exception) {
            $code = $exception->getCode();
            throw new ModelException("$code: Error when deleting a model.");
        }
    }

    public function getTable(): TypedTableSelection {
        return new TypedTableSelection($this->getModelClassName(), $this->tableName, $this->explorer, $this->conventions);
    }

    public function getExplorer(): Explorer {
        return $this->explorer;
    }

    public function getConventions(): Conventions {
        return $this->conventions;
    }

    /**
     * @param AbstractModel $model
     * @throws InvalidArgumentException
     */
    protected function checkType(AbstractModel $model): void {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
    }

    protected ?array $defaults = null;

    /**
     * Default data for the new model.
     * TODO is this really needed?
     * @return array
     */
    protected function getDefaultData(): array {
        if (!isset($this->defaults)) {
            $this->defaults = [];
            foreach ($this->getColumnMetadata() as $column) {
                if ($column['nativetype'] == 'TIMESTAMP' && isset($column['default'])
                    && !preg_match('/^[0-9]{4}/', $column['default'])) {
                    continue;
                }
                $this->defaults[$column['name']] = isset($column['default']) ? $column['default'] : null;
            }
        }
        return $this->defaults;
    }

    /**
     * Omits array elements whose keys aren't columns in the table.
     *
     * @param array $data
     * @return array
     */
    protected function filterData(array $data): array {
        $result = [];
        foreach ($this->getColumnMetadata() as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                $result[$name] = $data[$name];
            }
        }
        return $result;
    }

    private array $columns;

    private function getColumnMetadata(): array {
        if (!isset($this->columns)) {
            $this->columns = $this->explorer->getConnection()->getDriver()->getColumns($this->tableName);
        }
        return $this->columns;
    }

    /** @return string|AbstractModel */
    final public function getModelClassName(): string {
        return $this->modelClassName;
    }
}
