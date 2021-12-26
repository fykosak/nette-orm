<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Tests\ORM\ModelEvent;
use Fykosak\NetteORM\Tests\ORM\ModelParticipant;
use Fykosak\NetteORM\Tests\ORM\ServiceEvent;
use Fykosak\NetteORM\Tests\ORM\ServiceParticipant;
use Fykosak\NetteORM\TypedTableSelection;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Tester\Assert;

require_once __DIR__ . '/AbstractTestCase.php';

class TableSelectionTest extends AbstractTestCase
{

    public function testType(): void
    {
        /** @var ServiceEvent $serviceEvent */
        $serviceEvent = $this->container->getByType(ServiceEvent::class);
        $selection = $serviceEvent->getTable();
        Assert::type(TypedTableSelection::class, $selection);
        Assert::type(Selection::class, $selection);
        $event = $selection->fetch();
        Assert::type(ActiveRow::class, $event);
        Assert::type(ModelEvent::class, $event);
    }

    public function testReferenced(): void
    {
        /** @var ServiceParticipant $serviceEvent */
        $serviceEvent = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceEvent->getTable()->fetch();

        Assert::type(ModelParticipant::class, $participant);
        $event = $participant->getEvent();
        Assert::type(ModelEvent::class, $event);
    }

    public function testRelated(): void
    {
        /** @var ServiceParticipant $serviceEvent */
        $serviceEvent = $this->container->getByType(ServiceEvent::class);
        $event = $serviceEvent->getTable()->fetch();
        $row = $event->related('participant')->fetch();

        Assert::false($row instanceof ModelParticipant);
        $participant = ModelParticipant::createFromActiveRow($row);
        Assert::type(ModelParticipant::class, $participant);
    }

    public function testPassModel(): void
    {
        /** @var ServiceParticipant $serviceEvent */
        $serviceEvent = $this->container->getByType(ServiceEvent::class);
        $event = $serviceEvent->getTable()->fetch();
        $newEvent = ModelEvent::createFromActiveRow($event);
        Assert::same($event, $newEvent);
    }
}

$testCase = new TableSelectionTest();
$testCase->run();
