<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Throwable;

/**
 * @template TKey of string
 * @template TModel of Model
 */
class Mapper
{
    /**
     * @phpstan-var array<TKey, array{
     *      model: class-string<TModel>,
     *      service: class-string<Service<TModel>>
     *  }>
     */
    private array $map = [];

    /**
     * @throws Throwable
     * @phpstan-param TKey $table
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
     * @phpstan-param TKey $table
     * @phpstan-return array{
     *       model: class-string<TModel>,
     *       service: class-string<Service<TModel>>
     *   }|null
     */
    public function getDefinition(string $table): ?array
    {
        return $this->map[$table] ?? null;
    }
}
