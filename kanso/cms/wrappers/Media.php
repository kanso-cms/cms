<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use kanso\cms\wrappers\Wrapper;
use kanso\framework\database\query\Builder;
use kanso\framework\utility\Str;

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
     * Array of image fle extensions
     *
     * @var array
     */
    private $imgExtensions =
    [
        'png',
        'jpg',
        'jpeg',
        'gif',
    ];

    /**
     * Override inherited constructor
     * 
     * @access public
     * @param  \kanso\framework\database\query\Builder $SQL            SQL query builder
     * @param  array                                   $thumbnailSizes Assoc array of thumbnail sizes
     * @param  array                                   $data           Array row from Database

     */
    public function __construct(Builder $SQL, array $thumbnailSizes = ['small'  => 400,'medium' => 800,'large' => 1200] , array $data = [])
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
                $this->data['id'] = intval($this->SQL->connectionHandler()->lastInsertId());
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
     * Checks if this is an image
     *
     * @access public
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array($this->ext(), $this->imgExtensions);
    }

    /**
     * Returns the img width in px if this is an image
     *
     * @access public
     * @param  string $size Image size suffix (optional) (default 'original')
     * @return int
     */
    public function width(string $size = 'original'): int
    {
        $width = 0;

        if ($this->ext() === 'svg')
        {
            if (file_exists($this->imgSizePath()))
            {
                $xml = file_get_contents($this->imgSizePath());

                preg_match('/(width\=\")(.*?)(\")/', $xml, $_width);

                if ($_width && isset($_width[2]))
                {
                    $width = intval(str_replace('px', '', $_width[2]));
                }
            }
        }
        else if ($this->isImage())
        {
            $path = $this->imgSizePath($size);
            
            if (file_exists($path))
            {
                list($width, $height) = getimagesize($path);
            }
        }

        return intval($width);
    }

    /**
     * Returns the img height in px if this is an image
     *
     * @access public
     * @param  string $size Image size suffix (optional) (default 'original')
     * @return int
     */
    public function height(string $size = 'original'): int
    {
        $height = 0;

        if ($this->ext() === 'svg')
        {
            if (file_exists($this->imgSizePath()))
            {
                $xml = file_get_contents($this->imgSizePath());

                preg_match('/(height\=\")(.*?)(\")/', $xml, $_height);
                            
                if ($_height && isset($_height[2]))
                {
                    $height = intval(str_replace('px', '', $_height[2]));
                }
            }
        }

        if ($this->isImage())
        {
            $path = $this->imgSizePath($size);

            if (file_exists($path))
            {
                list($width, $height) = getimagesize($path);
            }
        }

        return intval($height);
    }

    /**
     * Returns the img width if this is an image
     *
     * @access public
     * @return int
     */
    public function ext(): string
    {
        return Str::getAfterLastChar($this->data['url'], '.');
    }

	/**
     * If the current file is an image, return the image url of a different size
     *
     * @access public
     * @param  string $size Image size suffix (optional) (default 'original')
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
     * @param  string $size Image size suffix (optional) (default 'original')
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