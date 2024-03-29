<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Caching\IStorage;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @template M of Model
 */
class TypedSelection extends Selection
{
    /** @phpstan-use TypedSelectionsTrait<M> */
    use TypedSelectionsTrait;

    public function __construct(
        Mapper $mapper,
        Explorer $explorer,
        Conventions $conventions,
        string $tableName,
        ?IStorage $cacheStorage = null
    ) {
        parent::__construct($explorer, $conventions, $tableName, $cacheStorage);
        $this->mapper = $mapper;
    }
}
