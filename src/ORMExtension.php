<?php

namespace Fykosak\NetteORM;

use Nette\DI\CompilerExtension;
use Nette\NotImplementedException;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 */
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
        if (isset($fieldDefinitions['serviceClassName'])) {
            $builder = $this->getContainerBuilder();
            $factory = $builder->addDefinition($this->prefix($tableName . '.service'));
            $factory->setFactory($fieldDefinitions['serviceClassName'], [$tableName, $fieldDefinitions['modelClassName']]);
        }
    }
}
