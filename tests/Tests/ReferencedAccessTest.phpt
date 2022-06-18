<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\ReferencedAccessor;
use Fykosak\NetteORM\Tests\ORM\EventModel;
use Fykosak\NetteORM\Tests\ORM\ParticipantModel;
use Fykosak\NetteORM\Tests\ORM\EventService;
use Fykosak\NetteORM\Tests\ORM\ParticipantService;
use Tester\Assert;

require_once __DIR__ . '/TestCase.php';

class ReferencedAccessTest extends TestCase
{

    public function testSuccess(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->getTable()->fetch();
        $modelEvent = ReferencedAccessor::accessModel($participant, EventModel::class);
        Assert::type(EventModel::class, $modelEvent);
    }

    public function testSame(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->getTable()->fetch();
        $newModel = ReferencedAccessor::accessModel($participant, ParticipantModel::class);
        Assert::same($participant, $newModel);
    }

    public function testNoCandidate(): void
    {
        /** @var EventService $service */
        $service = $this->container->getByType(EventService::class);
        $event = $service->getTable()->fetch();
        Assert::exception(function () use ($event) {
            ReferencedAccessor::accessModel($event, ParticipantModel::class);
        }, CannotAccessModelException::class);
    }
}

$testCase = new ReferencedAccessTest();
$testCase->run();
