<?php
/**
 * Array helper.
 *
 * @package    Modseven
 * @category   Helpers
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) 2016-2019  Koseven Team
 * @copyright  (c) since 2019 Modseven Team
 * @license    https://koseven.ga/LICENSE
 */

namespace Modseven;

use Traversable;

class Arr
{
    /**
     * default delimiter for path()
     * @var string
     */
    public static string $delimiter = '.';

    /**
     * Fill an array with a range of numbers.
     *
     * @param integer $step stepping
     * @param integer $max ending number
     * @return  array
     */
    public static function range(int $step = 10, int $max = 100): array
    {
        if ($step < 1) {
            return [];
        }

        $array = [];
        for ($i = $step; $i <= $max; $i += $step) {
            $array[$i] = $i;
        }

        return $array;
    }

    /**
     * Retrieve a single key from an array. If the key does not exist in the
     * array, the default value will be returned instead.
     *
     * @param mixed $array array to extract from
     * @param string $key key name
     * @param mixed $default default value
     * @return  mixed
     */
    public static function get($array, string $key, $default = NULL)
    {
        return $array[$key] ?? $default;
    }

    /**
     * Retrieves multiple paths from an array. If the path does not exist in the
     * array, the default value will be added instead.
     *
     * @param array $array array to extract paths from
     * @param array $paths list of path
     * @param mixed $default default value
     * @return  array
     */
    public static function extract(array $array, array $paths, $default = NULL): array
    {
        $found = [];
        foreach ($paths as $path) {
            self::setPath($found, $path, self::path($array, $path, $default));
        }

        return $found;
    }

    /**
     * Set a value on an array by path.
     *
     * @param array $array Array to update
     * @param string|array $path Path
     * @param mixed $value Value to set
     * @param string $delimiter Path delimiter
     */
    public static function setPath(array & $array, $path, $value, ?string $delimiter = NULL): void
    {
        if (!$delimiter) {
            // Use the default delimiter
            $delimiter = static::$delimiter;
        }

        // The path has already been separated into keys
        $keys = $path;
        if (!is_array($path)) {
            // Split the keys by delimiter
            $keys = explode($delimiter, $path);
        }

        // Set current $array to inner-most array path
        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (ctype_digit($key)) {
                // Make the key an integer
                $key = (int)$key;
            }

            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        // Set key on inner-most array
        $array[array_shift($keys)] = $value;
    }

	/**
	 * Retrieves a value from a multi-dimensional array using a "path" of keys.
	 *
	 * The path can be a string using a delimiter (default '.') or an array of keys.
	 * Supports wildcard '*' to fetch multiple values.
	 *
	 * **Important:** Originally written in 2019, this method did not enforce strict type checking.
	 * In PHP 8.0+ with strict typing, passing non-array types would throw a TypeError.
	 * To preserve the ability to provide non-array values (e.g., null, string, int) and return
	 * a default, the `$array` parameter is not type-hinted as `array`.
	 *
	 * @param mixed $array The array to search. Non-array values will cause the method to return `$default`.
	 * @param string|array $path The path to the value, either as a delimiter-separated string or an array of keys.
	 * @param mixed $default The default value to return if the path is not found or input is not an array.
	 * @param string|null $delimiter The delimiter for string paths. Defaults to `static::$delimiter`.
	 *
	 * @return mixed The value found at the path, or `$default` if not found.
	 *
	 * @link https://www.php.net/manual/en/language.types.declarations.php Strict type declarations in PHP
	 * @link https://wiki.php.net/rfc/strict_types History of strict typing and type enforcement
	 * @link https://www.php.net/manual/en/language.types.declarations.strict.php Behavior in PHP 8+
	 *
	 * @since 2019 Original implementation in Modseven
	 * @since PHP 8.0 Updated to remove array type hint to maintain backward compatibility with legacy tests
	 */
	public static function path($array, $path, $default = null, ?string $delimiter = null)
	{
		if (!self::isArray($array)) {
			return $default;
		}

		if (is_array($path)) {
			$keys = $path;
		} else {
			if (array_key_exists($path, $array)) {
				return $array[$path];
			}

			if ($delimiter === null) {
				$delimiter = static::$delimiter;
			}

			$path = ltrim($path, "{$delimiter} ");
			$path = rtrim($path, "{$delimiter} *");
			$keys = explode($delimiter, $path);
		}

		do {
			$key = array_shift($keys);

			/**
			 * Cast to string to avoid deprecation in PHP 8.1+ when passing int to ctype_digit().
			 * @link https://www.php.net/ctype_digit
			 * @link https://wiki.php.net/rfc/deprecations_php_8_1
			 */
			if (ctype_digit((string) $key)) {
				$key = (int)$key;
			}

			if (isset($array[$key])) {
				if ($keys) {
					if (self::isArray($array[$key])) {
						$array = $array[$key];
					} else {
						break;
					}
				} else {
					return $array[$key];
				}
			} elseif ($key === '*') {
				$values = [];
				foreach ($array as $arr) {
					if ($value = self::path($arr, implode($delimiter, $keys))) {
						$values[] = $value;
					}
				}
				if ($values) {
					return $values;
				}
				break;
			} else {
				break;
			}
		} while ($keys);

		return $default;
	}

    /**
     * Test if a value is an array with an additional check for array-like objects.
     *
     * @param mixed $value value to check
     * @return  boolean
     */
    public static function isArray($value): bool
    {
        if (is_array($value))
        {
            // Definitely an array
            return TRUE;
        }

        // Possibly a Traversable object, functionally the same as an array
        return (is_object($value) && $value instanceof Traversable);
    }

    /**
     * Retrieves muliple single-key values from a list of arrays.
     *
     * [!!] A list of arrays is an array that contains arrays, eg: array(array $a, array $b, array $c, ...)
     *
     * @param array $array list of arrays to check
     * @param string $key key to pluck
     *
     * @return  array
     */
    public static function pluck(array $array, string $key): array
    {
        $values = [];

        foreach ($array as $row) {
            if (isset($row[$key])) {
                // Found a value in this row
                $values[] = $row[$key];
            }
        }

        return $values;
    }

    /**
     * Adds a value to the beginning of an associative array.
     *
     * @param array $array array to modify
     * @param string $key array key name
     * @param mixed $val array value
     * @return  array
     */
    public static function unshift(array & $array, string $key, $val): array
    {
        $array = array_reverse($array, TRUE);
        $array[$key] = $val;
        $array = array_reverse($array, TRUE);

        return $array;
    }

    /**
     * Recursive version of [array_map](http://php.net/array_map), applies one or more
     * callbacks to all elements in an array, including sub-arrays.
     *
     * [!!] Because you can pass an array of callbacks, if you wish to use an array-form callback
     * you must nest it in an additional array as above. Calling Arr::map(array($this,'filter'), $array)
     * will cause an error.
     * [!!] Unlike `array_map`, this method requires a callback and will only map
     * a single array.
     *
     * @param mixed $callbacks array of callbacks to apply to every element in the array
     * @param array $array array to map
     * @param array $keys array of keys to apply to
     * @return  array
     */
    public static function map($callbacks, array $array, ?array $keys = NULL): array
    {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $array[$key] = self::map($callbacks, $array[$key], $keys);
            } elseif (!is_array($keys) || in_array($key, $keys, true)) {
                if (is_array($callbacks)) {
                    foreach ($callbacks as $cb) {
                        $array[$key] = $cb($array[$key]);
                    }
                } else {
                    $array[$key] = $callbacks($array[$key]);
                }
            }
        }

        return $array;
    }

	/**
	 * Recursively merge one or more arrays.
	 *
	 * This method merges arrays with the following rules:
	 * - Associative keys are overwritten by later arrays.
	 * - Numeric keys are appended without duplication.
	 * - Nested arrays are merged recursively.
	 * - Multiple arrays can be merged sequentially using variadic arguments.
	 *
	 * This behavior ensures compatibility with legacy Modseven array handling
	 * while adapting to stricter type enforcement introduced in PHP 8.x.
	 *
	 * Usage note:
	 * This method replaces the original merge logic written in November 2019,
	 * which may behave differently under PHP 8.x when handling numeric keys
	 * or deeply nested arrays.
	 *
	 * @param array $array1 The base array to merge into.
	 * @param array $array2 The array to merge from.
	 * @param array ...$arrays Optional additional arrays to merge sequentially.
	 *
	 * @return array The resulting merged array.
	 *
	 * @link https://www.php.net/manual/en/function.array-merge.php Official PHP array_merge() reference
	 * @link https://www.php.net/manual/en/function.in-array.php Official PHP in_array() reference
	 * @link https://www.php.net/manual/en/language.types.array.php Array type handling in PHP
	 *
	 * @since Modseven 2019 Original method
	 * @since PHP 8.0 Adapted for strict typing and recursive merge
	 */
	public static function merge(array ...$arrays): array
	{
		$result = array_shift($arrays);

		foreach ($arrays as $array) {
			foreach ($array as $key => $value) {
				if (is_int($key)) {
					if (!in_array($value, $result, true)) {
						$result[] = $value;
					}
				} elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
					$result[$key] = self::merge($result[$key], $value);
				} else {
					$result[$key] = $value;
				}
			}
		}

		return $result;
	}


	/**
     * Tests if an array is associative or not.
     *
     * @param array $array array to check
     * @return  boolean
     */
    public static function isAssoc(array $array): bool
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

	/**
	 * Recursively overwrites the values of a base array with one or more arrays.
	 *
	 * This method replaces array values in a deep (recursive) manner,
	 * preserving keys from the base array but overwriting them with values from subsequent arrays.
	 * Supports variadic arguments, allowing multiple arrays to be merged in sequence.
	 *
	 * Motivation:
	 * - Original implementation of `overwrite` from Modseven (2019) had shallow merge logic.
	 * - PHP array behavior, especially with nested arrays and numeric keys, has evolved since PHP 7.x.
	 * - Recursive merging ensures consistent behavior across modern PHP versions (8.0+).
	 *
	 * Features:
	 * - Recursive merge for nested arrays with matching keys.
	 * - Variadic arguments support (`array ...$arrays`) for merging multiple arrays.
	 * - Adds new keys from subsequent arrays if they don't exist in the base array.
	 *
	 * Edge-case examples:
	 * ```php
	 * // Empty first array
	 * Arr::overwrite([], ['a' => 1]); // ['a' => 1]
	 *
	 * // Mixed numeric and string keys
	 * Arr::overwrite(['a' => 1, 'b' => 2], ['b' => 3, 'c' => 4]); // ['a' => 1, 'b' => 3, 'c' => 4]
	 *
	 * // Recursive merge
	 * Arr::overwrite(['person' => ['name' => 'John', 'age' => 25]], ['person' => ['age' => 30]]);
	 * // ['person' => ['name' => 'John', 'age' => 30]]
	 *
	 * // Multiple arrays (variadic)
	 * Arr::overwrite(['a' => 1], ['b' => 2], ['c' => 3]); // ['a' => 1, 'b' => 2, 'c' => 3]
	 * ```
	 *
	 * @param array $array1 The base array to overwrite.
	 * @param array ...$arrays One or more arrays to merge into the base array.
	 * @return array The resulting overwritten array.
	 *
	 * @link https://www.php.net/manual/en/function.array-replace-recursive.php Array replacement in PHP
	 * @link https://www.php.net/manual/en/language.types.array.php PHP Array type documentation
	 * @link https://github.com/phpclub/modseven-core/blob/heads/unittests/tests/Unit/Arr/ArrOverwriteTest.php ArrOverwriteTest for edge cases
	 *
	 * @since PHP 8.0 Recursive variadic overwrite introduced.
	 * @version 1.0.0 Updated for PHP 8.4 compatibility and consistent recursive merge.
	 */
	public static function overwrite(array $array1, array ...$arrays): array
	{
		foreach ($arrays as $array) {
			foreach ($array as $key => $value) {
				if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
					$array1[$key] = self::overwrite($array1[$key], $value);
				} else {
					$array1[$key] = $value;
				}
			}
		}

		return $array1;
	}


	/**
     * Creates a callable function and parameter list from a string representation.
     * Note that this function does not validate the callback string.
     *
     * @param string $str callback string
     * @return  array   function, params
     */
    public static function callback(string $str): array
    {
        // Overloaded as parts are found
        $params = NULL;

        // command[param,param]
        if (preg_match('/^([^(]*+)\((.*)\)$/', $str, $match)) {
            // command
            $command = $match[1];

            if ($match[2] !== '') {
                // param,param
                $params = preg_split('/(?<!\\\\),/', $match[2]);
                $params = str_replace('\,', ',', $params);
            }
        } else {
            // command
            $command = $str;
        }

        if (strpos($command, '::') !== FALSE) {
            // Create a static method callable command
            $command = explode('::', $command, 2);
        }

        return [$command, $params];
    }

	/**
	 * Flatten a multi-dimensional array into a single-level array.
	 *
	 * This method has been updated to fix issues with modern PHP versions:
	 *
	 * - Previously, passing non-array items (e.g., strings) to `array_merge()` would
	 *   cause ArgumentCountError or TypeError in PHP 8+.
	 * - Recursive flattening now ensures that only arrays are passed to `array_merge()`.
	 * - Scalar values are safely appended to the result array.
	 *
	 * Changes:
	 * 1. Added recursion safety: `self::flatten($item)` always returns an array.
	 * 2. Removed passing non-array elements directly to `array_merge()`.
	 * 3. Preserves order of elements while flattening deeply nested arrays.
	 *
	 * This makes the method compatible with PHP 7.4+ and PHP 8.x.
	 *
	 * @link https://www.php.net/manual/en/function.array-merge.php Official PHP documentation for array_merge()
	 * @link https://www.php.net/manual/en/language.types.array.php PHP array type reference
	 *
	 * @param array $array Input array to flatten
	 * @return array Flattened array with single-level structure
	 */
	public static function flatten(array $array): array
	{
		$result = [];

		foreach ($array as $item) {
			if (is_array($item)) {
				// Recursively flatten arrays and merge safely
				$result = array_merge($result, self::flatten($item));
			} else {
				$result[] = $item;
			}
		}

		return $result;
	}


}
