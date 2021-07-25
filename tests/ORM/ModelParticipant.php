<?php

namespace Fykosak\NetteORM\Tests\ORM;

use Fykosak\NetteORM\AbstractModel;

class ModelParticipant extends AbstractModel
{

    public function getEvent(): ModelEvent
    {
        return ModelEvent::createFromActiveRow($this->event);
    }
}
