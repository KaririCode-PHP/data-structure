<?php

declare(strict_types=1);

namespace KaririCode\DataStructure\Collection;

use KaririCode\Contract\DataStructure\Structural\Collection;

class ArrayList implements Collection
{
    /** @var array<int, mixed> */
    private array $elements = [];

    #[\Override]
    public function add(mixed $element): void
    {
        $this->elements[] = $element;
    }

    #[\Override]
    public function addAll(Collection $collection): void
    {
        foreach ($collection->getItems() as $element) {
            $this->add($element);
        }
    }

    #[\Override]
    public function remove(mixed $element): bool
    {
        $index = array_search($element, $this->elements, true);

        if (false !== $index) {
            array_splice($this->elements, $index, 1);

            return true;
        }

        return false;
    }

    #[\Override]
    public function contains(mixed $element): bool
    {
        return \in_array($element, $this->elements, true);
    }

    #[\Override]
    public function find(mixed $element): ?int
    {
        $index = array_search($element, $this->elements, true);

        return \is_int($index) ? $index : null;
    }

    /**
     * @return array<int, mixed>
     */
    #[\Override]
    public function getItems(): array
    {
        return $this->elements;
    }

    #[\Override]
    public function clear(): void
    {
        $this->elements = [];
    }

    #[\Override]
    public function size(): int
    {
        return \count($this->elements);
    }

    #[\Override]
    public function get(mixed $index): mixed
    {
        if (! $this->isValidArrayKey($index) || ! \array_key_exists($index, $this->elements)) {
            throw new \OutOfRangeException('Key not found: ' . $this->keyToString($index));
        }

        return $this->elements[$index];
    }

    #[\Override]
    public function set(mixed $index, mixed $element): void
    {
        if (! $this->isValidArrayKey($index)) {
            throw new \InvalidArgumentException('Invalid key type: ' . \gettype($index));
        }

        if (! \array_key_exists($index, $this->elements)) {
            throw new \OutOfRangeException('Key not found: ' . $this->keyToString($index));
        }

        $this->elements[$index] = $element;
    }

    /**
     * @phpstan-assert-if-true int|string $key
     *
     * @psalm-assert-if-true int|string $key
     */
    private function isValidArrayKey(mixed $key): bool
    {
        return \is_int($key) || \is_string($key);
    }

    private function keyToString(mixed $key): string
    {
        if (\is_object($key)) {
            // Check if the object can be converted to a string
            if (method_exists($key, '__toString')) {
                return (string) $key;
            }

            // Fallback for objects without a __toString method
            return \get_class($key) . '@' . spl_object_id($key);
        }

        if (\is_array($key)) {
            return 'Array';
        }

        if (\is_resource($key)) {
            // Returns a safe string representation for resources
            return get_resource_type($key) . ' #' . (int) $key;
        }

        // Now that objects, arrays, and resources are handled,
        // the remaining types (scalar and null) can be safely cast to string.
        return \is_scalar($key) || null === $key ? (string) $key : get_debug_type($key);
    }
}
