<?php

declare(strict_types=1);

namespace KaririCode\DataStructure\Stack;

use KaririCode\Contract\DataStructure\Stack;

/**
 * ArrayStack implementation.
 *
 * This class implements a stack using a dynamic array.
 * It provides O(1) time complexity for push, pop, and peek operations.
 *
 * @category  Stacks
 *
 * @author    Walmir Silva <walmir.silva@kariricode.org>
 * @license   MIT
 *
 * @see       https://kariricode.org/
 */
class ArrayStack implements Stack
{
    /** @var list<mixed> */
    private array $elements = [];

    #[\Override]
    public function push(mixed $element): void
    {
        $this->elements[] = $element;
    }

    #[\Override]
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }

        return array_pop($this->elements);
    }

    #[\Override]
    public function peek(): mixed
    {
        $last = array_key_last($this->elements);

        return null === $last ? null : $this->elements[$last];
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    public function size(): int
    {
        return \count($this->elements);
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * @return list<mixed>
     */
    public function getItems(): array
    {
        return array_reverse($this->elements);
    }
}
