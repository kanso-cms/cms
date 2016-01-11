<?php

namespace Kanso\Utility;

/**
 * Collection of string manipulation methods.
 *
 */
class Str
{

	/**
	 * Reduce a string to x chars/words
	 *
	 * @param  string     $string      The string target
	 * @param  string     $length      The length to reduce it to
	 * @param  string     $suffix      A suffix to add (optional)
	 * @param  boolean    $toChar      Limit to characters or words (optional)
	 * @return string
	 */
	public static function reduce($string, $length, $suffix = '', $toChar = true)
	{
		if ($toChar) return (strlen($string) > $length ) ? substr($string, 0, $length).$suffix : $string;

		$words = explode(' ',$string);

		if(count($words) > $length) return implode(' ',array_slice($words, 0, $length)).$suffix;

		return $string;
	}

	/**
	 * Check if a string contains a word or letter
	 *
	 * @param  string    $string      The string target
	 * @param  string    $query       The query to check for   
	 * @return bool
	 */
	public static function contains($string, $query)
	{
		if (strpos($string, $query) !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Get characters after last occurance of character 
	 *
	 * @param  string    $query     The string query
	 * @param  string    $string    The string to be indexed 
	 * @return string
	 */
	public static function getAfterLastChar($string, $query)
	{
		if (!self::contains($string, $query)) return $string;
		return substr($string, strrpos($string, $query) + 1);
	}

	/**
	 * Get characters before last occurance of character 
	 *
	 * @param  string    $query     The string query
	 * @param  string    $string    The string to be indexed 
	 * @return string
	 */
	public static function getBeforeLastChar($string, $query)
	{
		if (!self::contains($string, $query)) return $string;
		return substr($string, 0,strrpos($string, $query));
	}

	/**
	 * Get characters after first occurance of character 
	 *
	 * @param  string    $query     The string query
	 * @param  string    $string    The string to be indexed 
	 * @return string
	 */
	public static function getAfterFirstChar($string, $query)
	{
		if (!self::contains($string, $query)) return $string;
		return substr($string, strpos($string, $query) + 1);
	}

	/**
	 * Get characters before first occurance of character 
	 *
	 * @param  string    $query     The string query
	 * @param  string    $string    The string to be indexed 
	 * @return string
	 */
	public static function getBeforeFirstChar($string, $query)
	{
		if (!self::contains($string, $query)) return $string;
		return substr($string, 0, strpos($string, $query));
	}

	/**
	 * Get characters after last occurance of word 
	 *
	 * @param  string    $query     The string query
	 * @param  string    $string    The string to be indexed 
	 * @return string
	 */
	public static function GetAfterLastWord($string, $query) 
	{

		if (!self::contains($string, $query)) return $string;
		# Return the part of the string found after the last occurrance
		# of the query
		$prefix = strpos($string, $query) + strlen($query);
		return substr($string, $prefix);
	}

	/**
	 * Get characters before last occurance of word 
	 *
	 * @param  string    $query     The string query
	 * @param  string    $string    The string to be indexed 
	 * @return string
	 */
	public static function GetBeforeLastWord($string, $query) 
	{

		if (!self::contains($string, $query)) return $string;
		# Return the part of the string found before the last occurrance
		# of the query
		$arr = explode($query, $string);
		return $arr[0];
	}

	/**
	 * Generate a random string
	 *
	 * @param  array    $length         The length of the string to be created (optional)
	 * @param  array    $withNumbers    Should numbers be included in the string (optional)
	 * @return string
	 */
	public static function generateRandom($length = 55, $withNumbers = true) 
	{

		$characters = $withNumbers ? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' : 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ;
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
		  $randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;

	}

	/**
	 * Compare multiple strings 
	 *
	 * @param  string   A parameter seperated list of strings to compare
	 * @return boolean
	 */
	public static function strcmpMulti()
	{

		$strs = func_get_args();
		if (empty($strs)) return false;
		$str  = $strs[0];
		foreach ($strs as $string) {
			if ($str !== $string) return false;
			$str = $string;
		}
		return true;

	}

	/**
	 * Compare two integers
	 *
	 * @param  int    $a
	 * @param  int    $b  
	 * @return boolean     
	 */
	public static function compareNumeric($a, $b) 
	{
		return (int)$a - (int)$b;
	}

	/**
	 * Compare two string alphabetically
	 *
	 * @param  string    $a
	 * @param  string    $b  
	 * @return boolean     
	 */
	public static function compareAlphaNumeric($a, $b) 
	{
		return strcasecmp($a,$b);
	}

	/**
	 * Filter a string into a valid slug
	 *
	 * @param  string    $str
	 * @return string
	 */
	public static function slugFilter($str) 
	{
		return strtolower(preg_replace("/[^a-zA-Z0-9 -]/", '', str_replace(' ', '-', $str)));
	}

	/**
	 * Create a boolean value
	 *
	 * @param  string    $str
	 * @return string
	 */
	public static function bool($mixedVar)
	{
		if (is_bool($mixedVar)) return (boolean)$mixedVar;
		if (is_integer($mixedVar)) return (boolean)$mixedVar;
		if (is_numeric($mixedVar)) {
			$mixedVar = (int)$mixedVar;
			return $mixedVar > 0;
		}
		if (is_string($mixedVar)) {
			$mixedVar = trim(strtolower($mixedVar));
			if ($mixedVar === 'yes') return true;
			if ($mixedVar === 'on')  return true;
			if ($mixedVar === 'true') return true;

			if ($mixedVar === 'no')  return false;
			if ($mixedVar === 'off')  return false;
			if ($mixedVar === 'false') return false;

		}
		return (boolean)$mixedVar;
	}

}