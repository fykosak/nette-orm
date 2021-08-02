<?php
declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Tests\ORM\ModelParticipant;
use Fykosak\NetteORM\Tests\ORM\ServiceEvent;
use Fykosak\NetteORM\Tests\ORM\ServiceParticipant;
use Tester\Assert;

require_once __DIR__ . '/AbstractTestCase.php';

class ServiceOperationTest extends AbstractTestCase
{

    public function testCreateSuccess(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $countBefore = $serviceParticipant->getTable()->count('*');

        $newEvent = $serviceParticipant->createNewModel(['event_id' => 1, 'name' => 'Igor']);
        $countAfter = $serviceParticipant->getTable()->count('*');

        Assert::same($countBefore + 1, $countAfter);
        Assert::type(ModelParticipant::class, $newEvent);
        Assert::same('Igor', $newEvent->name);
    }

    public function testCreateError(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $countBefore = $serviceParticipant->getTable()->count('*');
        Assert::exception(function () use ($serviceParticipant) {
            $serviceParticipant->createNewModel(['event_id' => 4, 'name' => 'Igor']);
        }, ModelException::class);

        $countAfter = $serviceParticipant->getTable()->count('*');

        Assert::same($countBefore, $countAfter);
    }

    public function testFindByPrimary(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->findByPrimary(1);

        Assert::same('Adam', $participant->name);
        Assert::type(ModelParticipant::class, $participant);

        $nullParticipant = $serviceParticipant->findByPrimary(10);
        Assert::null($nullParticipant);
    }

    public function testFindByPrimaryProtection(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->findByPrimary(null);

        Assert::null($participant);
    }

    public function testUpdateSuccess(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->findByPrimary(2);
        $serviceParticipant->updateModel($participant, ['name' => 'Betka']);
        Assert::same('Betka', $participant->name);
        Assert::type(ModelParticipant::class, $participant);
    }

    public function testUpdateError(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->findByPrimary(2);

        Assert::exception(function () use ($participant, $serviceParticipant) {
            $serviceParticipant->updateModel($participant, ['event_id' => 4]);
        }, ModelException::class);
        Assert::same('Bára', $participant->name);
    }

    public function testStoreExists(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->findByPrimary(2);
        $newParticipant = $serviceParticipant->storeModel(['name' => 'Betka'], $participant);
        Assert::same($participant, $newParticipant);// must be a same obj
        Assert::same('Betka', $participant->name);
        Assert::type(ModelParticipant::class, $participant);
    }

    public function testStoreNew(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $newParticipant = $serviceParticipant->storeModel(['event_id' => 1, 'name' => 'Igor'], null);
        Assert::same('Igor', $newParticipant->name);
        Assert::type(ModelParticipant::class, $newParticipant);
    }

    public function testDelete(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->findByPrimary(2);
        $countBefore = $serviceParticipant->getTable()->count('*');
        $serviceParticipant->disposeModel($participant);
        $countAfter = $serviceParticipant->getTable()->count('*');
        Assert::same($countBefore - 1, $countAfter);
    }

    public function testType(): void
    {
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $serviceEvent = $this->container->getByType(ServiceEvent::class);
        $event = $serviceEvent->getTable()->fetch();
        Assert::exception(function () use ($event, $serviceParticipant) {
            $serviceParticipant->disposeModel($event);
        }, \InvalidArgumentException::class);
    }

    public function testDeleteError(): void
    {
        $serviceEvent = $this->container->getByType(ServiceEvent::class);
        $event = $serviceEvent->getTable()->fetch();
        Assert::exception(function () use ($event, $serviceEvent) {
            $serviceEvent->disposeModel($event);
        }, ModelException::class);
    }

}

$testCase = new ServiceOperationTest();
$testCase->run();
