<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\SmartObject;

class Mapper
{
    use SmartObject;

    /**
     * @phpstan-var array<string,array{'model':class-string<Model>,'service':class-string<Service<Model>>}>
     */
    private array $map = [];

    /**
     * @throws \Exception
     */
    public function addDefinition(string $table, string $model, string $service): void
    {
        if (isset($this->map[$table])) {
            throw new \Exception();
        }
        $this->map[$table] = ['model' => $model, 'service' => $service];
    }

    /**
     * @phpstan-return array{'model':class-string<Model>,'service':class-string<Service<Model>>}
     */
    public function getDefinition(string $table): ?array
    {
        return $this->map[$table] ?? null;
    }
}
