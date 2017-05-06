<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers;

use Kanso\CMS\Wrappers\Wrapper;
use Kanso\Framework\Database\Query\Builder;
use Kanso\Framework\Utility\Str;

/**
 * Media utility wrapper
 *
 * @author Joe J. Howard
 */
class Media extends Wrapper
{
    /**
     * Assoc array of thumbnail sizes
     * 
     * @var array
     */ 
    private $thumbnailSizes;

    /**
     * Override inherited constructor
     * 
     * @access public
     * @param  \Kanso\Framework\Database\Query\Builder $SQL            SQL query builder
     * @param  array                                   $data           Array row from Database
     * @param  array                                   $thumbnailSizes Assoc array of thumbnail sizes
     */
    public function __construct(Builder $SQL, array $thumbnailSizes, array $data = [])
    {
        $this->SQL = $SQL;

        $this->data = !empty($data) ? $data : [];

        $this->thumbnailSizes = $thumbnailSizes;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool
	{
        $saved = false;

        $mediaExists = $this->SQL->SELECT('*')->FROM('media_uploads')->WHERE('path', '=', $this->data['path'])->ROW();

        if ($mediaExists)
        {
            $saved = $this->SQL->UPDATE('media_uploads')->SET($this->data)->WHERE('id', '=', $mediaExists['id'])->QUERY();
        }
        else
        {
            $saved = $this->SQL->INSERT_INTO('media_uploads')->VALUES($this->data)->QUERY();

            if ($saved)
            {
                $this->data['id'] = intval($this->SQL->connection()->lastInsertId());
            }
        }

        return !$saved ? false : true;
	}

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        if (isset($this->data['id']))
        {
            foreach ($this->thumbnailSizes as $size => $values)
            {
                $path = $this->imgSizePath($size);

                if (file_exists($path))
                {
                    unlink($path);
                }
            }
            
            if (file_exists($this->data['path']))
            {
                unlink($this->data['path']);
            }

            return $this->SQL->DELETE_FROM('media_uploads')->WHERE('id', '=', $this->data['id'])->QUERY() ? true : false;
        }

        return false;
    }

	/**
     * If the current file is an image, return the image url of a different size
     *
     * @access public
     * @return string
     */
    public function imgSize($size = 'original'): string
	{
        if ($size === 'original')
        {
            return $this->data['url'];
        }

		# Get the file extension from the mime type
		$ext = Str::getAfterLastChar($this->data['url'], '.');

		# Sanitize the file name
		$name = Str::getBeforeLastChar($this->data['url'], '.');

		return $name.'_'.$size.'.'.$ext;
	}

    /**
     * If the current file is an image, return the image path of a different size
     *
     * @access public
     * @return string
     */
    public function imgSizePath($size = 'original'): string
    {
        if ($size === 'original')
        {
            return $this->data['path'];
        }

        # Get the file extension from the mime type
        $ext = Str::getAfterLastChar($this->data['path'], '.');

        # Sanitize the file name
        $name = Str::getBeforeLastChar($this->data['path'], '.');

        return $name.'_'.$size.'.'.$ext;
    }
}