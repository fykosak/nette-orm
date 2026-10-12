<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Validator;

use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\ModelRelationsParser;
use Fykosak\NetteORM\Service\Service;
use Fykosak\NetteORM\Types\WGS84Point;
use Fykosak\NetteORM\ValidatorException;
use Nette\Utils\Type;

/**
 * @phpstan-import-type ColumnMeta from Service
 * @phpstan-import-type ColumnDoc from ModelRelationsParser
 */
class Validator
{
    /**
     * @template M of Model
     * @param Service<M> $service
     * @return ValidatorException[]
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __invoke(Service $service): array
    {
        $doc = ModelRelationsParser::parseModelDoc(new \ReflectionClass($service->getModelClassName()));
        $metaData = $service->getColumnMetadata();
        $exceptions = [];
        foreach ($metaData as $metaDatum) {
            try {
                $this->validateColumn($metaDatum, $doc[$metaDatum['name']] ?? null);
            } catch (ValidatorException $exception) {
                $exceptions[] = $exception;
            }
        }
        return $exceptions;
    }

    /**
     * @phpstan-param ColumnMeta $metadata
     * @phpstan-param ColumnDoc|null $doc
     * @throws \Exception
     */
    private function validateColumn(array $metadata, array|null $doc): void
    {
        $name = $metadata['name'];
        if (!isset($doc)) {
            throw new ValidatorException(
                $metadata['table'],
                $metadata['name'],
                \sprintf(
                    'Missing columns `%s`',
                    $name,
                )
            );
        }
        $dbType = $this->matchType($metadata);
        $errors = [];
        new CheckNull()->validate($dbType, $doc['type'], $metadata, $metadata['table'], $metadata['name'], $errors);
        new CheckBuildIn()->validate($dbType, $doc['type'], $metadata, $metadata['table'], $metadata['name'], $errors);
        new CheckClass()->validate($dbType, $doc['type'], $metadata, $metadata['table'], $metadata['name'], $errors);
        new CheckEnum()->validate($dbType, $doc['type'], $metadata, $metadata['table'], $metadata['name'], $errors);
    }

    /**
     * @phpstan-param ColumnMeta $metadata
     */
    private function matchType(array $metadata): Type
    {
        if ($metadata['nativetype'] === 'BOOL') {
            return $metadata['nullable'] ? Type::fromString('?bool') : Type::fromString('bool');
        }

        if (
            in_array(
                $metadata['nativetype'],
                ['DECIMAL', 'NUMERIC', 'FLOAT', 'DOUBLE', 'REAL', 'DOUBLE PRECISION'],
                true
            )
        ) {
            return $metadata['nullable'] ? Type::fromString('?float') : Type::fromString('float');
        }

        if (in_array($metadata['nativetype'], ['VARCHAR', 'CHAR', 'TEXT', 'LONGTEXT'], true)) {
            return $metadata['nullable'] ? Type::fromString('?string') : Type::fromString('string');
        }
        if (in_array($metadata['nativetype'], ['TIMESTAMP', 'DATETIME', 'DATE'], true)) {
            return $metadata['nullable'] ?
                Type::fromString('?' . \DateTimeInterface::class) :
                Type::fromString(\DateTimeInterface::class);
        }
        if ($metadata['nativetype'] === 'ENUM') {
            return $metadata['nullable'] ?
                Type::fromString('?' . \BackedEnum::class) :
                Type::fromString(\BackedEnum::class);
        }
        if ($metadata['nativetype'] === 'TINYINT' && $metadata['size'] === 1) {
            return $metadata['nullable'] ? Type::fromString('?bool') : Type::fromString('bool');
        }
        if ($metadata['nativetype'] === 'POINT') {
            return $metadata['nullable'] ?
                Type::fromString('?' . WGS84Point::class) :
                Type::fromString(WGS84Point::class);
        }
        if (
            in_array(
                $metadata['nativetype'],
                [
                    'TINYINT',
                    'TINYINT UNSIGNED',
                    'SMALLINT',
                    'SMALLINT UNSIGNED',
                    'MEDIUMINT',
                    'MEDIUMINT UNSIGNED',
                    'INT',
                    'INT UNSIGNED',
                    'BIGINT',
                    'BIGINT UNSIGNED'
                ],
                true
            )
        ) {
            return $metadata['nullable'] ? Type::fromString('?int') : Type::fromString('int');
        }

        throw new ValidatorException(
            $metadata['table'],
            $metadata['name'],
            sprintf(
                'Validator does not support type `%s`.',
                $metadata['nativetype'],
            )
        );
    }
}
