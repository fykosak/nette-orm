<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Tests\ORM;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read ActiveRow $event
 */
class ModelParticipant extends AbstractModel
{

    public function getEvent(): ModelEvent
    {
        return ModelEvent::createFromActiveRow($this->event);
    }
}
