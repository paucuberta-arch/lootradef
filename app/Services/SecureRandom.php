<?php

namespace App\Services;

use InvalidArgumentException;

class SecureRandom
{
    /** @template T @param array<int, T> $items @return array<int, T> */
    public function shuffle(array $items): array
    {
        $items = array_values($items);
        for ($index = count($items) - 1; $index > 0; $index--) {
            $swap = random_int(0, $index);
            [$items[$index], $items[$swap]] = [$items[$swap], $items[$index]];
        }

        return $items;
    }

    /** @template T @param array<int, T> $items @return T */
    public function pick(array $items): mixed
    {
        $items = array_values($items);
        if ($items === []) {
            throw new InvalidArgumentException('No se puede elegir de una colección vacía.');
        }

        return $items[random_int(0, count($items) - 1)];
    }

    public function index(array $items): int
    {
        if ($items === []) {
            throw new InvalidArgumentException('No se puede elegir de una colección vacía.');
        }

        return random_int(0, count($items) - 1);
    }
}
