<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\ORM;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read EventModel $event
 * @property-read ParticipantStatus $status
 * @property-read int $participant_id
 * @property-read int $event_id
 * @property-read string $name
 */
class ParticipantModel extends Model
{
}
