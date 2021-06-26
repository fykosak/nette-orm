<?php

namespace Fykosak\NetteORM;

use Nette\Database\Table\ActiveRow;

abstract class AbstractModel extends ActiveRow {

    /**
     * @param ActiveRow $row
     * @return static
     */
    public static function createFromActiveRow(ActiveRow $row): self {
        if ($row instanceof static) {
            return $row;
        }
        return new static($row->toArray(), $row->getTable());
    }
}
