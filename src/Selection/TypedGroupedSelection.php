<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Selection;

use Fykosak\NetteORM\Mapper;
use Fykosak\NetteORM\Model\Model;
use Nette\Caching\Storage;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\GroupedSelection;
use Nette\Database\Table\Selection;

/**
 * @template TModel of Model
 * @extends GroupedSelection<TModel>
 */
class TypedGroupedSelection extends GroupedSelection
{
    /** @phpstan-use TypedSelectionsTrait<TModel> */
    use TypedSelectionsTrait;

    /**
     * @phpstan-param Mapper<string,TModel> $mapper
     * @phpstan-param Selection<TModel> $refTable
     */
    public function __construct(
        Mapper $mapper,
        Explorer $explorer,
        Conventions $conventions,
        string $tableName,
        string $column,
        Selection $refTable,
        ?Storage $cacheStorage = null
    ) {
        /** @phpstan-ignore argument.type */
        parent::__construct($explorer, $conventions, $tableName, $column, $refTable, $cacheStorage);
        $this->mapper = $mapper;
    }
}
