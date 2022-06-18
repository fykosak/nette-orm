<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Tests\ORM\EventModel;
use Fykosak\NetteORM\Tests\ORM\ParticipantModel;
use Fykosak\NetteORM\Tests\ORM\EventService;
use Fykosak\NetteORM\Tests\ORM\ParticipantService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Tester\Assert;

require_once __DIR__ . '/TestCase.php';

class TableSelectionTest extends TestCase
{

    public function testType(): void
    {
        /** @var EventService $serviceEvent */
        $serviceEvent = $this->container->getByType(EventService::class);
        $selection = $serviceEvent->getTable();
        Assert::type(TypedSelection::class, $selection);
        Assert::type(Selection::class, $selection);
        $event = $selection->fetch();
        Assert::type(ActiveRow::class, $event);
        Assert::type(EventModel::class, $event);
    }

    public function testReferenced(): void
    {
        /** @var ParticipantService $serviceEvent */
        $serviceEvent = $this->container->getByType(ParticipantService::class);
        $participant = $serviceEvent->getTable()->fetch();

        Assert::type(ParticipantModel::class, $participant);
        $event = $participant->getEvent();
        Assert::type(EventModel::class, $event);
    }

    public function testRelated(): void
    {
        /** @var ParticipantService $serviceEvent */
        $serviceEvent = $this->container->getByType(EventService::class);
        $event = $serviceEvent->getTable()->fetch();
        $row = $event->related('participant')->fetch();

        Assert::false($row instanceof ParticipantModel);
        $participant = ParticipantModel::createFromActiveRow($row);
        Assert::type(ParticipantModel::class, $participant);
    }

    public function testPassModel(): void
    {
        /** @var ParticipantService $serviceEvent */
        $serviceEvent = $this->container->getByType(EventService::class);
        $event = $serviceEvent->getTable()->fetch();
        $newEvent = EventModel::createFromActiveRow($event);
        Assert::same($event, $newEvent);
    }
}

$testCase = new TableSelectionTest();
$testCase->run();
