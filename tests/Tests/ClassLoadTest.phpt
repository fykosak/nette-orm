<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\Tests\ORM\EventService;
use Fykosak\NetteORM\Tests\ORM\ParticipantService;
use Tester\Assert;

require_once __DIR__ . '/TestCase.php';

class ClassLoadTest extends TestCase
{

    public function testAlias(): void
    {
        $serviceEvent = $this->container->getByName('orm.event.service');
        Assert::type(Service::class, $serviceEvent);
        Assert::type(EventService::class, $serviceEvent);

        $serviceEvent = $this->container->getByName('orm.participant.service');
        Assert::type(Service::class, $serviceEvent);
        Assert::type(ParticipantService::class, $serviceEvent);
    }
}

$testCase = new ClassLoadTest();
$testCase->run();
