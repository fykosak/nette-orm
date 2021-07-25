<?php

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\Tests\ORM\ServiceEvent;
use Fykosak\NetteORM\Tests\ORM\ServiceParticipant;
use Tester\Assert;

require_once __DIR__ . '/AbstractTestCase.php';

class ClassLoadTest extends AbstractTestCase
{

    public function testAlias(): void
    {
        $serviceEvent = $this->container->getByName('orm.event.service');
        Assert::type(AbstractService::class, $serviceEvent);
        Assert::type(ServiceEvent::class, $serviceEvent);

        $serviceEvent = $this->container->getByName('orm.participant.service');
        Assert::type(AbstractService::class, $serviceEvent);
        Assert::type(ServiceParticipant::class, $serviceEvent);
    }
}

$testCase = new ClassLoadTest();
$testCase->run();
