# Nette ORM

![GitHub branch checks state](https://img.shields.io/github/checks-status/fykosak/nette-orm/master)
<img src="https://img.shields.io/badge/coverage-83%25-green" />

## install

### Create ORM model

```php
<?php 
/**
 * @property-read string name
 * @property-read int event_id
 * @property-read \DateTimeInterface begin
 * @property-read \DateTimeInterface end
 * @note better typehinting for your IDE
 */
class ModelEvent extends AbstractModel {
    // you can define realtions
    public function getParticipants(): GroupedSelection {
        return $this->related('participant', 'event_id');
    }
    // you can define own metods
    public function __toArray(): array {
        return [
            'eventId' => $this->event_id,          
            'begin' => $this->begin ? $this->begin->format('c') : null,
            'end' => $this->end ? $this->end->format('c') : null,          
            'name' => $this->name,
        ];
    }
}
```

### Create ORM service

```php
<?php

class ServiceEvent extends AbstractService {

    public function getNextEvents(): TypedTableSelection {
        return $this->getTable()->where('begin > NOW()');
    }
}
```

### Register extension
```neon
orm:
    <table_name>:
        service: 'FQN of service'
        model: 'FQN of model'
    <another_table_name>:
        service: 'FQN of another service'
        model: 'FQN of another model'

```
```neon
extensions:
    orm: Fykosak\NetteORM\ORMExtension
```

---
## Examples

TypedTableSelection is a regular selection you can use all methods like in nette DB Selection.

```php 
$query= $sericeEvent->getNextEvent();
$query->where('name','My cool event');
```

TypedTableSelection return ORM model instead of `ActiveRow`, but ORM model is a descendant of a `ActiveRow`.

```php 
$query= $sericeEvent->getNextEvent();
foreach($query as $event){
$event // event is a ModelEvent
}

$model = $sericeEvent->getNextEvent()->fetch(); // Model is a ModelEvent too.
```

Take care `GroupedSelection` still return `ActiveRow`, you can use static method `createFromActiveRow`

```php 
$query= $sericeEvent->getParticipants();
foreach($query as $row){
// $row is a ActiveRow
$participant = ModelParticipant::createFromActiveRow($row);
}
```
Define relations between Models by methods
```php
class ModelParticipant extends AbstractModel {
    // use ActiveRow to resolve relations and next create a Model.
    public function getEvent(): ModelEvent {
        return  ModelEvent::createFromActiveRow($this->event);
    }
}
```

Now you can use `ReferencedAccessor` to access Model
```php
$myModel // any model that has define single method returned ModelEvent
$modelEvent = ReferencedAccessor::accessModel($myModel,ModelEvent::class);

```

---
## Complete Git history

This repository was split from [fskdb repo](https://github.com/fykosak/fksdb/).
In order to have complete history of relevant files, you need to add a graft to
your local repo copy that links both histories together
```
git remote add fksdb https://github.com/fykosak/fksdb.git
git fetch fksdb
git replace --graft 354f1903018856820eaa8a5d82f00fdbc2867b09 7bb11da6798323c708b3cb6a643b428f3f8644ee
```

Enjoy continuous history search
```
git log --follow src/AbstractModel.php
```
