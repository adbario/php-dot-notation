<?php

/**
 * Dot - PHP dot notation access to arrays
 *
 * @author  Riku Särkinen <riku@adbar.io>
 * @link    https://github.com/adbario/php-dot-notation
 * @license https://github.com/adbario/php-dot-notation/blob/3.x/LICENSE.md (MIT License)
 */

namespace Adbar;

use Countable;
use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use Traversable;

/**
 * Dot
 *
 * This class provides a dot notation access and helper functions for
 * working with arrays of data. Inspired by Laravel Collection.
 *
 * @template TKey of array-key
 * @template TValue mixed
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \IteratorAggregate<TKey, TValue>
 */
class Dot implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * The stored items
     *
     * @var array<TKey, TValue>
     */
    protected $items = [];

    /**
     * The character to use as a delimiter, defaults to dot (.)
     *
     * @var non-empty-string
     */
    protected $delimiter = ".";

    /**
     * Create a new Dot instance
     *
     * @param  mixed  $items
     * @param  bool  $parse
     * @param  non-empty-string  $delimiter
     * @return void
     */
    public function __construct($items = [], $parse = false, $delimiter = ".")
    {
        $items = $this->getArrayItems($items);

        $this->delimiter = $delimiter ?: ".";

        if ($parse) {
            $this->set($items);
        } else {
            $this->items = $items;
        }
    }

    /**
     * Set a given key / value pair or pairs
     * if the key doesn't exist already
     *
     * @param  array<TKey, TValue>|int|string  $keys
     * @param  mixed  $value
     * @return $this
     */
    public function add($keys, $value = null)
    {
        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $this->add($key, $value);
            }
        } elseif ($this->get($keys) === null) {
            $this->set($keys, $value);
        }

        return $this;
    }

    /**
     * Return all the stored items
     *
     * @return array<TKey, TValue>
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Delete the contents of a given key or keys
     *
     * @param  array<TKey>|int|string|null  $keys
     * @return $this
     */
    public function clear($keys = null)
    {
        if ($keys === null) {
            $this->items = [];

            return $this;
        }

        $keys = (array) $keys;

        foreach ($keys as $key) {
            $this->set($key, []);
        }

        return $this;
    }

    /**
     * Delete the given key or keys
     *
     * @param  array<TKey>|array<TKey, TValue>|int|string  $keys
     * @return $this
     */
    public function delete($keys)
    {
        $keys = (array) $keys;

        foreach ($keys as $key) {
            if ($this->exists($this->items, $key)) {
                unset($this->items[$key]);

                continue;
            }

            $items = &$this->items;
            $segments = explode($this->delimiter, $key);
            $lastSegment = array_pop($segments);

            foreach ($segments as $segment) {
                if (!isset($items[$segment]) || !is_array($items[$segment])) {
                    continue 2;
                }

                $items = &$items[$segment];
            }

            unset($items[$lastSegment]);
        }

        return $this;
    }

    /**
     * Checks if the given key exists in the provided array.
     *
     * @param  array<TKey, TValue>  $array Array to validate
     * @param  int|string  $key  The key to look for
     * @return bool
     */
    protected function exists($array, $key)
    {
        return array_key_exists($key, $array);
    }

    /**
     * Flatten an array with the given character as a key delimiter
     *
     * @param  string  $delimiter
     * @param  mixed  $items
     * @param  string  $prepend
     * @return array<TKey, TValue>
     */
    public function flatten($delimiter = '.', $items = null, $prepend = '')
    {
        $flatten = [];

        if ($items === null) {
            $items = $this->items;
        }

        foreach ($items as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $flatten[] = $this->flatten($delimiter, $value, $prepend . $key . $delimiter);
            } else {
                $flatten[] = [$prepend . $key => $value];
            }
        }

        return array_merge(...$flatten);
    }

    /**
     * Return the value of a given key
     *
     * @param  int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->items;
        }

        if ($this->exists($this->items, $key)) {
            return $this->items[$key];
        }

        if (!is_string($key) || strpos($key, $this->delimiter) === false) {
            return $default;
        }

        $items = $this->items;

        foreach (explode($this->delimiter, $key) as $segment) {
            if (!is_array($items) || !$this->exists($items, $segment)) {
                return $default;
            }

            $items = &$items[$segment];
        }

        return $items;
    }

    /**
     * Return the given items as an array
     *
     * @param  array<TKey, TValue>|self<TKey, TValue>|object|string  $items
     * @return array<TKey, TValue>
     */
    protected function getArrayItems($items)
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof self) {
            return $items->all();
        }

        return (array) $items;
    }

    /**
     * Check if a given key or keys exists
     *
     * @param  array<TKey>|int|string  $keys
     * @return bool
     */
    public function has($keys)
    {
        $keys = (array) $keys;

        if (!$this->items || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $items = $this->items;

            if ($this->exists($items, $key)) {
                continue;
            }

            foreach (explode($this->delimiter, $key) as $segment) {
                if (!is_array($items) || !$this->exists($items, $segment)) {
                    return false;
                }

                $items = $items[$segment];
            }
        }

        return true;
    }

    /**
     * Check if a given key or keys are empty
     *
     * @param  array<TKey>|int|string|null  $keys
     * @return bool
     */
    public function isEmpty($keys = null)
    {
        if ($keys === null) {
            return empty($this->items);
        }

        $keys = (array) $keys;

        foreach ($keys as $key) {
            if (!empty($this->get($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Merge a given array or a Dot object with the given key
     * or with the whole Dot object
     *
     * @param  array<TKey, TValue>|self<TKey, TValue>|string  $key
     * @param  array<TKey, TValue>|self<TKey, TValue>  $value
     * @return $this
     */
    public function merge($key, $value = [])
    {
        if (is_array($key)) {
            $this->items = array_merge($this->items, $key);
        } elseif (is_string($key)) {
            $items = (array) $this->get($key);
            $value = array_merge($items, $this->getArrayItems($value));

            $this->set($key, $value);
        } elseif ($key instanceof self) {
            $this->items = array_merge($this->items, $key->all());
        }

        return $this;
    }

    /**
     * Recursively merge a given array or a Dot object with the given key
     * or with the whole Dot object.
     *
     * Duplicate keys are converted to arrays.
     *
     * @param  array<TKey, TValue>|self<TKey, TValue>|string  $key
     * @param  array<TKey, TValue>|self<TKey, TValue>  $value
     * @return $this
     */
    public function mergeRecursive($key, $value = [])
    {
        if (is_array($key)) {
            $this->items = array_merge_recursive($this->items, $key);
        } elseif (is_string($key)) {
            $items = (array) $this->get($key);
            $value = array_merge_recursive($items, $this->getArrayItems($value));

            $this->set($key, $value);
        } elseif ($key instanceof self) {
            $this->items = array_merge_recursive($this->items, $key->all());
        }

        return $this;
    }

    /**
     * Recursively merge a given array or a Dot object with the given key
     * or with the whole Dot object.
     *
     * Instead of converting duplicate keys to arrays, the value from
     * given array will replace the value in Dot object.
     *
     * @param  array<TKey, TValue>|self<TKey, TValue>|string  $key
     * @param  array<TKey, TValue>|self<TKey, TValue>  $value
     * @return $this
     */
    public function mergeRecursiveDistinct($key, $value = [])
    {
        if (is_array($key)) {
            $this->items = $this->arrayMergeRecursiveDistinct($this->items, $key);
        } elseif (is_string($key)) {
            $items = (array) $this->get($key);
            $value = $this->arrayMergeRecursiveDistinct($items, $this->getArrayItems($value));

            $this->set($key, $value);
        } elseif ($key instanceof self) {
            $this->items = $this->arrayMergeRecursiveDistinct($this->items, $key->all());
        }

        return $this;
    }

    /**
     * Merges two arrays recursively. In contrast to array_merge_recursive,
     * duplicate keys are not converted to arrays but rather overwrite the
     * value in the first array with the duplicate value in the second array.
     *
     * @param  array<TKey, TValue>|array<TKey, array<TKey, TValue>>  $array1 Initial array to merge
     * @param  array<TKey, TValue>|array<TKey, array<TKey, TValue>>  $array2 Array to recursively merge
     * @return array<TKey, TValue>|array<TKey, array<TKey, TValue>>
     */
    protected function arrayMergeRecursiveDistinct(array $array1, array $array2)
    {
        $merged = &$array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Return the value of a given key and
     * delete the key
     *
     * @param  int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key = null, $default = null)
    {
        if ($key === null) {
            $value = $this->all();
            $this->clear();

            return $value;
        }

        $value = $this->get($key, $default);
        $this->delete($key);

        return $value;
    }

    /**
     * Push a given value to the end of the array
     * in a given key
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function push($key, $value = null)
    {
        if ($value === null) {
            $this->items[] = $key;

            return $this;
        }

        $items = $this->get($key);

        if (is_array($items) || $items === null) {
            $items[] = $value;
            $this->set($key, $items);
        }

        return $this;
    }

    /**
     * Replace all values or values within the given key
     * with an array or Dot object
     *
     * @param  array<TKey, TValue>|self<TKey, TValue>|string  $key
     * @param  array<TKey, TValue>|self<TKey, TValue>  $value
     * @return $this
     */
    public function replace($key, $value = [])
    {
        if (is_array($key)) {
            $this->items = array_replace($this->items, $key);
        } elseif (is_string($key)) {
            $items = (array) $this->get($key);
            $value = array_replace($items, $this->getArrayItems($value));

            $this->set($key, $value);
        } elseif ($key instanceof self) {
            $this->items = array_replace($this->items, $key->all());
        }

        return $this;
    }

    /**
     * Set a given key / value pair or pairs
     *
     * @param  array<TKey, TValue>|int|string  $keys
     * @param  mixed  $value
     * @return $this
     */
    public function set($keys, $value = null)
    {
        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $this->set($key, $value);
            }

            return $this;
        }

        $items = &$this->items;

        if (is_string($keys)) {
            foreach (explode($this->delimiter, $keys) as $key) {
                if (!isset($items[$key]) || !is_array($items[$key])) {
                    $items[$key] = [];
                }

                $items = &$items[$key];
            }
        }

        $items = $value;

        return $this;
    }

    /**
     * Replace all items with a given array
     *
     * @param  mixed  $items
     * @return $this
     */
    public function setArray($items)
    {
        $this->items = $this->getArrayItems($items);

        return $this;
    }

    /**
     * Replace all items with a given array as a reference
     *
     * @param  array<TKey, TValue>  $items
     * @return $this
     */
    public function setReference(array &$items)
    {
        $this->items = &$items;

        return $this;
    }

    /**
     * Return the value of a given key or all the values as JSON
     *
     * @param  mixed  $key
     * @param  int  $options
     * @return string|false
     */
    public function toJson($key = null, $options = 0)
    {
        if (is_string($key)) {
            return json_encode($this->get($key), $options);
        }

        $options = $key === null ? 0 : $key;

        return json_encode($this->items, $options);
    }

    /**
     * Output or return a parsable string representation of the
     * given array when exported by var_export()
     *
     * @param  array<TKey, TValue>  $items
     * @return object
     */
    public static function __set_state(array $items): object
    {
        return (object) $items;
    }

    /*
     * --------------------------------------------------------------
     * ArrayAccess interface
     * --------------------------------------------------------------
     */

    /**
     * Check if a given key exists
     *
     * @param  int|string  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Return the value of a given key
     *
     * @param  int|string  $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a given value to the given key
     *
     * @param int|string|null  $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value): void
    {
        if ($key === null) {
            $this->items[] = $value;

            return;
        }

        $this->set($key, $value);
    }

    /**
     * Delete the given key
     *
     * @param  int|string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->delete($key);
    }

    /*
     * --------------------------------------------------------------
     * Countable interface
     * --------------------------------------------------------------
     */

    /**
     * Return the number of items in a given key
     *
     * @param  int|string|null  $key
     * @return int
     */
    public function count($key = null): int
    {
        return count($this->get($key));
    }

    /*
     * --------------------------------------------------------------
     * IteratorAggregate interface
     * --------------------------------------------------------------
     */

    /**
     * Get an iterator for the stored items
     *
     * @return \ArrayIterator<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /*
     * --------------------------------------------------------------
     * JsonSerializable interface
     * --------------------------------------------------------------
     */

    /**
     * Return items for JSON serialization
     *
     * @return array<TKey, TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
