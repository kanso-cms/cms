<?php

namespace Kanso\Utility;

/**
 * Array helper
 *
 */
class Arr
{

	/**
	 * Paginate an array
	 *
	 * @param  array    $list    Array of data to paginated
	 * @param  int      $page    The current page to return 
	 * @param  int      $limit   How many data per page
	 * @return array|false            Paginated array of data
	 */
	public static function paginate($list, $page, $limit) 
	{
		$total            = count($list); // Find out how many items are in the table
		$limit            = ($limit ? $limit : 10); // How many items to list per page
		$pages            = ceil($total / $limit); // How many pages will there be
		$page             = ($page === false || $page ===  0 ? 1 : $page); // What page are we currently on?
		$offset           = ($page - 1)  * $limit;  // Calculate the offset for the query    
		$start            = $offset + 1; // Some information to display to the user
		$end              = min(($offset + $limit), $total);
		if ($page > $pages) return false;
		$paged = [];
		for ($i=0; $i < (int)$pages; $i++) {
		  $offset  = $i * $limit;
		    $paged[] = array_slice($list, $offset, $limit);
		}
		return $paged;
	}

	/**
	 * Is associative array
	 *
	 * @param  array    $arr 
	 * @return boolean
	 */
	public static function isAssoc($arr)
	{
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * Is multi-dimenstional array
	 *
	 * @param  array    $arr 
	 * @return boolean
	 */
	public static function isMulti($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) return true;
        }
        return false;
    }

	/**
	 * Validate an array of needles are set in a haystack
	 *
	 * @param  array    $haystack
	 * @param  array    $needles 
	 * @return boolean
	 */
	public static function issets($needles, $haystack) 
	{
		foreach ($needles as $needle) {
			if (!isset($haystack[$needle])) return false;
		}
		return true;
	}
	/**
	 * Unset an array of keys in a target array
	 *
	 * @param  array    $haystack    The target array to be checked
	 * @param  array    $needles     An array of keys to be checked and removed
	 * @return bool
	 */
	public static function unsetMultiple($needles, $haystack) 
	{

		$response = [];
		foreach ($haystack as $key => $value) {
			if (!in_array($key, $needles)) $response[$key] = $value;
		}
		return $response;
		
	}

	/**
	 * Count a nested array 1 level deep
	 *
	 * @param  array    $array
	 * @return int
	 */
	public static function countNested($array)
	{
		$count = 0;

		if (!empty($array)) {
			foreach ($array as $key => $value) {
				$count += count($value);
			}
		}
		
		return $count;

	}

	/**
	 * Sort a multi-dimensional array by key
	 *
	 * @param  array      $array
	 * @param  string     $key
	 * @param  boolean    $reverse (optional)
	 * @return array     
	 */
	public static function sortMulti($array, $key, $direction = 'ASC') 
	{

		# If the key uses dot notation, split it
		if (strpos($key, '.') !== false) $key = explode('.', $key);

		uasort($array, function($a, $b) use ($key)
	    {
	    	$aV = null;
	        $bV = null;

	        # If the key uses dot notation
	        if (is_array($key)) {
	        	$aV = (isset($a[$key[0]]) ? $a[$key[0]] : null);
	        	$bV = (isset($b[$key[0]]) ? $b[$key[0]] : null);
	        	if ($aV && $bV) {
	        		array_shift($key);
	        		foreach ($key as $k) {
	        			$aV = (isset($aV[$k]) ? $aV[$k] : null);
	        			$bV = (isset($bV[$k]) ? $bV[$k] : null);
	        		}
	        	}
	        }
	        else {
	        	$aV = (isset($a[$key]) ? $a[$key] : null);
	        	$bV = (isset($b[$key]) ? $b[$key] : null);
	        }
	       

	        if ($aV && $bV) {
	            if (is_numeric($aV)) return (int)$aV - (int)$bV;
	            if (is_string($aV)) return strcasecmp($aV, $bV);
	            if (is_array($aV))  return  count($bV) - count($aV);
	        }
	        return true;
	    });
	    
	    if ($direction === 'DESC') return array_reverse($array);
	    return $array;
	}

	/**
	 * Implode an associative array by key
	 *
	 * @param  string      $key
	 * @param  array       $array
	 * @param  string      $glue (optional)
	 * @return string     
	 */
    public static function implodeByKey($key, $array, $glue = '')
    {
        $str = '';
        foreach ($array as $arr) {
            if (isset($arr[$key])) $str .= $arr[$key].$glue;
        }
        return rtrim($str, $glue);
    }

	/**
	 * Check if a value is in a multi-dimensional array
	 *
	 * @param  string      $needle
	 * @param  array       $haystack
	 * @param  boolean     $strict (optional)
	 * @return boolean     
	 */
	public static function inMulti($needle, $haystack, $strict = false) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::inMulti($needle, $item, $strict))) {
	            return true;
	        }
	    }

	    return false;
	}

}