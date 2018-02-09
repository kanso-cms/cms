<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\pixl\processor;

/**
 * Image processor interface
 *
 * @author Joe J. Howard
 */
interface ProcessorInterface
{
	/**
     * Load image parameters for internal use
     *
     * @access public
     * @param  string         $filename Absolute path to file
     * @throws Exception      If file is not an image
     * @return Exception|null
     */
    public function load(string $filename);

	/**
     * Get the image width in px
     *
     * @access public
     * @return int
     */
    public function width(): int;

    /**
     * Get the image height in px
     *
     * @access public
     * @return int
     */
    public function height(): int;

    /**
     * Save the new file to disk
     *
     * @access public
     * @param  string $filename     Absolute path to file
     * @param  mixed  $image_type   PHP image type constant (optional) (default NULL)
     * @param  int    $quality      Quality of image to save (optional)
     * @param  int    $permissions  File permissions to save with (optional)
     * @return mixed
     */
    public function save(string $filename, int $image_type = null, int $quality = null, int $permissions = null);

    /**
     * Resize to height
     *
     * @access public
     * @param  int  $height        Height in px
     * @param  bool $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function resizeToHeight(int $height, bool $allow_enlarge = false);

    /**
     * Resize to width
     *
     * @access public
     * @param  int                  $width         Width in px
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function resizeToWidth(int $width, bool $allow_enlarge = false);

    /**
     * Scale image by a percentage
     *
     * @param  int                  $scale         Scale percentage
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function scale(int $scale);

    /**
     * Resize image to height and width
     *
     * @param  int                  $width         Width in px
     * @param  int                  $height        Height in px
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function resize(int $width, int $height, bool $allow_enlarge = false);

    /**
     * Crop to width and height
     *
     * @param  int                  $width         Width in px
     * @param  int                  $height        Height in px
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function crop(int $width, int $height, bool $allow_enlarge = false);
}
