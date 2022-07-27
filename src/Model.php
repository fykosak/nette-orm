<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Database\Table\ActiveRow;
use Nette\MemberAccessException;
use Nette\Utils\Reflection;

abstract class Model extends ActiveRow
{
    /**
     * @return ActiveRow|mixed
     * @throws MemberAccessException|\ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        $selfReflection = new \ReflectionClass(static::class);
        $docs = ModelDocParser::parseModelDoc($selfReflection);
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
