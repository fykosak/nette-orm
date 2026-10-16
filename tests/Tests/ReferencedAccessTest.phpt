<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Tests\ORM\EventModel;
use Fykosak\NetteORM\Tests\ORM\EventService;
use Fykosak\NetteORM\Tests\ORM\ParticipantModel;
use Fykosak\NetteORM\Tests\ORM\ParticipantService;
use Tester\Assert;

require_once __DIR__ . '/TestCase.php';

class ReferencedAccessTest extends TestCase
{
    private EventService $eventService;
    private ParticipantService $participantService;

    public function injectEventService(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    public function injectParticipantService(ParticipantService $participantService): void
    {
        $this->participantService = $participantService;
    }

    public function testSuccess(): void
    {
        /** @var ParticipantModel $participant */
        $participant = $this->participantService->getTable()->fetch();
        $modelEvent = $participant->getReferencedModel(EventModel::class);
        Assert::type(EventModel::class, $modelEvent);
    }

    public function testSame(): void
    {
        /** @var ParticipantModel $participant */
        $participant = $this->participantService->getTable()->fetch();
        $newModel = $participant->getReferencedModel(ParticipantModel::class);
        Assert::same($participant, $newModel);
    }

    public function testNoCandidate(): void
    {
        /** @var EventModel $event */
        $event = $this->eventService->getTable()->fetch();
        Assert::exception(function () use ($event) {
            $event->getReferencedModel(ParticipantModel::class);
        }, CannotAccessModelException::class);
    }
}

$testCase = new ReferencedAccessTest();
$testCase->run();
