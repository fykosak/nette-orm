<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Extension;
use Nette\Bridges\DatabaseDI\DatabaseExtension;
use Nette\Database\Explorer;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Environment;

define('TEMP_DIR', __DIR__ . '/../../temp');

require_once __DIR__ . '/../../vendor/autoload.php';

abstract class TestCase extends \Tester\TestCase
{

    protected Container $container;

    public function __construct()
    {
        Environment::setup();
        error_reporting(~E_DEPRECATED);
        $loader = new ContainerLoader(TEMP_DIR, true);

        $class = $loader->load(function (Compiler $compiler) {

            $compiler->addExtension('orm', new Extension());
            $compiler->addExtension('database', new DatabaseExtension());
            $compiler->loadConfig(__DIR__ . '/../config.neon');
        });

        $this->container = new $class();
    }

    protected function setUp(): void
    {
        Environment::lock('DB', TEMP_DIR);
        /** @var Explorer $explorer */
        $explorer = $this->container->getByType(Explorer::class);
        $explorer->query(
            "DELETE FROM `participant`;
DELETE FROM `event`;

INSERT INTO `event` (event_id, begin, end)
VALUES (1, '2010-01-01', '2010-02-01'),
       (2, '2010-02-01', '2010-03-01'),
       (3, '2010-03-01', '2010-04-01');
INSERT INTO `participant` (participant_id,event_id, name)
VALUES (1,1, 'Adam'),
       (2,1, 'Bára'),
       (3,1, 'Cecilia'),
       (4,2, 'Dano'),
       (5,2, 'Emil'),
       (6,3, 'Fero'),
       (7,3, 'Gustav'),
       (8,3, 'Husák');"
        );
        parent::setUp();
    }
}
