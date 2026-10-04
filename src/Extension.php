<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Fykosak\NetteORM\Model\DummyModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\DummyService;
use Fykosak\NetteORM\Service\Service;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\NotImplementedException;

class Extension extends CompilerExtension
{
    /**
     * @throws NotImplementedException
     */
    public function loadConfiguration(): void
    {
        $mapper = $this->getContainerBuilder()->addDefinition($this->prefix('mapper'));
        $mapper->setFactory(Mapper::class);
        /** @phpstan-ignore foreach.nonIterable */
        foreach ($this->config as $tableName => $fieldDefinitions) {
            /** @phpstan-ignore argument.type */
            $this->registerORMService($tableName, $fieldDefinitions, $mapper);
        }
    }

    /**
     * @template TModel of Model
     * @phpstan-param array{
     *     service?:class-string<Service<TModel>>,
     *     model?:class-string<TModel>,
     *     context?:mixed
     * } $fieldDefinition
     */
    final protected function registerORMService(
        string $tableName,
        array $fieldDefinition,
        ServiceDefinition $mapper
    ): void {
        $serviceClassName = $fieldDefinition['service'] ?? DummyService::class;
        $modelClassName = $fieldDefinition['model'] ?? DummyModel::class;

        $mapper->addSetup('addDefinition', [$tableName, $modelClassName, $serviceClassName]);

        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.service'));
        if (isset($fieldDefinition['context'])) {
            $factory->setFactory($serviceClassName, [$tableName, $fieldDefinition['context']]);
        } else {
            $factory->setFactory($serviceClassName, [$tableName]);
        }
    }
}
