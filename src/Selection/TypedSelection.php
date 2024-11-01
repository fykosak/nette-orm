<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Selection;

use Fykosak\NetteORM\Mapper;
use Fykosak\NetteORM\Model\Model;
use Nette\Caching\IStorage;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @template TModel of Model
 */
class TypedSelection extends Selection
{
    /** @phpstan-use TypedSelectionsTrait<TModel> */
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
