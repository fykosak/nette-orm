<?php

declare(strict_types=1);

namespace Fykosak\NetteORM;

final class LocalizedString
{
    private array $texts;

    public function __construct(array $texts)
    {
        $this->texts = $texts;
    }

    public function get(string $lang): ?string
    {
        return $this->texts[$lang] ?? null;
    }

    public function __serialize(): array
    {
        return $this->texts;
    }
}
