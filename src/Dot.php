<?php
/**
 * Dot - PHP dot notation array access
 *
 * @author  Riku Särkinen <riku@adbar.io>
 * @link    https://github.com/adbario/php-dot-notation
 * @license https://github.com/adbario/php-dot-notation/blob/master/LICENSE.md (MIT License)
 */
namespace Adbar;

use ArrayAccess;

/**
 * Dot
 *
 * This class provides a dot notation access to the regular arrays and
 * ArrayAccess objects for easy multidimensional handling.
 */
class Dot implements ArrayAccess
{
    /**
     * The stored array
     *
     * @var array
     */
    protected $array;

    /**
     * Create a new Dot instance
     *
     * @param array $array Array to store
     */
    public function __construct($array = [])
    {
        $this->setArray($array);
    }

    /**
     * Set a value to a given path or an array of paths and values
     *
     * @param mixed $key   Path or an array of paths and values
     * @param mixed $value Value to set if the path is not an array
     */
    public function set($key, $value = null)
    {
        if (is_string($key)) {
            if (is_array($value) && !empty($value)) {
                // Iterate the values
                foreach ($value as $k => $v) {
                    $this->set("$key.$k", $v);
                }
            } else {
                // Iterate a path
                $keys = explode('.', $key);
                $array = &$this->array;

                foreach ($keys as $key) {
                    if (!isset($array[$key]) || !is_array($array[$key])) {
                        $array[$key] = [];
                    }

                    $array = &$array[$key];
                }

                // Set a value
                $array = $value;
            }
        } elseif (is_array($key)) {
            // Iterate an array of paths and values
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        }
    }

    /**
     * Add a value or an array of values to path
     *
     * @param mixed $key   Path or an array of paths and values
     * @param mixed $value Value to set if the path is not an array
     * @param bool  $pop   Helper to pop out the last key if the value is an array
     */
    public function add($key, $value = null, $pop = false)
    {
        if (is_string($key)) {
            if (is_array($value)) {
                // Iterate the values
                foreach ($value as $k => $v) {
                    $this->add("$key.$k", $v, true);
                }
            } else {
                // Iterate a path
                $keys = explode('.', $key);
                $array = &$this->array;

                if ($pop === true) {
                    array_pop($keys);
                }

                foreach ($keys as $key) {
                    if (!isset($array[$key]) || !is_array($array[$key])) {
                        $array[$key] = [];
                    }

                    $array = &$array[$key];
                }

                // Add a value
                $array[] = $value;
            }
        } elseif (is_array($key)) {
            // Iterate an array of paths and values
            foreach ($key as $k => $v) {
                $this->add($k, $v);
            }
        }
    }

    /**
     * Get a value from a path or default value if the path doesn't exist
     *
     * @param  string $key     Path
     * @param  mixed  $default Default value
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', (string)$key);
        $array = &$this->array;

        foreach ($keys as $key) {
            if (!$this->exists($array, $key)) {
                return $default;
            }

            $array = &$array[$key];
        }

        return $array;
    }

    /**
     * Get a value from a path or all the stored values and remove them
     *
     * @param  string|null $key     Path
     * @param  mixed       $default Default value
     * @return mixed
     */
    public function pull($key = null, $default = null)
    {
        if (is_string($key)) {
            // Get a value from a path
            $value = $this->get($key, $default);
            $this->delete($key);

            return $value;
        }

        if (is_null($key)) {
            // Get all the stored values
            $value = $this->all();
            $this->clear();

            return $value;
        }
    }

    /**
     * Get all the stored values
     *
     * @return array
     */
    public function all()
    {
        return $this->array;
    }

    /**
     * Check if a path exists
     *
     * @param  string $key Path
     * @return bool
     */
    public function has($key)
    {
        $keys = explode('.', (string)$key);
        $array = &$this->array;

        foreach ($keys as $key) {
            if (!$this->exists($array, $key)) {
                return false;
            }

            $array = &$array[$key];
        }

        return true;
    }

    /**
     * Determine if the given key exists in the provided array
     *
     * @param  ArrayAccess|array $array
     * @param  string|int        $key
     * @return bool
     */
    public function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return isset($array[$key]);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Delete a path or an array of paths
     *
     * @param mixed $key Path or an array of paths to delete
     */
    public function delete($key)
    {
        if (is_string($key)) {
            // Iterate a path
            $keys = explode('.', $key);
            $array = &$this->array;
            $last = array_pop($keys);

            foreach ($keys as $key) {
                if (!$this->exists($array, $key)) {
                    return;
                }

                $array = &$array[$key];
            }

            unset($array[$last]);
        } elseif (is_array($key)) {
            // Iterate an array of paths
            foreach ($key as $k) {
                $this->delete($k);
            }
        }
    }

    /**
     * Delete all values from a given path,
     * from an array of paths or clear all the stored values
     *
     * @param mixed $key Path or an array of paths to clean
     */
    public function clear($key = null)
    {
        if (is_string($key)) {
            // Clear the path
            $this->set($key, []);
        } elseif (is_array($key)) {
            // Iterate an array of paths
            foreach ($key as $k) {
                $this->clear($k);
            }
        } elseif (is_null($key)) {
            // Clear all the stored arrays
            $this->array = [];
        }
    }

    /**
     * Sort the values of a path or all the stored values
     *
     * @param  string|null $key Path to sort
     * @return array
     */
    public function sort($key = null)
    {
        if (is_string($key)) {
            // Sort values of a path
            $values = $this->get($key);

            return $this->sortArray((array)$values);
        } elseif (is_null($key)) {
            // Sort all the stored values
            return $this->sortArray($this->array);
        }
    }

    /**
     * Recursively sort the values of a path or all the stored values
     *
     * @param  string|null $key   Path to sort
     * @param  array       $array Array to sort
     * @return array
     */
    public function sortRecursive($key = null, $array = null)
    {
        if (is_array($array)) {
            // Loop through an array
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $value = $this->sortRecursive(null, $value);
                }
            }

            return $this->sortArray($array);
        } elseif (is_string($key)) {
            // Sort values of a path
            $values = $this->get($key);

            return $this->sortRecursive(null, (array)$values);
        } elseif (is_null($key)) {
            // Sort all the stored values
            return $this->sortRecursive(null, $this->array);
        }
    }

    /**
     * Sort the given array
     *
     * @param  array $array Array to sort
     * @return array
     */
    public function sortArray($array)
    {
        $this->isAssoc($array) ? ksort($array) : sort($array);

        return $array;
    }

    /**
     * Determine whether the given value is array accessible
     *
     * @param  mixed $value Array to verify
     * @return bool
     */
    public function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if an array is associative
     *
     * @param  array|null $array Array to verify
     * @return bool
     */
    public function isAssoc($array = null)
    {
        $keys = is_array($array) ? array_keys($array) : array_keys($this->array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Store an array
     *
     * @param array $array
     */
    public function setArray($array)
    {
        if ($this->accessible($array)) {
            $this->array = $array;
        }
    }

    /**
     * Store an array as a reference
     *
     * @param array $array
     */
    public function setReference(&$array)
    {
        if ($this->accessible($array)) {
            $this->array = &$array;
        }
    }

    /*
     * --------------------------------------------------------------
     * ArrayAccess Abstract Methods
     * --------------------------------------------------------------
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /*
     * --------------------------------------------------------------
     * Magic Methods
     * --------------------------------------------------------------
     */
    public function __set($key, $value = null)
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        $this->delete($key);
    }
}
