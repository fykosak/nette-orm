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

    public function testCreateSuccess(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $countBefore = $serviceParticipant->getTable()->count('*');

        $newEvent = $serviceParticipant->storeModel(['event_id' => 1, 'name' => 'Igor']);
        $countAfter = $serviceParticipant->getTable()->count('*');

        Assert::same($countBefore + 1, $countAfter);
        Assert::type(ParticipantModel::class, $newEvent);
        Assert::same('Igor', $newEvent->name);
    }

    public function testCreateError(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $countBefore = $serviceParticipant->getTable()->count('*');
        Assert::exception(function () use ($serviceParticipant) {
            $serviceParticipant->storeModel(['event_id' => 4, 'name' => 'Igor']);
        }, ForeignKeyConstraintViolationException::class);

        $countAfter = $serviceParticipant->getTable()->count('*');

        Assert::same($countBefore, $countAfter);
    }

    public function testFindByPrimary(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->findByPrimary(1);

        Assert::same('Adam', $participant->name);
        Assert::type(ParticipantModel::class, $participant);

        $nullParticipant = $serviceParticipant->findByPrimary(10);
        Assert::null($nullParticipant);
    }

    public function testFindByPrimaryProtection(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->findByPrimary(null);

        Assert::null($participant);
    }

    public function testUpdateSuccess(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->findByPrimary(2);
        $serviceParticipant->storeModel(['name' => 'Betka'], $participant);
        Assert::same('Betka', $participant->name);
        Assert::type(ParticipantModel::class, $participant);
    }

    public function testUpdateError(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->findByPrimary(2);

        Assert::exception(function () use ($participant, $serviceParticipant) {
            $serviceParticipant->storeModel(['event_id' => 4], $participant);
        }, ForeignKeyConstraintViolationException::class);
        Assert::same('Bára', $participant->name);
    }

    public function testStoreExists(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->findByPrimary(2);
        $newParticipant = $serviceParticipant->storeModel(['name' => 'Betka'], $participant);
        Assert::same($participant, $newParticipant);// must be a same obj
        Assert::same('Betka', $participant->name);
        Assert::type(ParticipantModel::class, $participant);
    }

    public function testStoreNew(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $newParticipant = $serviceParticipant->storeModel(['event_id' => 1, 'name' => 'Igor'], null);
        Assert::same('Igor', $newParticipant->name);
        Assert::type(ParticipantModel::class, $newParticipant);
    }

    public function testDelete(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $participant = $serviceParticipant->findByPrimary(2);
        $countBefore = $serviceParticipant->getTable()->count('*');
        $serviceParticipant->disposeModel($participant);
        $countAfter = $serviceParticipant->getTable()->count('*');
        Assert::same($countBefore - 1, $countAfter);
    }

    public function testType(): void
    {
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $serviceEvent = $this->container->getByType(EventService::class);
        $event = $serviceEvent->getTable()->fetch();
        Assert::exception(function () use ($event, $serviceParticipant) {
            $serviceParticipant->disposeModel($event);
        }, \TypeError::class);
    }

    public function testDeleteError(): void
    {
        $serviceEvent = $this->container->getByType(EventService::class);
        $event = $serviceEvent->getTable()->fetch();
        Assert::exception(function () use ($event, $serviceEvent) {
            $serviceEvent->disposeModel($event);
        }, ForeignKeyConstraintViolationException::class);
    }
}

$testCase = new ServiceOperationTest();
$testCase->run();
