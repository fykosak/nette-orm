<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Tests\ORM\EventService;
use Fykosak\NetteORM\Tests\ORM\ParticipantModel;
use Fykosak\NetteORM\Tests\ORM\ParticipantService;
use Nette\Database\ForeignKeyConstraintViolationException;
use Tester\Assert;

require_once __DIR__ . '/TestCase.php';

class ServiceOperationTest extends TestCase
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

    public function testCreateSuccess(): void
    {
        $countBefore = $this->participantService->getTable()->count('*');

        $newEvent = $this->participantService->storeModel(['event_id' => 1, 'name' => 'Igor']);
        $countAfter = $this->participantService->getTable()->count('*');

        Assert::same($countBefore + 1, $countAfter);
        Assert::type(ParticipantModel::class, $newEvent);
        Assert::same('Igor', $newEvent->name);
    }

    public function testCreateError(): void
    {
        $countBefore = $this->participantService->getTable()->count('*');
        Assert::exception(function () {
            $this->participantService->storeModel(['event_id' => 4, 'name' => 'Igor']);
        }, ForeignKeyConstraintViolationException::class);

        $countAfter = $this->participantService->getTable()->count('*');

        Assert::same($countBefore, $countAfter);
    }

    public function testFindByPrimary(): void
    {
        $participant = $this->participantService->findByPrimary(1);

        Assert::same('Adam', $participant->name);
        Assert::type(ParticipantModel::class, $participant);

        $nullParticipant = $this->participantService->findByPrimary(10);
        Assert::null($nullParticipant);
    }

    public function testFindByPrimaryProtection(): void
    {
        $participant = $this->participantService->findByPrimary(null);

        Assert::null($participant);
    }

    public function testUpdateSuccess(): void
    {
        $participant = $this->participantService->findByPrimary(2);
        $this->participantService->storeModel(['name' => 'Betka'], $participant);
        Assert::same('Betka', $participant->name);
        Assert::type(ParticipantModel::class, $participant);
    }

    public function testUpdateError(): void
    {
        $participant = $this->participantService->findByPrimary(2);

        Assert::exception(function () use ($participant) {
            $this->participantService->storeModel(['event_id' => 4], $participant);
        }, ForeignKeyConstraintViolationException::class);
        Assert::same('Bára', $participant->name);
    }

    public function testStoreExists(): void
    {
        $participant = $this->participantService->findByPrimary(2);
        $newParticipant = $this->participantService->storeModel(['name' => 'Betka'], $participant);
        Assert::same($participant, $newParticipant);// must be a same obj
        Assert::same('Betka', $participant->name);
        Assert::type(ParticipantModel::class, $participant);
    }

    public function testStoreNew(): void
    {
        $newParticipant = $this->participantService->storeModel(['event_id' => 1, 'name' => 'Igor'], null);
        Assert::same('Igor', $newParticipant->name);
        Assert::type(ParticipantModel::class, $newParticipant);
    }

    public function testDelete(): void
    {
        $participant = $this->participantService->findByPrimary(2);
        $countBefore = $this->participantService->getTable()->count('*');
        $this->participantService->disposeModel($participant);
        $countAfter = $this->participantService->getTable()->count('*');
        Assert::same($countBefore - 1, $countAfter);
    }

    public function testType(): void
    {
        $event = $this->eventService->getTable()->fetch();
        Assert::exception(function () use ($event) {
            $this->participantService->disposeModel($event);
        }, \TypeError::class);
    }

    public function testDeleteError(): void
    {
        $event = $this->eventService->getTable()->fetch();
        Assert::exception(function () use ($event) {
            $this->eventService->disposeModel($event);
        }, ForeignKeyConstraintViolationException::class);
    }
}

$testCase = new ServiceOperationTest();
$testCase->run();
