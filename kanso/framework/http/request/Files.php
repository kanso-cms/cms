<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\request;

use kanso\framework\common\ArrayAccessTrait;

/**
 * Files manager
 *
 * @author Joe J. Howard
 */
class Files
{
	use ArrayAccessTrait;

	/**
     * Constructor
     *
     * @access public
     * @param  array  $parameters $_FILES upload (optional) (default [])
     */
	public function __construct(array $parameters = [])
	{
		$parameters = empty($parameters) ? $_FILES : $parameters;

		$this->data = $this->convertToUploadedFileObjects($parameters);
	}

	/**
	 * Creates a consistent uploaded file array.
	 *
	 * @param  array $file File info
	 * @return array
	 */
	protected function createUploadedFile(array $file): array
	{
		return
		[
			'tmp_name' => $file['tmp_name'],
			'name'     => $file['name'],
			'size'     => $file['size'],
			'type'     => $file['type'],
			'error'    => $file['error'],

		];
	}

	/**
	 * Normalizes a multi file upload array to a more manageable format.
	 *
	 * @param  array $files File upload array
	 * @return array
	 */
	protected function normalizeMultiUpload(array $files): array
	{
		$normalized = [];

		$keys = array_keys($files);

		$count = count($files['name']);

		for($i = 0; $i < $count; $i++)
		{
			foreach($keys as $key)
			{
				$normalized[$i][$key] = $files[$key][$i];
			}
		}

		return $normalized;
	}

	/**
	 * Converts the $_FILES array to an array of consistent arrays
	 *
	 * @param  array $files File upload array
	 * @return array
	 */
	protected function convertToUploadedFileObjects(array $files): array
	{
		$uploadedFiles = [];

		foreach($files as $name => $file)
		{
			if(is_array($file['name']))
			{
				foreach($this->normalizeMultiUpload($file) as $file)
				{
					$uploadedFiles[$name][] = $this->createUploadedFile($file);
				}
			}
			else
			{
				$uploadedFiles[$name] = $this->createUploadedFile($file);
			}
		}

		return $uploadedFiles;
	}

	/**
	 * {@inheritdoc}
	 */
	public function add(string $name, $value)
	{
		if(is_array($value))
		{
			$value = $this->createUploadedFile($value);
		}

		$this->data[$name] = $value;
	}
}