<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\managers;

use kanso\framework\database\query\Builder;
use kanso\framework\http\request\Environment;
use kanso\framework\pixl\Image;
use kanso\framework\pixl\processor\GD;
use kanso\framework\utility\Mime;
use kanso\framework\utility\Str;
use kanso\cms\auth\Gatekeeper;
use kanso\cms\wrappers\managers\Manager;
use kanso\cms\wrappers\providers\MediaProvider;

/**
 * Media manager
 *
 * @author Joe J. Howard
 */
class MediaManager extends Manager
{
    /**
     * Status code for corrupted or invalid file
     *
     * @var int
     */
    const CORRUPT_FILE = 100;

    /**
     * Status code for unsupported file
     *
     * @var int
     */
    const UNSUPPORTED_TYPE = 101;

    /**
     * Path to uploads
     *
     * @var string
     */
    private $uploadDir;

    /**
     * Array of accepted mime types
     *
     * @var array
     */
    private $acceptedMime;

    /**
     * Assoc array of thumbnail names and sizes
     *
     * @var array
     */
    private $thumbnailSizes;

    /**
     * Thumbnail quality for images 0 -> 100
     *
     * @var int
     */
    private $thumbnailQuality;

    /**
     * Array of image mime types
     *
     * @var array
     */
    private $imageMime =
    [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/gif',
    ];

    /**
     * Override inherited constructor
     * 
     * @access public
     * @param  \kanso\framework\database\query\Builder     $SQL          SQL query builder
     * @param  \kanso\cms\wrappers\providers\MediaProvider $provider     Provider manager
     * @param  string                                      $uploadDir    Path to upload files to
     * @param  array                                       $acceptedMime Array of accepted mime types
     */
    public function __construct(Builder $SQL, MediaProvider $provider, Environment $environment, Gatekeeper $gatekeeper, string $uploadDir, array $acceptedMime, array $thumbnailSizes, int $thumbnailQuality)
    {
        $this->SQL = $SQL;

        $this->provider = $provider;

        $this->uploadDir = $uploadDir;

        $this->acceptedMime = $acceptedMime;

        $this->environment = $environment;

        $this->gatekeeper = $gatekeeper;

        $this->thumbnailSizes = $thumbnailSizes;

        $this->thumbnailQuality = $thumbnailQuality;
    }

    /**
     * {@inheritdoc}
     */
    public function provider(): MediaProvider
    {
        return $this->provider;
    }

    /**
     * Creates a new media entry
     * 
     * @access public
     * @param  string $path  Path to file
     * @param  string $title Attachment title (optional) (default '')
     * @param  string $alt   Attachment alt text (optional) (default '')
     * @return mixed
     */
    public function create(string $path, string $title = '', string $alt = '')
    {
        $url        = str_replace($this->environment->DOCUMENT_ROOT, $this->environment->HTTP_HOST, $path);
        $dimensions = '';
        $uploaderId = 1;
        $user       = $this->gatekeeper->getUser();
        $isImage    = in_array(Mime::fromExt(Str::getAfterLastChar($path, '.')), $this->imageMime);

        if ($isImage)
        {
            list($width, $height, $type, $attr) = getimagesize($path);
            
            $dimensions = "$width x $height";
        }

        if ($user)
        {
            $uploaderId = $user->id;
        }

        return $this->provider->create([
            'url'   => $url,
            'path'  => $path,
            'title' => $title,
            'alt'   => $alt,
            'size'  => filesize($path),
            'date'  => time(),
            'uploader_id' => $uploaderId,
            'dimensions'  => $dimensions,
        ]);
    }

	/**
     * Gets a post by id
     * 
     * @access public
     * @param  int    $id Tag id
     * @return mixed
     */
	public function byId(int $id)
	{
		return $this->provider->byId($id);
	}

    /**
     * Create and save a single uploaded file
     *
     * @param  array      $file        Single file item from the $_FILES super global
     * @param  string     $title       Title for the attachment
     * @param  string     $alt         Alt text for the attachment
     * @param  bool       $validate    Allow only valid file types 
     * @return bool|CORRUPT_FILE|UNSUPPORTED_TYPE
     */
    public function upload($FILE, $title = '', $alt = '', $validate = true)
    {
        # Validate all the proper keys are set
        if (!isset($FILE['type']) || !isset($FILE['name']) || !isset($FILE['tmp_name']) || !isset($FILE['size']))
        {
            return self::CORRUPT_FILE;
        }

        # Validate the file was uploaded with PHP HTTP POST 
        if (!is_uploaded_file($FILE['tmp_name']))
        {
            return self::CORRUPT_FILE;
        }

        # Run the validation
        if ($validate === true)
        {
            # Validate this file type is supported
            if (!in_array($FILE['type'], $this->acceptedMime))
            {
                return self::UNSUPPORTED_TYPE;
            }
        }
       
        # Is the file an image?
        $isImage = in_array($FILE['type'], $this->imageMime);

        # If the file is not an image we just upload it directly
        if (!$isImage)
        {
            # Get the file extension from the mime type
            $ext = Mime::toExt($FILE['type']);

            # Sanitize the file name
            $name = Str::slug(Str::getBeforeLastChar($FILE['name'], '.')).'.'.$ext;

            # Create the destination
            $dest = $this->uniqueName($this->uploadDir.DIRECTORY_SEPARATOR.$name);

            $uploaded = move_uploaded_file($FILE['tmp_name'], $dest);

            if ($uploaded)
            {
                return $this->create($dest, $title, $alt);
            } 
        }
        else
        {
            # Grab our image processor
            $Imager = new Image($FILE['tmp_name'], new GD);

            # Get the file extension from the mime type
            $ext = Mime::toExt($FILE['type']);

            # Sanitize the file name
            $name = Str::slug(Str::getBeforeLastChar($FILE['name'], '.'));

            # Save the destination path
            $destPath = $this->uniqueName($this->uploadDir.DIRECTORY_SEPARATOR.$name.'.'.$ext);

            # Image quality
            $qual = $ext === 'png' ? ($this->thumbnailQuality/10) : $this->thumbnailQuality;

            # Loop through config sizes, resize and upload
            foreach ($this->thumbnailSizes as $suffix => $size) {
                
                # Set the destination
                $dst = $this->uniqueName($this->uploadDir.DIRECTORY_SEPARATOR.$name.'_'.$suffix.'.'.$ext);

                # If sizes are declared with width & Height - resize to those dimensions
                # otherwise just resize to width;
                if (is_array($size))
                {
                    $Imager->crop($size[0], $size[1], true);
                }
                else
                {
                    $Imager->resizeToWidth($size, true);
                }

                # Save the file
                $saved = $Imager->save($dst, false, $qual);

                if (!$saved)
                {
                    return false;
                }

            }

            # Upload the original
            $uploaded = move_uploaded_file($FILE['tmp_name'], $destPath);

            # Save to the database and update info
            if ($uploaded)
            {
                return $this->create($destPath, $title, $alt);
            }
        
        }
        return false;
    }

    /**
     * Create a unique filename
     *
     * @param   string     $path      File path
     * @return  string
     */
    private function uniqueName(string $path)
    {
        $dir   = Str::getBeforeLastChar($path, '/');
        $name  = Str::getAfterLastChar($path, '/');
        $ext   = Str::getAfterLastChar($name, '.');
        $name  = Str::getBeforeLastChar($name, '.');
        $count = 1;

        if (!file_exists($path))
        {
            return $path;
        }

        while(file_exists($path))
        {
            $path = $dir.'/'.$name.$count.'.'.$ext;
            $count++;
        }

        return $path;
    }
}
