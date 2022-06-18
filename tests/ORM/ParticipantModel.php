<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\ORM;

use Fykosak\NetteORM\Model;

class ParticipantModel extends Model
{
    public function getEvent(): EventModel
    {
        return EventModel::createFromActiveRow($this->event);
    }
}
