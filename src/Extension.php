<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;

class Extension extends CompilerExtension
{

    /**
     * @throws NotImplementedException
     */
    public function loadConfiguration(): void
    {
        foreach ($this->config as $tableName => $fieldDefinitions) {
            $this->registerORMService($tableName, $fieldDefinitions);
        }
    }

    final protected function registerORMService(string $tableName, array $fieldDefinitions): void
    {
        $serviceClassName = $fieldDefinitions['serviceClassName']
            ?? ($fieldDefinitions['service']
                ?? DummyService::class);
        $modelClassName = $fieldDefinitions['modelClassName']
            ?? ($fieldDefinitions['model']
                ?? DummyModel::class);

        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.service'));
        if (isset($fieldDefinitions['context'])) {
            $factory->setFactory($serviceClassName, [$tableName, $modelClassName, $fieldDefinitions['context']]);
        } else {
            $factory->setFactory($serviceClassName, [$tableName, $modelClassName]);
        }
    }
}
