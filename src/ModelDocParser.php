<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\Utils\Type;

class ModelDocParser
{
    /**
     * @param \ReflectionClass $modelReflection
     * @return Type[][]|string[][]|null
     */
    public static function parseModelDoc(\ReflectionClass $modelReflection): ?array
    {
        $doc = $modelReflection->getDocComment();
        if ($doc === false) {
            return null;
        }
        $properties = [];
        foreach (explode("\n", $doc) as $line) {
            if (
                preg_match(
                    '/\*\s+@property-read\s+([A-Za-z0-9_>|]+)\s+\$?([A-Za-z0-9_]+)/',
                    $line,
                    $matches
                )
            ) {
                [, $returnString, $property] = $matches;
                $returnType = Type::fromString($returnString);
                $properties[$property] = [
                    'type' => $returnType,
                    'property' => $property,
                ];
            }
        }
        return $properties;
    }
}
