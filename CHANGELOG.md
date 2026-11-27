# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the package
follows [Semantic Versioning](https://semver.org/). Releases before 1.2.0 are
described on the [GitHub releases page](https://github.com/KaririCode-PHP/data-structure/releases).

## [Unreleased]

## [1.2.0] — 2026-11-27

Fixes found when the quality gate of KaririCode Devkit first ran on this
package: PER-CS, PHPStan level 9, Psalm and PHPUnit pass now.

### Fixed

- **`TreeMap` broke the red-black rules on removal.** Removing a node with
  two children compared the successor's colour with the wrong constant, so a
  black successor skipped the fix-up and a red one ran it; removing a black
  node other than the root with one red child left the child red; and a
  successor without a right child skipped the fix-up altogether. Lookups
  stayed correct, but paths lost their balance and operations degraded
  towards O(n). Removal now follows RB-DELETE (Cormen et al.) with null
  leaves.
- **`TreeMap` had two notions of key equality.** `put()` took keys of which
  neither orders before the other (1 and 1.0, 1 and "1") for the same key,
  while `get()`, `containsKey()` and `remove()` required `===`: after
  `put(1, …)`, `put(1.0, …)` updated the entry that `get(1.0)` could not
  find. Every operation now uses the ordering of `put()`.
- **A `CircularArrayQueue` (`ArrayQueue`, `ArrayDeque`) built with capacity
  0** failed on its first `enqueue()` with a division by zero. A capacity
  below 1 is refused when the queue is built, with
  `InvalidArgumentException`.
- `TreeMap::getItems()` converts a bool or a float key to an int as PHP
  does, without the deprecation PHP raises for a float, and throws a
  `TypeError` naming the type of a key that cannot be an array key.

### Changed

- The parameters of `ArrayList::get()`/`set()` and `LinkedList::get()`/`set()`
  are named `$index`, and that of `TreeSet::union()`, `intersection()` and
  `difference()` `$other`, as in the kariricode/contract interfaces: named
  arguments written against the interfaces failed with an unknown-parameter
  error. Code that passed `key:` or `otherSet:` to these classes must use
  the new names.
- The methods that implement the contract carry `#[\Override]`, and the
  docblocks give the element types of arrays and iterators.
- The lock holds kariricode/contract 2.12.1; the requirement stays `^2.0`.
- CI pins its actions by commit SHA and kcode 1.2.2 by SHA-256.

### Added

- Tests: the red-black rules after 4,000 random insertions and removals,
  checked against a plain array, and a regression test for each fix above.

[Unreleased]: https://github.com/KaririCode-PHP/data-structure/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/KaririCode-PHP/data-structure/releases/tag/v1.2.0
