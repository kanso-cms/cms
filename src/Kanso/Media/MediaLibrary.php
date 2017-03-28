<?php

namespace Kanso\Media;

/**
 * Media Library
 *
 * The Media Library class handles media uploads
 *
 */ 
class MediaLibrary
{	

	/**
     * @var \Kanso\Kanso::getInstance()->Database()->Builder()
     */
    private $SQL;

   	/**
     * Constructor
     *
     */
	public function __construct()
	{
		$this->SQL = \Kanso\Kanso::getInstance()->Database()->Builder();
		
	}

	/**
	 * Get an attachment by id
	 *
	 * @param integer    $id    id of file in the database
	 * @return \Kanso\Media\Attachment|false
	 *
	 */
	public function byId($id)
	{
		$row = $this->SQL->SELECT('*')->FROM('media_uploads')->WHERE('id', '=', intval($id))->ROW();
		if ($row) {
			return new \Kanso\Media\Attachment($row);
		}
		return false;
	}

	/**
	 * Get an attachment by key
	 *
	 * @param string    $key      The key from the database table (column)    
	 * @param integer   $value    The value for the key
	 * @return \Kanso\Media\Attachment|false
	 *
	 */
	public function byKey($key, $value)
	{
		$row = $this->SQL->SELECT('*')->FROM('media_uploads')->WHERE($key, '=', $value)->ROW();
		if ($row) {
			return new \Kanso\Media\Attachment($row);
		}
		return false;
	}

	/**
     * Create and save a single uploaded file
     *
     * @param  array      $FILE        Single file item from the $_FILES super global
     * @param  string     $title       Title for the attachment
     * @param  string     $alt         Alt text for the attachment
     * @param  string     $rel         Rel text for the attachment
     * @param  boolean    $validate    Allow only valid file types 
     * @return \Kanso\Media\Attachment|CORRUPT_FILE|UNSUPPORTED_TYPE|false
     *
     */
	public function upload($FILE, $title = '', $alt = '', $rel = 'attachment', $validate = true)
	{
		$attachment = new \Kanso\Media\Attachment;
		$upload     = $attachment->upload($FILE, $title, $alt, $rel, $validate);
		if ($upload === true) return $attachment;
		return $upload;
	}
   		
}