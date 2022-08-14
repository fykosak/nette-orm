<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\MemberAccessException;
use Nette\Utils\Reflection;

/**
 * @method TypedGroupedSelection related(string $key, ?string $throughColumn = null)
 */
abstract class Model extends ActiveRow
{

    public function __construct(array $data, Selection $table)
    {
        if (!$table instanceof TypedGroupedSelection && !$table instanceof TypedSelection) {
            throw new \InvalidArgumentException(
                'Selection must be a instance of TypedSelection or TypedGroupedSelection'
            );
        }
        parent::__construct($data, $table);
    }

    /**
     * @return ActiveRow|mixed
     * @throws MemberAccessException|\ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        $selfReflection = new \ReflectionClass(static::class);
        $docs = ModelParser::parseModelDoc($selfReflection);
        if (!is_null($value) && isset($docs[$key])) {
            $item = $docs[$key];
            if ($value instanceof ActiveRow && $item['type']->isClass()) {
                $returnType = new \ReflectionClass(
                    Reflection::expandClassName($item['type']->getSingleName(), $selfReflection)
                );
                if ($returnType->isSubclassOf(self::class)) {
                    $value = $returnType->newInstance($value->toArray(), $value->getTable());
                }
            }
        }
        return $value;
    }

    /**
     * @return static
     */
    public static function createFromActiveRow(ActiveRow $row): self
    {
        if ($row instanceof static) {
            return $row;
        }
        return new static($row->toArray(), $row->getTable());
    }
}
