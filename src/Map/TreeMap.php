<?php

declare(strict_types=1);

namespace KaririCode\DataStructure\Map;

use KaririCode\Contract\DataStructure\Behavioral\IterableCollection;
use KaririCode\Contract\DataStructure\Map;
use KaririCode\DataStructure\Tree\TreeMapNode;

/**
 * TreeMap implementation.
 *
 * This class implements a map using a self-balancing binary search tree (Red-Black Tree).
 * It provides O(log n) time complexity for put, get, and remove operations.
 *
 * Keys are ordered with PHP's `<` and `>`: two keys neither of which is
 * smaller than the other are the same key, for put() as for get(),
 * containsKey() and remove().
 *
 * @category  Maps
 *
 * @author    Walmir Silva <walmir.silva@kariricode.org>
 * @license   MIT
 *
 * @implements \IteratorAggregate<array-key, mixed>
 *
 * @see       https://kariricode.org/
 */
class TreeMap implements Map, IterableCollection, \IteratorAggregate
{
    private ?TreeMapNode $root = null;
    private int $size = 0;

    #[\Override]
    public function put(mixed $key, mixed $value): void
    {
        if (null === $this->root) {
            $this->root = new TreeMapNode($key, $value);
            $this->root->setBlack();
            ++$this->size;

            return;
        }

        $newNode = $this->insertNode($this->root, $key, $value);

        // If insertNode returned a new node, it means the insertion occurred.
        if (null !== $newNode) {
            ++$this->size;
            $this->balanceAfterInsertion($newNode);
        }
    }

    #[\Override]
    public function get(mixed $key): mixed
    {
        return $this->findNode($key)?->value;
    }

    /**
     * @return list<mixed>
     */
    #[\Override]
    public function keys(): array
    {
        $keys = [];
        $this->inOrderTraversalKeys($this->root, $keys);

        return $keys;
    }

    /**
     * @return list<mixed>
     */
    #[\Override]
    public function values(): array
    {
        $values = [];
        $this->inOrderTraversalValues($this->root, $values);

        return $values;
    }

    #[\Override]
    public function remove(mixed $key): bool
    {
        $node = $this->findNode($key);
        if (null === $node) {
            return false;
        }
        $this->deleteNode($node);
        --$this->size;

        return true;
    }

    #[\Override]
    public function size(): int
    {
        return $this->size;
    }

    #[\Override]
    public function clear(): void
    {
        $this->root = null;
        $this->size = 0;
    }

    #[\Override]
    public function containsKey(mixed $key): bool
    {
        return null !== $this->findNode($key);
    }

    /**
     * The entries in key order, as an array: keys convert to array keys as
     * PHP converts them (a bool or a float to an int, null to "").
     *
     * @throws \TypeError when a key cannot be an array key (an array or an object)
     *
     * @return array<array-key, mixed>
     */
    public function getItems(): array
    {
        $items = [];
        $this->inOrderTraversal($this->root, $items);

        return $items;
    }

    /**
     * @return \Iterator<array-key, mixed>
     */
    #[\Override]
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->getItems());
    }

    /**
     * @param list<mixed> $keys
     */
    private function inOrderTraversalKeys(?TreeMapNode $node, array &$keys): void
    {
        if (null !== $node) {
            $this->inOrderTraversalKeys($node->left, $keys);
            $keys[] = $node->key;
            $this->inOrderTraversalKeys($node->right, $keys);
        }
    }

    /**
     * @param list<mixed> $values
     */
    private function inOrderTraversalValues(?TreeMapNode $node, array &$values): void
    {
        if (null !== $node) {
            $this->inOrderTraversalValues($node->left, $values);
            $values[] = $node->value;
            $this->inOrderTraversalValues($node->right, $values);
        }
    }

    /**
     * @param array<array-key, mixed> $items
     */
    private function inOrderTraversal(?TreeMapNode $node, array &$items): void
    {
        if (null !== $node) {
            $this->inOrderTraversal($node->left, $items);
            $items[$this->arrayKey($node->key)] = $node->value;
            $this->inOrderTraversal($node->right, $items);
        }
    }

    private function arrayKey(mixed $key): int|string
    {
        return match (true) {
            \is_int($key), \is_string($key) => $key,
            \is_bool($key), \is_float($key) => (int) $key,
            null === $key => '',
            default => throw new \TypeError('A ' . get_debug_type($key) . ' key cannot be an array key.'),
        };
    }

    /**
     * -1 when $a orders before $b, 1 when after, 0 when they are the same key.
     */
    private function compareKeys(mixed $a, mixed $b): int
    {
        if ($a < $b) {
            return -1;
        }

        return $a > $b ? 1 : 0;
    }

    private function insertNode(TreeMapNode $root, mixed $key, mixed $value): ?TreeMapNode
    {
        $parent = $root;
        $current = $root;
        $order = 0;
        while (null !== $current) {
            $parent = $current;
            $order = $this->compareKeys($key, $current->key);
            if (0 === $order) {
                // Key already exists, just update the value and do not insert a new node.
                $current->value = $value;

                return null; // Return null to indicate that no node was inserted.
            }
            $current = $order < 0 ? $current->left : $current->right;
        }

        $newNode = new TreeMapNode($key, $value);
        $newNode->parent = $parent;

        if ($order < 0) {
            $parent->left = $newNode;
        } else {
            $parent->right = $newNode;
        }

        return $newNode; // Return the newly created node.
    }

    private function balanceAfterInsertion(TreeMapNode $node): void
    {
        $parent = $node->parent;
        while (null !== $parent && $parent->isRed()) {
            // A red node is never the root, so the grandparent exists.
            $grandparent = $this->parentOf($parent);
            $parentIsLeft = $parent === $grandparent->left;
            $uncle = $parentIsLeft ? $grandparent->right : $grandparent->left;

            if (null !== $uncle && $uncle->isRed()) {
                $parent->setBlack();
                $uncle->setBlack();
                $grandparent->setRed();
                $node = $grandparent;
                $parent = $node->parent;

                continue;
            }

            if ($parentIsLeft && $node === $parent->right) {
                $node = $parent;
                $this->rotateLeft($node);
                $parent = $this->parentOf($node);
            } elseif (! $parentIsLeft && $node === $parent->left) {
                $node = $parent;
                $this->rotateRight($node);
                $parent = $this->parentOf($node);
            }

            $parent->setBlack();
            $grandparent->setRed();
            $this->rotate($grandparent, ! $parentIsLeft);
            $parent = $node->parent;
        }
        $this->root?->setBlack();
    }

    private function rotate(TreeMapNode $node, bool $toTheLeft): void
    {
        if ($toTheLeft) {
            $this->rotateLeft($node);

            return;
        }
        $this->rotateRight($node);
    }

    private function rotateLeft(TreeMapNode $node): void
    {
        $rightChild = $node->right ?? throw new \LogicException('A left rotation needs a right child.');
        $node->setRight($rightChild->left);

        $rightChild->parent = $node->parent;
        if (null === $node->parent) {
            $this->root = $rightChild;
        } elseif ($node === $node->parent->left) {
            $node->parent->left = $rightChild;
        } else {
            $node->parent->right = $rightChild;
        }
        $rightChild->left = $node;
        $node->parent = $rightChild;
    }

    private function rotateRight(TreeMapNode $node): void
    {
        $leftChild = $node->left ?? throw new \LogicException('A right rotation needs a left child.');
        $node->setLeft($leftChild->right);

        $leftChild->parent = $node->parent;
        if (null === $node->parent) {
            $this->root = $leftChild;
        } elseif ($node === $node->parent->right) {
            $node->parent->right = $leftChild;
        } else {
            $node->parent->left = $leftChild;
        }
        $leftChild->right = $node;
        $node->parent = $leftChild;
    }

    private function findNode(mixed $key): ?TreeMapNode
    {
        $current = $this->root;
        while (null !== $current) {
            $order = $this->compareKeys($key, $current->key);
            if (0 === $order) {
                return $current;
            }
            $current = $order < 0 ? $current->left : $current->right;
        }

        return null;
    }

    /**
     * Removes $node from the tree (Cormen et al., RB-DELETE), with null for
     * the leaves: $child takes the place of the node that leaves its
     * position, and $childParent is where it hangs, so the fix-up can climb
     * from there when $child is null.
     */
    private function deleteNode(TreeMapNode $node): void
    {
        $removedColor = $node->color;

        if (null === $node->left || null === $node->right) {
            $child = $node->left ?? $node->right;
            $childParent = $node->parent;
            $this->transplant($node, $child);
        } else {
            $successor = $this->minimum($node->right);
            $removedColor = $successor->color;
            $child = $successor->right;

            if ($successor->parent === $node) {
                $childParent = $successor;
            } else {
                $childParent = $successor->parent;
                $this->transplant($successor, $successor->right);
                $successor->setRight($node->right);
            }

            $this->transplant($node, $successor);
            $successor->setLeft($node->left);
            $successor->color = $node->color;
        }

        $node->left = null;
        $node->right = null;
        $node->parent = null;

        if (TreeMapNode::BLACK === $removedColor) {
            $this->balanceAfterRemoval($child, $childParent);
        }
    }

    private function transplant(TreeMapNode $u, ?TreeMapNode $v): void
    {
        if (null === $u->parent) {
            $this->root = $v;
        } elseif ($u === $u->parent->left) {
            $u->parent->left = $v;
        } else {
            $u->parent->right = $v;
        }
        if (null !== $v) {
            $v->parent = $u->parent;
        }
    }

    /**
     * RB-DELETE-FIXUP: $node carries an extra black, and a null $node is a
     * black leaf under $parent. The sibling of a doubly black position is
     * never null, since its side of the tree holds at least one black node.
     */
    private function balanceAfterRemoval(?TreeMapNode $node, ?TreeMapNode $parent): void
    {
        while (null !== $parent && $node !== $this->root && $this->isBlack($node)) {
            $isLeft = $node === $parent->left;
            $sibling = $this->siblingOf($parent, $isLeft);

            if ($sibling->isRed()) {
                $sibling->setBlack();
                $parent->setRed();
                $this->rotate($parent, $isLeft);
                $sibling = $this->siblingOf($parent, $isLeft);
            }

            $near = $isLeft ? $sibling->left : $sibling->right;
            $far = $isLeft ? $sibling->right : $sibling->left;

            if ($this->isBlack($near) && $this->isBlack($far)) {
                $sibling->setRed();
                $node = $parent;
                $parent = $node->parent;

                continue;
            }

            if ($this->isBlack($far)) {
                $near?->setBlack();
                $sibling->setRed();
                $this->rotate($sibling, ! $isLeft);
                $sibling = $this->siblingOf($parent, $isLeft);
                $far = $isLeft ? $sibling->right : $sibling->left;
            }

            $sibling->color = $parent->color;
            $parent->setBlack();
            $far?->setBlack();
            $this->rotate($parent, $isLeft);
            $node = $this->root;
            $parent = null;
        }
        $node?->setBlack();
    }

    private function isBlack(?TreeMapNode $node): bool
    {
        return null === $node || $node->isBlack();
    }

    private function parentOf(TreeMapNode $node): TreeMapNode
    {
        return $node->parent ?? throw new \LogicException('Red-black invariant broken: a red node without a parent.');
    }

    private function siblingOf(TreeMapNode $parent, bool $ofLeftChild): TreeMapNode
    {
        $sibling = $ofLeftChild ? $parent->right : $parent->left;

        return $sibling ?? throw new \LogicException('Red-black invariant broken: a doubly black node without a sibling.');
    }

    private function minimum(TreeMapNode $node): TreeMapNode
    {
        while (null !== $node->left) {
            $node = $node->left;
        }

        return $node;
    }
}
