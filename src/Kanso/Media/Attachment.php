<?php

namespace Kanso\Media;

/**
 * Media
 *
 * This class serves as wrapper around the media_uploads table. It
 * is used by the MediaLibrary to manage uploaded files.
 *
 */
class Attachment 
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
     * Row data from/to the database
     *
     * @var array
     */
	private $data = [];

    /**
     * @var \Kanso\Kanso::getInstance()->Database()->Builder()
     */
    private $SQL;

    /**
     * @var \Kanso\Kanso::getInstance()->FileSystem
     */
    private $filestsystem;

    /**
     * @var array
     */
    private $imageMime = [
    	'image/jpg',
		'image/jpeg',
		'image/png',
		'image/gif',
    ];

    /**
     * @var array
     */
    private $acceptedMime = [

    	// Images
    	'image/jpg',
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/tiff',
		'image/ico',
		'image/vnd.adobe.photoshop',
		'image/webp',

		// Microsoft office
		'application/pdf',
		'application/msword',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'application/vnd.ms-word.document.macroEnabled.12',
		'application/vnd.ms-excel',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'application/vnd.ms-excel.sheet.macroEnabled.12',
		'application/vnd.ms-excel.template.macroEnabled.12',
		'application/vnd.ms-excel.addin.macroEnabled.12',
		'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
		'application/vnd.ms-powerpoint',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'application/vnd.openxmlformats-officedocument.presentationml.template',
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'application/vnd.ms-powerpoint.addin.macroEnabled.12',
		'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
		'application/vnd.ms-powerpoint.presentation.macroEnabled.12',

		// Audio
    	'audio/aac',
		'application/atom+xml',
		'audio/mpeg',
		'audio/midi',
		'audio/midi',
		'audio/x-matroska',
		'audio/vnd.rn-realaudio',
		'audio/vnd.rn-realaudio',
		'audio/wav',
		'audio/x-ms-wma',
		'audio/ogg',

		// Video
		'video/avi',
		'video/x-flv',
		'video/x-matroska',
		'video/mp4',
		'video/mpeg',
		'video/3gpp',
		'video/3gpp2',
		
		// Text
		'text/plain',
		'text/xml',

    ];

	/**
     * Constructor
     * 
     * @param mixed    $rowOrId    Array from Database row or user ID
     *
     */
    public function __construct($rowOrId = NULL)
    {
        $this->SQL          = \Kanso\Kanso::getInstance()->Database()->Builder();
        $this->filestsystem = \Kanso\Kanso::getInstance()->FileSystem;

    	if (is_array($rowOrId)) {
    		$this->applyRow($rowOrId);
    	}
    	else if (is_numeric($rowOrId) || is_int($rowOrId)) {
    		$entry = $this->SQL->SELECT('*')->FROM('media_uploads')->WHERE('id', '=', intval($rowOrId))->ROW();
            $this->applyRow($entry);
    	}
    }

    /********************************************************************************
    * PUBLIC METHODS
    *******************************************************************************/

    /**
     * Create and save a single uploaded file
     *
     * @param  array      $file        Single file item from the $_FILES super global
     * @param  string     $title       Title for the attachment
     * @param  string     $alt         Alt text for the attachment
     * @param  string     $rel         Rel text for the attachment
     * @param  boolean    $validate    Allow only valid file types 
     * @return boolean|CORRUPT_FILE|UNSUPPORTED_TYPE
     *
     */
    public function upload($FILE, $title = '', $alt = '', $rel = 'attachment', $validate = true)
	{
        # Validate all the proper keys are set
        if (!isset($FILE['type']) || !isset($FILE['name']) || !isset($FILE['tmp_name']) || !isset($FILE['size'])) {
        	return self::CORRUPT_FILE;
        }

         # Validate the file was uploaded with PHP HTTP POST 
        if (!is_uploaded_file($FILE['tmp_name'])) {
        	return self::CORRUPT_FILE;
        }

        # Run the validation
        if ($validate === true) {
        	# Validate this file type is supported
        	if (!in_array($FILE['type'], $this->acceptedMime)) return self::UNSUPPORTED_TYPE;
    	
    	}
       
        # Is the file an image?
        $isImage = in_array($FILE['type'], $this->imageMime);

        # If the file is not an image we just upload it directly
        if (!$isImage) {

        	# Get the file extension from the mime type
       		$ext = \Kanso\Utility\Mime::toExt($FILE['type']);

        	# Sanitize the file name
        	$name = \Kanso\Utility\Str::slugFilter(\Kanso\Utility\Str::getBeforeLastChar($FILE['name'], '.')).'.'.$ext;

        	# Create the destination
        	$dest = $this->uniqueName(\Kanso\Kanso::getInstance()->Environment['KANSO_UPLOADS_DIR'].DIRECTORY_SEPARATOR.'Public'.DIRECTORY_SEPARATOR.$name);

        	$uploaded = move_uploaded_file($FILE['tmp_name'], $dest);

        	if ($uploaded) {

        		return $this->saveNewAttachment($dest, $title, $alt, $rel, false);
        	} 
        }
        else {

        	# Grab our image processor
        	$Imager = new \Kanso\Utility\Images($FILE['tmp_name']);

        	# Declare config sizes locally
        	$sizes = \Kanso\Kanso::getInstance()->Config['KANSO_THUMBNAILS'];

        	# Declare size suffixes for resize name
       		$suffixes  = ["small", "medium", "large"];

       		# Get the file extension from the mime type
       		$ext = \Kanso\Utility\Mime::toExt($FILE['type']);

        	# Sanitize the file name
        	$name = \Kanso\Utility\Str::slugFilter(\Kanso\Utility\Str::getBeforeLastChar($FILE['name'], '.'));

       		# Save the destination dir
       		$destDir = \Kanso\Kanso::getInstance()->Environment['KANSO_UPLOADS_DIR'].DIRECTORY_SEPARATOR.'Images';

       		# Save the destination path
       		$destPath = $this->uniqueName($destDir.DIRECTORY_SEPARATOR.$name.'.'.$ext);

       		# Image quality
       		$qual  = \Kanso\Kanso::getInstance()->Config['KANSO_IMG_QUALITY'];
       		$qual  = ($ext === 'png' ? ($qual/10) : $qual);

	        # Loop through config sizes, resize and upload
        	for ($i = 0; $i < count($suffixes); $i++) {

        		# Naming suffix
        		$suffix = $suffixes[$i];

        		# Sizing
        		$size = $sizes[$i];
	            
	            # Set the destination
	            $dst = $this->uniqueName($destDir.DIRECTORY_SEPARATOR.$name.'_'.$suffix.'.'.$ext);

	            # If sizes are declared with width & Height - resize to those dimensions
	            # otherwise just resize to width;
	            if (is_array($size)) {
	                $Imager->crop($size[0], $size[1], true);
	            }
	            else {
	                $Imager->resizeToWidth($size, true);
	            }

	            # Save the file
	            $saved = $Imager->save($dst, false, $qual);

	            if (!$saved) return false;

        	}

        	# Upload the original
        	$uploaded = move_uploaded_file($FILE['tmp_name'], $destPath);

        	# Save to the database and update info
        	if ($uploaded) return $this->saveNewAttachment($destPath, $title, $alt, $rel, true);
        
        }
        return false;
	}

    /**
     * Save the file data to the database
     *
     * @return boolean
     */
    public function save()
	{
        $saved = false;
        if (!isset($this->data['id'])) {
            $saved = $this->SQL->INSERT_INTO('media_uploads')->VALUES($this->data)->QUERY();
        }
        else {
            $saved = $this->SQL->UPDATE('media_uploads')->SET($this->data)->WHERE('id', '=', $this->data['id'])->QUERY();
        }

       	if ($saved) return true;

       	return false;
	}

	/**
     * If the current file is an image, return the image url of a different size
     *
     * @return boolean
     */
    public function imgSize($size = 'large')
	{
		# Get the file extension from the mime type
		$ext = \Kanso\Utility\Str::getAfterLastChar($this->data['url'], '.');

		# Sanitize the file name
		$name = \Kanso\Utility\Str::getBeforeLastChar($this->data['url'], '.');

		return $name.'_'.$size.'.'.$ext;
	}

	/**
     * Delete this user from the database
     *
     * @return boolean
     */
    public function delete()
	{
		# Must be an id set
		if (!isset($this->data['id'])) return false;

		# Get the file extension from the mime type
		$ext = \Kanso\Utility\Str::getAfterLastChar($this->data['path'], '.');

		# Sanitize the file name
		$name = \Kanso\Utility\Str::getBeforeLastChar($this->data['path'], '.');

		# Declare size suffixes for resize name
       	$suffixes = ["small", "medium", "large"];

       	for ($i = 0; $i < count($suffixes); $i++) {
       		$path = $name.'_'.$suffixes[$i].'.'.$ext;
       		if ($this->filestsystem->exists($path)) {
       			$this->filestsystem->delete($path);
       		}
       	}
       	if ($this->filestsystem->exists($this->data['path'])) {
       		$this->filestsystem->delete($this->data['path']);
       		return $this->SQL->DELETE_FROM('media_uploads')->WHERE('id', '=', $this->data['id'])->QUERY();
       	}
	}

    public function asArray()
    {
        return $this->data;
    }

    /********************************************************************************
	* MAGIC METHOD OVVERIDES
	*******************************************************************************/

	public function __get($key)
	{
		$key = $this->normalizeKey($key);
		if (array_key_exists($key, $this->data)) return $this->data[$key];
		
		return null;
	}

	public function __set($key, $value)
	{
		$key = $this->normalizeKey($key);
		$this->data[$key] = $value;
	}

	public function __isset($key)
	{
		$key = $this->normalizeKey($key);
		return array_key_exists($key, $this->data);
	}

	public function __unset($key)
	{
		$key = $this->normalizeKey($key);
		if (array_key_exists($key, $this->data)) {
			$this->data[$key] = '';
		}
	}
	
	/********************************************************************************
	* PRIVATE HELPERS
	*******************************************************************************/

	/**
     * Save the file data to the database from a successful upload
     *
     * @param  string      $file        Path to file
     * @param  string      $title       Title for the attachment
     * @param  string      $alt         Alt text for the attachment
     * @param  string      $rel         Rel text for the attachment
     * @param  boolean     $isImage     Is this an image
     * @return boolean
     *
     */
	private function saveNewAttachment($file, $title, $alt, $rel, $isImage)
	{
		# Get required data
		$env  = \Kanso\Kanso::getInstance()->Environment;
		$path = $file;
		$url  = str_replace($env['DOCUMENT_ROOT'], $env['HTTP_HOST'], $path);
		$dimensions = '';
		$uploaderId = 1;
		$user       = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
		if ($isImage) {
			list($width, $height, $type, $attr) = getimagesize($file);
			$dimensions = "$width x $height";
		}
		if ($user) {
			$uploaderId = $user->id;
		}

		$this->data = [
			'url'   => $url,
			'path'  => $path,
			'title' => $title,
			'alt'   => $alt,
			'rel'   => $rel,
			'size'  => $this->filestsystem->size($file),
			'date'  => time(),
			'uploader_id' => $uploaderId,
			'dimensions'  => $dimensions,
		];
		$saved = $this->save();
		if ($saved) {
			$this->data['id'] = \Kanso\Kanso::getInstance()->Database->lastInsertId();
			return true;
		}
		return false;
	}

    /**
     * Apply a database row to the user
     *
     * @param  array     $row      Array from Database row
     */
    private function applyRow($row)
    {
    	foreach ($row as $key => $value) {
    		$this->data[$this->normalizeKey($key)] = $value;
    	}
    }

    /**
     * Make sure the key is valid
     *
     * @param  string     $key      Database column
     */
    private function normalizeKey($key)
	{
		return strval($key);
	}

    /**
     * Create a unique filename
     *
     * @param   string     $path      File path
     * @return  string
     */
    private function uniqueName($path)
    {
        $dir   = dirname($path);
        $name  = \Kanso\Utility\Str::getAfterLastChar($path, '/');
        $ext   = \Kanso\Utility\Str::getAfterLastChar($name, '.');
        $name  = \Kanso\Utility\Str::getBeforeLastChar($name, '.');
        $count = 1;
        if (!$this->filestsystem->exists($path)) return $path;
        while($this->filestsystem->exists($path)) {
            $path = $dir.'/'.$name.$count.'.'.$ext;
            $count++;
        }
        return $path;

    }
}