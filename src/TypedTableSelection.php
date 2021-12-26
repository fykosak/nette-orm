<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @phpstan-template T
 */
class TypedTableSelection extends Selection
{
    public function __construct(
        protected readonly string $modelClassName,
        string $table,
        Explorer $explorer,
        Conventions $conventions,
    ) {
        parent::__construct($explorer, $conventions, $table);
    }

    /**
     * @phpstan-return T
     */
    protected function createRow(array $row): AbstractModel
    {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }
}
