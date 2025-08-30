<?php //phpcs:ignore

declare(strict_types=1);

namespace Fykosak\NetteORM\Types;

use Nette\InvalidStateException;

final readonly class WGS84Point
{
    protected const int GPSSRId = 4326; //phpcs:ignore
    protected const int TypEndian = 1; //phpcs:ignore
    protected const int PointType = 1; //phpcs:ignore

    public function __construct(
        public float $longitude,
        public float $latitude,
    ) {
    }

    /**
     * @return array{float,float,float}
     */
    private function floatToDeg(float $value): array
    {
        $deg = floor($value);
        $rest = ($value - $deg) * 60;
        $minutes = floor($rest);
        $rest = ($rest - $minutes) * 60;
        $seconds = $rest;
        return [$deg, $minutes, $seconds];
    }

    /**
     * @phpstan-param 'longitude'|'latitude' $axis
     */
    public function toHumanString(float $value, string $axis, bool $escape = true): string
    {
        if ($value < 0) {
            $label = $axis === 'longitude' ? 'W' : 'S';
        } else {
            $label = $axis === 'longitude' ? 'E' : 'N';
        }
        [$deg, $minutes, $seconds] = $this->floatToDeg(abs($value));
        if ($escape) {
            return sprintf('%d&deg; %d&apos; %.2f&quot; %s', $deg, $minutes, $seconds, $label);
        } else {
            return sprintf('%d° %d\' %.2f" %s', $deg, $minutes, $seconds, $label);
        }
    }

    public static function fromBytes(?string $data): ?self
    {
        if (!isset($data)) {
            return null;
        }
        $parsed = unpack("Lsrid/cendian/Vtype/dx/dy", $data);
        if ($parsed === false) {
            throw new InvalidStateException();
        }
        if ($parsed['srid'] !== self::GPSSRId) {//its GPS
            throw new InvalidStateException();
        }
        if ($parsed['endian'] !== self::TypEndian) {
            throw new InvalidStateException();
        }
        if ($parsed['type'] !== self::PointType) {
            throw new InvalidStateException();
        }
        return new self($parsed['x'], $parsed['y']);
    }

    public function toString(): string
    {
        return pack(
            'LcVdd',
            self::GPSSRId,
            self::TypEndian,
            self::PointType,
            $this->longitude,
            $this->latitude
        );
    }
}
