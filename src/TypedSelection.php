<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class TypedSelection extends Selection
{

    protected string $modelClassName;
    private Mapper $mapper;

    public function __construct(
        string $modelClassName,
        string $table,
        Explorer $explorer,
        Conventions $conventions,
        Mapper $mapper
    ) {
        parent::__construct($explorer, $conventions, $table);
        $this->modelClassName = $modelClassName;
        $this->mapper = $mapper;
    }

    protected function createRow(array $row): Model
    {
        $className = $this->mapper->getDefinition($this->name)['model'];
        return new $className($row, $this, $this->mapper);
    }
}
