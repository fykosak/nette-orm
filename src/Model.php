<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\MemberAccessException;

abstract class Model extends ActiveRow
{

    public Mapper $mapper;

    public function __construct(array $data, Selection $table, Mapper $mapper)
    {
        parent::__construct($data, $table);
        $this->mapper = $mapper;
    }

    /**
     * @return ActiveRow|mixed
     * @throws MemberAccessException
     */
    public function &__get($key)
    {
        $value = parent::__get($key);
        if ($value instanceof ActiveRow) {
            $definition = $this->mapper->getDefinition($key);
            if ($definition) {
                $className = $definition['model'];
                $value = new $className($value->toArray(), $value->getTable(), $this->mapper);
            }
        }
        return $value;
    }

    /**
     * @return static
     */
    public static function createFromActiveRow(ActiveRow $row, Mapper $mapper): self
    {
        if ($row instanceof static) {
            return $row;
        }
        return new static($row->toArray(), $row->getTable(), $mapper);
    }
}
