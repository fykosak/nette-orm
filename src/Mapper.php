<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Nette\SmartObject;

/**
 * @phpstan-type MapperItem array{
 *     model:class-string<Model>,
 *     service:class-string<Service<Model>>
 *     }
 */
class Mapper
{
    use SmartObject;

    /**
     * @phpstan-var array<string,MapperItem>
     */
    private array $map = [];

    /**
     * @template TModel of Model
     * @throws \Exception
     * @phpstan-param class-string<TModel> $model
     * @phpstan-param class-string<Service<TModel>> $service
     */
    public function addDefinition(string $table, string $model, string $service): void
    {
        if (isset($this->map[$table])) {
            throw new \Exception();
        }
        $this->map[$table] = ['model' => $model, 'service' => $service];
    }

    /**
     * @phpstan-return MapperItem
     */
    public function getDefinition(string $table): ?array
    {
        return $this->map[$table] ?? null;
    }
}
