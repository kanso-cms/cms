<?php

namespace Kanso\Utility;

/**
 * Encode/Decode Ini files, done right
 * 
 */
class Ini
{

	/**
	 * Convert an array to .ini syntax
	 *
	 * @param  array      $array
	 * @return string
	*/
	public static function encode($array)
	{

		$ini = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $val = '';
                foreach ($value as $v) {
                    $val .= "$v,";
                }
                $val = rtrim($val, ',');
                $ini .= "$key = [$val]\n";
            }
            else if (is_string($value)) {
                $ini .= "$key = '$value'\n";
            }
            else if (is_numeric($value)) {
                $ini .= "$key = $value\n";
            }
            else if (is_bool($value)) {
                if ($value == true) {
                    $ini .= "$key = 'true'\n";
                }
                else {
                    $ini .= "$key = 'false'\n";
                }
            }
            else if (is_null($value)) {
            	$ini .= "$key = 'NULL'\n";
            }
        }
        return $ini;

	}

	/**
	 * Convert an .ini string (file contents) to a php array
	 *
	 * @param  string      $str
	 * @return array
	*/
	public static function decode($str)
	{
		$data = [];
		$ini  = array_map('trim', explode("\n", $str));
		foreach ($ini as $line) {

			# Remove comments
			if (strpos($line, ';') !== false || empty(trim($line))) {
				continue;
			}

			# Values must contain an '=' sign
			else if (strpos($line, '=') !== false) {
				
				# Split the line into key, value
				$line = array_map('trim', explode('=', $line));

				# Make sure line has both a key and value
				if (count($line) !== 2) continue;

				# Set the key/value
				$key   = $line[0];
				$value = $line[1];
				
				# Convert value to a valid PHP variable
				$data[$key] = self::stringToPHP($value);

			}
		}
		return $data;

	}

	/**
	 * Convert an .ini string value to a php variable
	 *
	 * @param  string      $value
	 * @return mixed
	*/
	private static function stringToPHP($value)
	{

		# Remove quotes
		$value = str_replace('"', '', $value);
		$value = str_replace('\'', '', $value);
		$value = trim($value);

		if (strpos($value, '[') !== false) {
            $value = str_replace('[', '', $value);
            $value = str_replace(']', '', $value);
			$data  = [];
			$value = array_map('trim', explode(',', $value));
			foreach ($value as $val) {
				if (empty($val)) continue;
				$data[] = self::stringToPHP($val);
			}
			return $data;
		}
		else if (is_numeric($value)) {
			return (int) $value;
		}
		else if ($value === 'NULL' || $value === 'null') {
			return NULL;
		}
		else if ($value === 'true') {
			return true;
		}
		else if ($value === 'false') {
			return false;
		}
		else if ($value === '[]' || $value === '[ ]') {
			return [];
		}
		else {
			return $value;
		}
	}
	
}