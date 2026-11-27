<?php

declare(strict_types=1);

namespace KaririCode\DataStructure\Set;

use KaririCode\Contract\DataStructure\Set;
use KaririCode\DataStructure\Map\TreeMap;

/**
 * TreeSet implementation.
 *
 * This class implements a set using a TreeMap to store elements. It provides O(log n) time complexity
 * for add, remove, and contains operations.
 *
 * @category  Sets
 *
 * @author    Walmir Silva <walmir.silva@kariricode.org>
 * @license   MIT
 *
 * @see       https://kariricode.org/
 *
 * @implements \IteratorAggregate<int, mixed>
 * @implements Set<mixed>
 */
class TreeSet implements Set, \IteratorAggregate
{
    private TreeMap $map;

    public function __construct()
    {
        $this->map = new TreeMap();
    }

    #[\Override]
    public function add(mixed $element): void
    {
        if (null === $this->map->get($element)) {
            $this->map->put($element, true);
        }
    }

    #[\Override]
    public function remove(mixed $element): bool
    {
        return $this->map->remove($element);
    }

    #[\Override]
    public function clear(): void
    {
        $this->map = new TreeMap();
    }

    #[\Override]
    public function contains(mixed $element): bool
    {
        return null !== $this->map->get($element);
    }

    public function size(): int
    {
        return $this->map->size();
    }

    #[\Override]
    public function union(Set $other): TreeSet
    {
        $resultSet = new TreeSet();
        foreach ($this as $item) {
            $resultSet->add($item);
        }
        foreach ($other as $item) {
            $resultSet->add($item);
        }

        return $resultSet;
    }

    #[\Override]
    public function intersection(Set $other): TreeSet
    {
        $resultSet = new TreeSet();
        foreach ($this as $item) {
            if ($other->contains($item)) {
                $resultSet->add($item);
            }
        }

        return $resultSet;
    }

    #[\Override]
    public function difference(Set $other): TreeSet
    {
        $resultSet = new TreeSet();
        foreach ($this as $item) {
            if (! $other->contains($item)) {
                $resultSet->add($item);
            }
        }

        return $resultSet;
    }

    #[\Override]
    public function find(mixed $element): mixed
    {
        return $this->contains($element) ? $element : null;
    }

    /**
     * @return \Traversable<int, mixed>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->map->keys());
    }

    /**
     * @return list<mixed>
     */
    public function getItems(): array
    {
        $elements = [];
        foreach ($this as $item) {
            $elements[] = $item;
        }

        return $elements;
    }
}
