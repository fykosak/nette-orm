<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\NetteORM\Tests\ORM\EventModel;
use Fykosak\NetteORM\Tests\ORM\EventService;
use Fykosak\NetteORM\Tests\ORM\ParticipantModel;
use Fykosak\NetteORM\Tests\ORM\ParticipantService;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Tester\Assert;

require_once __DIR__ . '/TestCase.php';

class TableSelectionTest extends TestCase
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

    public function testType(): void
    {
        $selection = $this->eventService->getTable();
        Assert::type(TypedSelection::class, $selection);
        Assert::type(Selection::class, $selection);
        $event = $selection->fetch();
        Assert::type(ActiveRow::class, $event);
        Assert::type(EventModel::class, $event);
    }

    public function testReferenced(): void
    {
        $participant = $this->participantService->getTable()->fetch();

        Assert::type(ParticipantModel::class, $participant);
        $event = $participant->event;
        Assert::type(EventModel::class, $event);
    }

    public function testRelated(): void
    {
        $event = $this->eventService->getTable()->fetch();
        $participant = $event->related('participant')->fetch();
        Assert::type(ParticipantModel::class, $participant);
    }
/*
    public function testPassModel(): void
    {
        /** @var ParticipantService $serviceEvent
        $serviceEvent = $this->container->getByType(EventService::class);
        $event = $serviceEvent->getTable()->fetch();
        $newEvent = EventModel::createFromActiveRow($event);
        Assert::same($event, $newEvent);
    }*/
}

$testCase = new TableSelectionTest();
$testCase->run();
