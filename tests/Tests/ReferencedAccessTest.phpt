<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
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
        /** @var ParticipantModel $participant */
        $participant = $serviceParticipant->getTable()->fetch();
        $modelEvent = $participant->getReferencedModel(EventModel::class);
        Assert::type(EventModel::class, $modelEvent);
    }

    public function testSame(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        /** @var ParticipantModel $participant */
        $participant = $serviceParticipant->getTable()->fetch();
        $newModel = $participant->getReferencedModel(ParticipantModel::class);
        Assert::same($participant, $newModel);
    }

    public function testNoCandidate(): void
    {
        /** @var EventService $service */
        $service = $this->container->getByType(EventService::class);
        /** @var EventModel $event */
        $event = $service->getTable()->fetch();
        Assert::exception(function () use ($event) {
            $event->getReferencedModel(ParticipantModel::class);
        }, CannotAccessModelException::class);
    }
}

$testCase = new ReferencedAccessTest();
$testCase->run();
