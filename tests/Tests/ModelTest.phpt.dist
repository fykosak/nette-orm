<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\Tests;

use Fykosak\NetteORM\Tests\ORM\ParticipantModel;
use Fykosak\NetteORM\Tests\ORM\ParticipantService;
use Fykosak\NetteORM\Tests\ORM\ParticipantStatus;
use Tester\Assert;

require_once __DIR__ . '/TestCase.php';

class ModelTest extends TestCase
{
    public function testCreateFromEnum(): void
    {
        /** @var ParticipantService $serviceParticipant */
        $serviceParticipant = $this->container->getByType(ParticipantService::class);
        $countBefore = $serviceParticipant->getTable()->count('*');

        $newEvent = $serviceParticipant->storeModel(
            ['event_id' => 1, 'name' => 'Igor', 'status' => ParticipantStatus::Cancelled]
        );
        $countAfter = $serviceParticipant->getTable()->count('*');

        Assert::same($countBefore + 1, $countAfter);
        Assert::type(ParticipantModel::class, $newEvent);
        Assert::same(ParticipantStatus::Cancelled, $newEvent->status);
    }
}

$testCase = new ModelTest();
$testCase->run();
