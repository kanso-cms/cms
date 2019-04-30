<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\utility;

/**
 * Humanizer class.
 *
 * @author Joe J. Howard
 */
class Humanizer
{
	/**
	 * Returns a human friendly file size.
	 *
	 * @access public
	 * @param  int    $size   File size in bytes
	 * @param  bool   $binary True to use binary suffixes and false to use decimal suffixes (optional) (default FALSE)
	 * @return string
	 */
	public static function fileSize(int $size, bool $binary = false): string
	{
		if($size > 0)
		{
			if($binary === true)
			{
				$base  = 1024;
				$terms = ['byte', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
			}
			else
			{
				$base  = 1000;
				$terms = ['byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
			}

			$e = intval(floor(log($size, $base)));

			$s = round($size / pow($base, $e), 2);

			return $s . ' ' . self::pluralize($terms[$e], intval($s));
		}
		else
		{
			return '0 bytes';
		}
	}

	/**
	 * Returns a time ago from a timestamp or strtotime string.
	 *
	 * @access public
	 * @param  mixed  $time A valid UNIX timestamp or PHP valid "strtotime" parameter
	 * @return string
	 */
	public static function timeAgo($time): string
	{
		$timeStamp = self::isTimestamp($time) ? $time : strtotime($time);

		$time = time() - $timeStamp;

	    $time = ($time < 1) ? 1 : $time;

	    $tokens = [
	        31536000 => 'year',
	        2592000 => 'month',
	        604800 => 'week',
	        86400 => 'day',
	        3600 => 'hour',
	        60 => 'minute',
	        1 => 'second',
	    ];

	    foreach ($tokens as $unit => $text)
	    {
	        if ($time < $unit)
	        {
	        	continue;
	        }

	        $numberOfUnits = floor($time / $unit);

	        return $numberOfUnits . ' ' . $text . (($numberOfUnits>1)?'s':'');
	    }

	    return '';
	}

	/**
	 * Returns a time elapsed.
	 *
	 * @access public
	 * @param  mixed  $time A valid UNIX timestamp or PHP valid "strtotime" parameter
	 * @return string
	 */
	public static function timeElapsed($time): string
	{
		$timeStamp = self::isTimestamp($time) ? $time : strtotime($time);

	    $time = ($time < 1) ? 1 : $time;

	    $tokens =
	    [
	        31536000 => 'year',
	        2592000 => 'month',
	        604800 => 'week',
	        86400 => 'day',
	        3600 => 'hour',
	        60 => 'minute',
	        1 => 'second',
	    ];

	    foreach ($tokens as $unit => $text)
	    {
	        if ($time < $unit)
	        {
	        	continue;
	        }

	        $numberOfUnits = floor($time / $unit);

	        return $numberOfUnits . ' ' . $text . (($numberOfUnits>1)?'s':'');
	    }

	    return '';
	}

	/**
	 * Pluralize a word.
	 *
	 * @access  public
	 * @param  string $word  The input word
	 * @param  int    $count The amount of items (optional) (default 2)
	 * @return string
	 */
	public static function pluralize(string $word, int $count = 2): string
	{
	    return Pluralize::convert($word, $count);
	}

	/**
	 * Validate that a variable is a valid UNIX timestamp.
	 *
	 * @access  private
	 * @param  mixed $timestamp A valid UNIX timestamp or PHP valid "strtotime" parameter
	 * @return bool
	 */
	private static function isTimestamp($timestamp): bool
	{
	    return (is_numeric($timestamp) && intval($timestamp) == $timestamp);
	}
}
