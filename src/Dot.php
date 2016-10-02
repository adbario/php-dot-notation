<?php

namespace AdBar;

use ArrayAccess;

/**
 * Dot Notation
 *
 * This class provides dot notation access to arrays, so it's easy to handle
 * multidimensional data in a clean way.
 */
class Dot implements ArrayAccess
{
    /** @var array Data */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array|null $data Data
     */
    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            $this->data = $data;
        }
    }

    /**
     * Set value or array of values to path
     *
     * @param mixed      $key   Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     */
    public function set($key, $value = null)
    {
        if (is_string($key)) {
            if (is_array($value)) {
                // Iterate values
                foreach ($value as $k => $v) {
                    $this->set("$key.$k", $v);
                }
            } else {
                // Iterate path
                $keys = explode('.', $key);
                $data = &$this->data;
                foreach ($keys as $key) {
                    if (!isset($data[$key]) || !is_array($data[$key])) {
                        $data[$key] = [];
                    }
                    $data = &$data[$key];
                }
                // Set value to path
                $data = $value;
            }
        } elseif (is_array($key)) {
            // Iterate array of paths and values
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        }
    }

    /**
     * Add value or array of values to path
     *
     * @param mixed      $key   Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     * @param boolean    $pop   Helper to pop out last key if value is an array
     */
    public function add($key, $value = null, $pop = false)
    {
        if (is_string($key)) {
            if (is_array($value)) {
                // Iterate values
                foreach ($value as $k => $v) {
                    $this->add("$key.$k", $v, true);
                }
            } else {
                // Iterate path
                $keys = explode('.', $key);
                $data = &$this->data;
                if ($pop === true) {
                    array_pop($keys);
                }
                foreach ($keys as $key) {
                    if (!isset($data[$key]) || !is_array($data[$key])) {
                        $data[$key] = [];
                    }
                    $data = &$data[$key];
                }
                // Add value to path
                $data[] = $value;
            }
        } elseif (is_array($key)) {
            // Iterate array of paths and values
            foreach ($key as $k => $v) {
                $this->add($k, $v);
            }
        }
    }

    /**
     * Get value of path, default value if path doesn't exist or all data
     *
     * @param  mixed|null $key     Path
     * @param  mixed|null $default Default value
     * @return mixed               Value of path
     */
    public function get($key = null, $default = null)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            $data = &$this->data;
            foreach ($keys as $key) {
                if (!isset($data[$key])) {
                    return $default;
                }
                $data = &$data[$key];
            }
            // Get value
            return $data;
        } elseif (is_null($key)) {
            // Get all data
            return $this->data;
        }
    }

    /**
     * Check if path exists
     *
     * @param  string  $key Path
     * @return boolean
     */
    public function has($key)
    {
        $keys = explode('.', (string)$key);
        $data = &$this->data;
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return false;
            }
            $data = &$data[$key];
        }

        return true;
    }

    /**
     * Delete path or array of paths
     *
     * @param mixed $key Path or array of paths to delete
     */
    public function delete($key)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            $data = &$this->data;
            $last = array_pop($keys);
            foreach ($keys as $key) {
                if (!isset($data[$key])) {
                    return;
                }
                $data = &$data[$key];
            }
            if (isset($data[$last])) {
                // Detele path
                unset($data[$last]);
            }
        } elseif (is_array($key)) {
            // Iterate array of paths
            foreach ($key as $k) {
                $this->delete($k);
            }
        }
    }

    /**
     * Delete all data, data from path or array of paths and
     * optionally format path if it doesn't exist
     *
     * @param mixed|null $key    Path or array of paths to clean
     * @param boolean    $format Format option
     */
    public function clear($key = null, $format = false)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            $data = &$this->data;
            foreach ($keys as $key) {
                if (!isset($data[$key]) || !is_array($data[$key])) {
                    if ($format === true) {
                        $data[$key] = [];
                    } else {
                        return;
                    }
                }
                $data = &$data[$key];
            }
            // Clear path
            $data = [];
        } elseif (is_array($key)) {
            // Iterate array
            foreach ($key as $k) {
                $this->clear($k, $format);
            }
        } elseif (is_null($key)) {
            // Clear all data
            $this->data = [];
        }
    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Set data as a reference
     *
     * @param array $data
     */
    public function setDataAsRef(array &$data)
    {
        $this->data = &$data;
    }

    /**
     * ArrayAccess abstract methods
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

    /**
     * Magic methods
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
