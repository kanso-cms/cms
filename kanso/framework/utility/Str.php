<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\utility;

/**
 * String helper
 *
 * @author Joe J. Howard
 */
class Str
{
	/**
	 * Alphanumeric characters.
	 *
	 * @var string
	 */
	const ALNUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Alphabetic characters.
	 *
	 * @var string
	 */
	const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Hexadecimal characters.
	 *
	 * @var string
	 */
	const HEXDEC = '0123456789abcdef';

	/**
	 * Numeric characters.
	 *
	 * @var string
	 */
	const NUMERIC = '0123456789';

	/**
	 * ASCII symbols.
	 *
	 * @var string
	 */
	const SYMBOLS = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

	/**
	 * Replaces newline with <br> or <br />.
	 *
	 * @access public
	 * @param  string $string The input string
	 * @param  bool   $xhtml  Should we return XHTML?
	 * @return string
	 */
	public static function nl2br(string $string, bool $xhtml = false): string
	{
		return str_replace(["\r\n", "\n\r", "\n", "\r"], $xhtml === true ? '<br/>' : '<br>', $string);
	}

	/**
	 * Replaces <br> and <br /> with newline.
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function br2nl(string $string): string
	{
		return str_replace(['<br>', '<br/>', '<br />'], "\n", $string);
	}

	/**
	 * Converts camel case to underscored.
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function camel2underscored(string $string): string
	{
		return mb_strtolower(preg_replace('/([a-z])([A-Z])/u', "$1_$2", $string));
	}

	/**
	 * Converts camel case to space.
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function camel2case(string $string): string
	{
		$result = '';

		$chars  = str_split($string);

		foreach ($chars as $i => $char)
		{
			if (ctype_upper($char))
			{
				if (isset($chars[$i-1]) && $chars[$i-1] === ' ')
				{
					$result .= $char;
				}
				else
				{
					$result .= " $char";
				}
			}
			else
			{
				$result .= $char;
			}
		}

		return trim($result);
	}

	/**
	 * Converts underscored to camel case.
	 *
	 * @access public
	 * @param  string $string The input string
	 * @param  bool   $upper  Return upper case camelCase?
	 * @return string
	 */
	public static function underscored2camel(string $string, bool $upper = false): string
	{
		return preg_replace_callback(($upper ? '/(?:^|_)(.?)/u' : '/_(.?)/u'), function($matches){ return mb_strtoupper($matches[1]); }, $string);
	}

	/**
	 * Reduce a string to n characters or words
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  int    $length The length to reduce it to
	 * @param  string $suffix A suffix to add (optional) (default '')
	 * @param  bool   $toChar Limit to characters else words (optional) (default TRUE)
	 * @return string
	 */
	public static function reduce(string $string, int $length, string $suffix = '', bool $toChar = true): string
	{
		if ($toChar) return (strlen($string) > $length ) ? substr($string, 0, $length).$suffix : $string;

		$words = explode(' ',$string);

		if(count($words) > $length) return implode(' ',array_slice($words, 0, $length)).$suffix;

		return $string;
	}

	/**
	 * Check if a string contains another string
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  string $query  The query to check for   
	 * @return bool
	 */
	public static function contains(string $string, string $query): bool
	{
		return strpos($string, $query) !== false;
	}

	/**
	 * Get characters after last occurrence of string in a target string
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  string $query  The query to check for   
	 * @return bool
	 */
	public static function getAfterLastChar(string $string, string $query): string
	{
		if (!self::contains($string, $query)) return $string;
		
		return substr($string, strrpos($string, $query) + 1);
	}

	/**
	 * Get characters before last occurrence of character 
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  string $query  The query to check for   
	 * @return bool
	 */
	public static function getBeforeLastChar(string $string, string $query): string
	{
		if (!self::contains($string, $query)) return $string;
		
		return substr($string, 0,strrpos($string, $query));
	}

	/**
	 * Get characters after first occurrence of character 
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  string $query  The query to check for   
	 * @return bool
	 */
	public static function getAfterFirstChar(string $string, string $query): string
	{
		if (!self::contains($string, $query)) return $string;
		
		return substr($string, strpos($string, $query) + 1);
	}

	/**
	 * Get characters before first occurrence of character 
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  string $query  The query to check for   
	 * @return bool
	 */
	public static function getBeforeFirstChar(string $string, string $query): string
	{
		if (!self::contains($string, $query)) return $string;
		
		return substr($string, 0, strpos($string, $query));
	}

	/**
	 * Get characters after last occurrence of word 
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  string $query  The query to check for   
	 * @return bool
	 */
	public static function getAfterLastWord(string $string, string $query): string
	{
		if (!self::contains($string, $query))
		{
			return $string;
		}

		$split = explode($query, $string);

		return array_pop($split);
	}

	/**
	 * Get characters before last occurance of word 
	 *
	 * @access public
	 * @param  string $string The target string
	 * @param  string $query  The query to check for   
	 * @return bool
	 */
	public static function getBeforeLastWord(string $string, string $query): string
	{
		if (!self::contains($string, $query))
		{
			return $string;
		}

		$arr = explode($query, $string);

		array_pop($arr);

		return implode($query, $arr);
	}

	/**
	 * Returns a random string of the selected type and length.
	 *
     * @access public
	 * @param  int    $length Desired string length
	 * @param  string $pool   Character pool to use
	 * @return string
	 */
	public static function random(int $length = 55, string $pool = Str::ALNUM): string
	{
		$string = '';

		$poolSize = mb_strlen($pool) - 1;

		for($i = 0; $i < $length; $i++)
		{
			$string .= mb_substr($pool, random_int(0, $poolSize), 1);
		}

		return $string;
	}

	/**
	 * Compare two numerical strings
	 *
     * @access public
	 * @param  string $a
	 * @param  string $b  
	 * @return boolean     
	 */
	public static function compareNumeric(string $a, string $b): bool 
	{
		if (self::contains($a, '.') || self::contains($b, '.'))
		{
			return floatval($a) === floatval($b);
		}
		return intval($a)  === intval($b);
	}

	/**
	 * Creates a URL friendly string.
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function slug(string $str): string 
	{
    	return preg_replace('/-+/', '-', urlencode(ltrim(rtrim(preg_replace('/[^a-z0-9_-]/', '', preg_replace('/[\s-]+/', '-', strtolower($str))), '-'), '-')));
	}

	/**
	 * Filters a a string to alpha characters
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function alpha(string $str): string
	{
		return trim(preg_replace("/[^a-zA-Z]/", '', $str));
	}

	/**
	 * Filters a a string to alpha characters with dashes
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function alphaDash(string $str): string
	{
		return preg_replace('/-+/', '-', ltrim(rtrim(preg_replace('/[^a-zA-Z_-]/', '', preg_replace('/[\s-]+/', '-', $str)), '-'), '-'));
	}

	/**
	 * Filters a a string to alpha-numeric characters
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function alphaNum(string $str): string
	{
		return preg_replace('/-+/', '-',  ltrim(rtrim(trim(preg_replace("/[^a-zA-Z0-9]/", '', $str)), '-'), '-'));
	}

	/**
	 * Filters a a string to alpha-numeric characters with dashes
	 *
	 * @access public
	 * @param  string $string The input string
	 * @return string
	 */
	public static function alphaNumDash(string $str): string
	{
		return preg_replace('/-+/', '-', ltrim(rtrim(preg_replace('/[^a-zA-Z0-9_-]/', '', preg_replace('/[\s-]+/', '-', $str)), '-'), '-'));
	}

	/**
	 * Escapes text for entry into mySQL
	 *
	 * @access public
	 * @param  string $str The input string
	 * @return string
	 */
	public static function mysqlEncode(string $str): string
	{
		return htmlentities($str, ENT_QUOTES, 'UTF-8', false);
	}

	/**
	 * Un-escapes text from entry into mySQL
	 *
	 * @access public
	 * @param  string $str The input string
	 * @return string
	 */
	public static function mysqlDecode(string $str): string
	{
		return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Create a boolean value from a mixed variable
	 *
	 * @access public
	 * @param  mixed $mixedVar Mixed variable to convert
	 * @return bool
	 */
	public static function bool($mixedVar): bool
	{
		if (is_bool($mixedVar)) 
		{
			return boolval($mixedVar);
		}
		else if (is_integer($mixedVar))
		{
			return boolval($mixedVar);
		}
		else if (is_float($mixedVar))
		{
			return floatval($mixedVar) > 0;
		}
		else if (is_numeric($mixedVar))
		{
			return intval($mixedVar) > 0;
		}
		else if (is_string($mixedVar))
		{
			$mixedVar = trim(strtolower($mixedVar));
			
			if ($mixedVar === 'yes' || $mixedVar === 'on' || $mixedVar === 'true')
			{
				return true;
			}
			else if ($mixedVar === 'no' || $mixedVar === 'off' || $mixedVar === 'false')
			{
				return false;
			}
		}

		return boolval($mixedVar);
	}

	/**
	 * Remove the query string and santize a uri
	 *
	 * @access public
	 * @param  string $str REQUEST_URI
	 * @return string
	 */
	public static function queryFilterUri(string $uri): string
	{
		return ltrim(rtrim(Str::getBeforeFirstChar($uri, '?'), '/'), '/');
	}
}
