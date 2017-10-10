<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\providers;

use kanso\cms\wrappers\Media;
use kanso\framework\database\query\Builder;
use kanso\cms\wrappers\providers\Provider;

/**
 * Media provider
 *
 * @author Joe J. Howard
 */
class MediaProvider extends Provider
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
     * @param  \kanso\framework\database\query\Builder $SQL            SQL query builder
     * @param  array                                   $thumbnailSizes Assoc array of thumbnail sizes
     */
    public function __construct(Builder $SQL, array $thumbnailSizes)
    {
        $this->SQL = $SQL;

        $this->thumbnailSizes = $thumbnailSizes;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $row)
    {
        $media = new Media($this->SQL, $this->thumbnailSizes, $row);

        if ($media->save())
        {
            return $media;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function byId(int $id)
    {
    	return $this->byKey('id', $id, true);
    }

    /**
     * {@inheritdoc}
     */
    public function byKey(string $key, $value, bool $single = false)
    {
    	if ($single)
        {
    		$row = $this->SQL->SELECT('*')->FROM('media_uploads')->WHERE($key, '=', $value)->ROW();

    		if ($row)
            {
                return new Media($this->SQL, $this->thumbnailSizes, $row);
            }

            return null;
    	}
    	else
        {
            $media = [];

    		$rows = $this->SQL->SELECT('*')->FROM('media_uploads')->WHERE($key, '=', $value)->FIND_ALL();

    		foreach ($rows as $row)
            {
                $media[] = new Media($this->SQL, $this->thumbnailSizes, $row);
            }

            return $media;
    	}
    }
}
