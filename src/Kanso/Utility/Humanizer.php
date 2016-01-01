<?php

namespace Kanso\Utility;

/**
 * Convert various values to human friendly format
 *
 */
class Humanizer
{

	/**
	 * Returns a human friendly file size.
	 *
	 * @param   int      $size    File size in bytes
	 * @param   boolean  $binary  True to use binary suffixes and false to use decimal suffixes
	 * @return  string
	 */
	public static function fileSize($size, $binary = true)
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

			$e = floor(log($size, $base));

			return round($size / pow($base, $e), 2) . ' ' . $terms[$e];
		}
		else
		{
			return '0 byte';
		}
	}

	/**
	 * Returns a time ago from a timestamp
	 *
	 * @param   int      $time    A valid UNIX timestamp
	 * @param   string
	 */
	public static function timeAgo($time)
	{

		$timeStamp = self::isValidTimeStamp($time) ? $time : strtotime($time);

		$time = time() - $timeStamp;
	    
	    $time = ($time < 1 ) ? 1 : $time;

	    $tokens = array (
	        31536000 => 'year',
	        2592000 => 'month',
	        604800 => 'week',
	        86400 => 'day',
	        3600 => 'hour',
	        60 => 'minute',
	        1 => 'second'
	    );

	    foreach ($tokens as $unit => $text) {
	        if ($time < $unit) continue;
	        $numberOfUnits = floor($time / $unit);
	        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
	    }
	}

	/**
	 * Validate that a variable is a valid UNIX timestamp
	 *
	 * @param   mixed      $timestamp
	 * @param   boolean
	 */
	private static function isValidTimeStamp($timestamp)
	{
	    return ( is_numeric($timestamp) && (int)$timestamp == $timestamp );
	}

	/**
	 * Convert a directory into a url
	 *
	 * @param   string      $dirname
	 * @param   string
	 */
	public static function dirToURL($dirname)
	{
		$env = \Kanso\Kanso::getInstance()->Environment();
		return str_replace($env['DOCUMENT_ROOT'], $env['HTTP_HOST'], $dirname);
	}

}