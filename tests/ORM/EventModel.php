<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\ORM;

use DateTimeInterface;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $event_id
 * @property-read DateTimeInterface $begin
 * @property-read DateTimeInterface $end
 */
class EventModel extends Model
{
}
