<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class TypedSelection extends Selection
{

    protected string $modelClassName;

    public function __construct(string $modelClassName, string $table, Explorer $explorer, Conventions $conventions)
    {
        parent::__construct($explorer, $conventions, $table);
        $this->modelClassName = $modelClassName;
    }

    protected function createRow(array $row): Model
    {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }
}
