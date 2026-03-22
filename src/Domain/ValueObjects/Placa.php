<?php

declare(strict_types=1);

namespace FrotaSmart\Domain\ValueObjects;

use FrotaSmart\Domain\Exceptions\InvalidPlacaException;

final class Placa
{
    private string $value;

    public function __construct(string $placa)
    {
        $normalized = self::normalize($placa);

        if (! self::isValidFormat($normalized)) {
            throw InvalidPlacaException::forValue($placa);
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function normalize(string $placa): string
    {
        $placa = strtoupper(trim($placa));

        return str_replace(['-', ' '], '', $placa);
    }

    private static function isValidFormat(string $placa): bool
    {
        $oldPattern = '/^[A-Z]{3}[0-9]{4}$/';
        $mercosulPattern = '/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/';

        return preg_match($oldPattern, $placa) === 1 || preg_match($mercosulPattern, $placa) === 1;
    }
}
