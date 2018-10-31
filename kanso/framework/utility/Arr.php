<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\utility;

/**
 * Array utility functions.
 *
 * @author Joe J. Howard
 */
class Arr
{
	/**
	 * Sets an array value using "dot notation".
	 *
	 * @access public
	 * @param array  $array Array you want to modify
	 * @param string $path  Array path
	 * @param mixed  $value Value to set
	 */
	public static function set(array &$array, string $path, $value)
	{
		$segments = explode('.', $path);

		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if(!isset($array[$segment]) || !is_array($array[$segment]))
			{
				$array[$segment] = [];
			}

			$array =& $array[$segment];
		}

		$array[array_shift($segments)] = $value;
	}

	/**
	 * Search for an array value using "dot notation". Returns TRUE if the array key exists and FALSE if not.
	 *
	 * @access public
	 * @param  array  $array Array we're goint to search
	 * @param  string $path  Array path
	 * @return bool
	 */
	public static function has(array $array, string $path): bool
	{
		$segments = explode('.', $path);

		foreach($segments as $segment)
		{
			if(!is_array($array) || !isset($array[$segment]))
			{
				return false;
			}

			$array = $array[$segment];
		}

		return true;
	}

	/**
	 * Returns value from array using "dot notation".
	 *
	 * @access public
	 * @param  array      $array   Array we're going to search
	 * @param  string     $path    Array path
	 * @param  null|mixed $default Default return value
	 * @return null|mixed
	 */
	public static function get(array $array, string $path, $default = null)
	{
		$segments = explode('.', $path);

		foreach($segments as $segment)
		{
			if(!is_array($array) || !isset($array[$segment]))
			{
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * Deletes an array value using "dot notation".
	 *
	 * @access public
	 * @param  array  $array Array you want to modify
	 * @param  string $path  Array path
	 * @return bool
	 */
	public static function delete(array &$array, string $path): bool
	{
		$segments = explode('.', $path);

		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if(!isset($array[$segment]) || !is_array($array[$segment]))
			{
				return false;
			}

			$array =& $array[$segment];
		}

		unset($array[array_shift($segments)]);

		return true;
	}

	/**
	 * Returns a random value from an array.
	 *
	 * @access public
	 * @param  array $array Array you want to pick a random value from
	 * @return mixed
	 */
	public static function random(array $array)
	{
		return $array[array_rand($array)];
	}

	/**
	 * Returns TRUE if the array is associative and FALSE if not.
	 *
	 * @access public
	 * @param  array $array Array to check
	 * @return bool
	 */
	public static function isAssoc(array $array): bool
	{
		return count(array_filter(array_keys($array), 'is_string')) > 0;
	}

	/**
	 * Returns TRUE if the array is multi-dimensional and FALSE if not.
	 *
	 * @access public
	 * @param  array $arr Array to check
	 * @return bool
	 */
	public static function isMulti(array $array): bool
    {
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
            	return true;
            }
        }

        return false;
    }

	/**
	 * Returns the values from a single column of the input array, identified by the key.
	 *
	 * @access public
	 * @param  array  $array Array to pluck from
	 * @param  string $key   Array key
	 * @return array
	 */
	public static function pluck(array $array, string $key): array
	{
		return array_map(function($value) use ($key)
		{
			return is_object($value) ? $value->$key : $value[$key];
		}, $array);
	}

	/**
	 * Insert into an associative array at a specific index.
	 *
	 * @access public
	 * @param  array $array Array to use
	 * @param  mixed $item  Item to insert
	 * @param  int   $index Index to insert item at
	 * @return array
	 */
	public static function insertAt(array $array, $item, int $index): array
	{
		if (!self::isAssoc($array))
		{
			array_splice($array, $index, 0, $item);

			return $array;
		}

		if (!is_array($item))
		{
			$result   = [];
			$i        = 0;
			$inserted = false;

			foreach ($array as $key => $value)
			{
				if ($i === $index)
				{
					$inserted   = true;
					$result[] = $item;
					$i++;
				}

				$result[$key] = $value;

				$i++;
			}
			if (!$inserted)
			{
				$result[] = $item;
			}

			return $result;
		}

		$previousItems = array_slice($array, 0, $index, true);

	    $nextItems     = array_slice($array, $index, null, true);

	    return $previousItems + $item + $nextItems;
	}

	/**
	 * Returns TRUE if all needles exist in target array and FALSE if not.
	 *
	 * @access public
	 * @param  array $needles  Array of needles
	 * @param  array $haystack Array to check
	 * @return bool
	 */
	public static function issets(array $needles, array $haystack): bool
	{
		foreach ($needles as $needle)
		{
			if (!array_key_exists($needle, $haystack)) return false;
		}
		return true;
	}

	/**
	 * Unsets an array of needles from a target array.
	 *
	 * @access public
	 * @param  array $needles  Array of needles
	 * @param  array $haystack Array to modify
	 * @return array
	 */
	public static function unsets(array $needles, array $haystack): array
	{
		$result = [];

		foreach ($haystack as $key => $value)
		{
			if (!in_array($key, $needles)) $result[$key] = $value;
		}

		return $result;
	}

	/**
	 * Sort a multi-dimensional array by key.
	 *
	 * @access public
	 * @param  array  $array   Array to sort
	 * @param  string $key     Key to sort by
	 * @param  string $reverse Direction to sort 'ASC'|'DESC' (optional) (default 'ASC')
	 * @return array
	 */
	public static function sortMulti(array $array, string $key, string $direction = 'ASC'): array
	{
		// If the key uses dot notation, split it
		if (strpos($key, '.') !== false)
		{
			$key = explode('.', $key);
		}

		$usort = self::isAssoc($array) ? 'uasort' : 'usort';

		$usort($array, function($a, $b) use ($key)
	    {
	    	$aV = null;
	        $bV = null;

	        // If the key uses dot notation
	        if (is_array($key))
	        {
	        	$aV = (isset($a[$key[0]]) ? $a[$key[0]] : null);
	        	$bV = (isset($b[$key[0]]) ? $b[$key[0]] : null);

	        	if ($aV && $bV)
	        	{
	        		array_shift($key);

	        		foreach ($key as $k)
	        		{
	        			$aV = (isset($aV[$k]) ? $aV[$k] : null);
	        			$bV = (isset($bV[$k]) ? $bV[$k] : null);
	        		}
	        	}
	        }
	        else
	        {
	        	$aV = static::arrayLikeAccess($key, $a);
	        	$bV = static::arrayLikeAccess($key, $b);
	        }

	        if ($aV && $bV)
	        {
	            if (is_numeric($aV))
	            {
	            	return intval($aV) >= intval($bV);
	            }
	            elseif (is_string($aV))
	            {
	            	return strcasecmp($aV, $bV);
	            }
	            elseif (is_array($aV))
	            {
	            	return count($bV) - count($aV);
	            }
	        }

	        return true;
	    });

	    if ($direction !== 'ASC')
	    {
	    	$array = array_reverse($array);
	    }

	    return $array;
	}

	/**
	 * Get a value from an array or object.
	 *
	 * @access public
	 * @param  string $key   Key to use
	 * @param  mixed  $mixed Array or object
	 * @return mixed
	 */
	public static function arrayLikeAccess(string $key, $mixed)
	{
		if (is_array($mixed))
		{
			return isset($mixed[$key]) ? $mixed[$key] : null;
		}
		elseif (is_object($mixed))
		{
			return isset($mixed->{$key}) ? $mixed->{$key} : null;
		}
		return null;
	}

    /**
     * Implode an associative array by key.
     *
     * @access public
     * @param  string $key   Key to explode by
     * @param  array  $array Target array to use
     * @param  string $glue  String between pieces (optional) (default '')
     * @return string
     */
    public static function implodeByKey(string $key, array $array, string $glue = ''): string
    {
        $str = '';

        foreach ($array as $arr)
        {
            if (isset($arr[$key]))
            {
            	$str .= $arr[$key] . $glue;
            }
        }

        if ($glue === '')
        {
        	return $str;
        }

        $split = array_filter(explode($glue, $str));

        return implode($glue, $split);
    }

	/**
	 * Recursively check if a value is in a multi-dimensional array.
	 *
	 * @access public
	 * @param  string $needle   The value to search for
	 * @param  array  $haystack The array to search in
	 * @param  bool   $strict   Applies strict compassions between values (optional) (default FALSE)
	 * @return bool
	 */
	public static function inMulti(string $needle, array $haystack, bool $strict = false): bool
	{
	    foreach ($haystack as $item)
	    {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::inMulti($needle, $item, $strict)))
	        {
	            return true;
	        }
	    }

	    return false;
	}

	/**
	 * Paginate an array. Returns FALSE if current page is more than max pages.
	 *
	 * @access public
	 * @param  array       $list  Array of data to paginated
	 * @param  int         $page  The current page to return
	 * @param  int         $limit How many items per page
	 * @return array|false
	 */
	public static function paginate(array $list, int $page, int $limit)
	{
		$total            = count($list);
		$limit            = ($limit ? $limit : 10);
		$pages            = ceil($total / $limit);
		$page             = ($page === false || $page ===  0 ? 1 : $page);
		$offset           = ($page - 1)  * $limit;
		$start            = $offset + 1;
		$end              = min(($offset + $limit), $total);
		if ($page > $pages) return false;
		$paged = [];
		for ($i=0; $i < (int) $pages; $i++) {
		 	$offset  = $i * $limit;
		    $paged[] = array_slice($list, $offset, $limit);
		}
		return $paged;
	}
}
