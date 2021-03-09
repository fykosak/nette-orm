<?php

namespace Fykosak\NetteORM;

use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class TypedTableSelection extends Selection {

    protected string $modelClassName;

    public function __construct(string $modelClassName, string $table, Explorer $explorer, Conventions $conventions) {
        parent::__construct($explorer, $conventions, $table);
        $this->modelClassName = $modelClassName;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return AbstractModel
     */
    protected function createRow(array $row): AbstractModel {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }
}
