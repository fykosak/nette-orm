<?php

declare(strict_types=1);

namespace Fykosak\NetteORM\Attributes;

use Attribute;

#[Attribute(\Attribute::TARGET_METHOD)]
class ReferencedFollow
{
    public function __construct(public bool $follow = true)
    {
    }
}
