<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

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
        foreach ($this->config as $tableName => $fieldDefinitions) {
            $this->registerORMService($tableName, $fieldDefinitions, $mapper);
        }
    }

    final protected function registerORMService(
        string $tableName,
        array $fieldDefinitions,
        ServiceDefinition $mapper
    ): void {
        $serviceClassName = $fieldDefinitions['serviceClassName']
            ?? ($fieldDefinitions['service']
                ?? DummyService::class);
        $modelClassName = $fieldDefinitions['modelClassName']
            ?? ($fieldDefinitions['model']
                ?? DummyModel::class);

        $mapper->addSetup('addDefinition', [$tableName, $modelClassName, $serviceClassName]);

        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.service'));
        if (isset($fieldDefinitions['context'])) {
            $factory->setFactory($serviceClassName, [$tableName, $fieldDefinitions['context']]);
        } else {
            $factory->setFactory($serviceClassName, [$tableName]);
        }
    }
}
