<?php

namespace Fykosak\NetteORM;

use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;

class ORMExtension extends CompilerExtension {

    /**
     * @throws NotImplementedException
     */
    public function loadConfiguration(): void {
        foreach ($this->config as $tableName => $fieldDefinitions) {
            $this->registerORMService($tableName, $fieldDefinitions);
        }
    }

    final protected function registerORMService(string $tableName, array $fieldDefinitions): void {
        $serviceClassName = $fieldDefinitions['serviceClassName'] ?? ($fieldDefinitions['service'] ?? null);
        $modelClassName = $fieldDefinitions['modelClassName'] ?? ($fieldDefinitions['model'] ?? null);
        if ($serviceClassName) {
            $builder = $this->getContainerBuilder();
            $factory = $builder->addDefinition($this->prefix($tableName . '.service'));
            $factory->setFactory($serviceClassName, [$tableName, $modelClassName]);
        }
    }
}
