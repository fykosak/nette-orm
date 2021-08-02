<?php
declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\ReferencedAccessor;
use Fykosak\NetteORM\Tests\ORM\ModelEvent;
use Fykosak\NetteORM\Tests\ORM\ModelParticipant;
use Fykosak\NetteORM\Tests\ORM\ServiceEvent;
use Fykosak\NetteORM\Tests\ORM\ServiceParticipant;
use Tester\Assert;

require_once __DIR__ . '/AbstractTestCase.php';

class ReferencedAccessTest extends AbstractTestCase
{

    public function testSuccess(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->getTable()->fetch();
        $modelEvent = ReferencedAccessor::accessModel($participant, ModelEvent::class);
        Assert::type(ModelEvent::class, $modelEvent);
    }

    public function testSame(): void
    {
        /** @var ServiceParticipant $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ServiceParticipant::class);
        $participant = $serviceParticipant->getTable()->fetch();
        $newModel = ReferencedAccessor::accessModel($participant, ModelParticipant::class);
        Assert::same($participant, $newModel);
    }

    public function testNoCandidate(): void
    {
        /** @var ServiceEvent $service */
        $service = $this->container->getByType(ServiceEvent::class);
        $event = $service->getTable()->fetch();
        Assert::exception(function () use ($event) {
            ReferencedAccessor::accessModel($event, ModelParticipant::class);
        }, CannotAccessModelException::class);
    }

}

$testCase = new ReferencedAccessTest();
$testCase->run();
