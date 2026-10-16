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
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class Extension extends CompilerExtension
{
    /**
     * @throws NotImplementedException
     */
    public function loadConfiguration(): void
    {
        $mapper = $this->getContainerBuilder()->addDefinition($this->prefix('mapper'));
        $mapper->setFactory(Mapper::class, [$this->config['validation']]);

        /** @phpstan-ignore foreach.nonIterable */
        foreach ($this->config['tables'] as $tableName => $fieldDefinitions) {
            /** @phpstan-ignore argument.type */
            $this->registerORMService($tableName, $fieldDefinitions, $mapper);
        }
    }

    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'validation' => Expect::bool(false),
            'tables' => Expect::arrayOf(
                Expect::structure([
                    'model' => Expect::string(),
                    'service' => Expect::string(),
                ])->castTo('array'),
                'string'
            )->castTo('array')
        ])->otherItems('mixed')->castTo('array');
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
