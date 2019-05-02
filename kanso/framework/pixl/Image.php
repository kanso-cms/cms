<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\pixl;

use kanso\framework\pixl\processor\ProcessorInterface;
use RuntimeException;

/**
 * Image manager.
 *
 * @author Joe J. Howard
 */
class Image
{
    /**
     * Path to image file.
     *
     * @var string
     */
    private $image;

    /**
     * Processor instance.
     *
     * @var \kanso\framework\pixl\processor\ProcessorInterface
     */
    private $processor;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\pixl\processor\ProcessorInterface $processor Image processor implementation
     * @param string                                             $image     Absolute path to file (optional) (default '')
     */
    public function __construct(ProcessorInterface $processor, string $image = '')
    {
        $this->processor = $processor;

        if (!empty($image))
        {
            $this->loadImage($image);
        }
    }

    /**
     * Load an image file into the processor.
     *
     * @access public
     * @param  string           $image Absolute path to file (optional) (default '')
     * @throws RuntimeException If image file doesn't exist
     */
    public function loadImage(string $image)
    {
        if (!file_exists($image))
        {
            throw new RuntimeException(vsprintf('The image [ %s ] does not exist.', [$image]));
        }

        $this->image = $image;

        $this->processor->load($this->image);
    }

    /**
     * Get the image width in px.
     *
     * @access public
     * @return int
     */
    public function width(): int
    {
        return $this->processor->width();
    }

    /**
     * Get the image height in px.
     *
     * @access public
     * @return int
     */
    public function height(): int
    {
        return $this->processor->height();
    }

    /**
     * Save the new file to disk.
     *
     * @access public
     * @param  string|null $image       Absolute path to file (optional) (default NULL)
     * @param  int|null    $image_type  PHP image type constant (optional) (default NULL)
     * @param  int|null    $quality     Quality of image to save (optional)
     * @param  int|null    $permissions File permissions to save with (optional)
     * @return mixed
     */
    public function save(string $image = null, int $image_type = null, int $quality = null, int $permissions = null)
    {
        $image = $image ?? $this->image;

        if (file_exists($image))
        {
            if(!is_writable($image))
            {
                throw new RuntimeException(vsprintf('The file [ %s ] isn\'t writable.', [$image]));
            }
        }
        else
        {
            $pathInfo = pathinfo($image);

            if(!is_writable($pathInfo['dirname']))
            {
                throw new RuntimeException(vsprintf('The directory [ %s ] isn\'t writable.', [$pathInfo['dirname']]));
            }
        }

        return $this->processor->save($image, $image_type, $quality, $permissions);
    }

    /**
     * Resize to height.
     *
     * @access public
     * @param  int                         $height        Height in px
     * @param  bool                        $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\pixl\Image
     */
    public function resizeToHeight(int $height, bool $allow_enlarge = false): Image
    {
        $this->processor->resizeToHeight($height, $allow_enlarge);

        return $this;
    }

    /**
     * Resize to width.
     *
     * @access public
     * @param  int                         $width         Width in px
     * @param  bool                        $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\pixl\Image
     */
    public function resizeToWidth(int $width, bool $allow_enlarge = false): Image
    {
        $this->processor->resizeToHeight($width, $allow_enlarge);

        return $this;
    }

    /**
     * Scale image by a percentage.
     *
     * @param  int                         $scale Scale percentage
     * @return \kanso\framework\pixl\Image
     */
    public function scale(int $scale): Image
    {
        $this->processor->scale($scale);

        return $this;
    }

    /**
     * Resize image to height and width.
     *
     * @param  int                         $width         Width in px
     * @param  int                         $height        Height in px
     * @param  bool                        $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\pixl\Image
     */
    public function resize(int $width, int $height, bool $allow_enlarge = false): Image
    {
        $this->processor->resize($width, $height, $allow_enlarge);

        return $this;
    }

    /**
     * Crop to width and height.
     *
     * @param  int                         $width         Width in px
     * @param  int                         $height        Height in px
     * @param  bool                        $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\pixl\Image
     */
    public function crop(int $width, int $height, bool $allow_enlarge = false): Image
    {
        $this->processor->crop($width, $height, $allow_enlarge);

        return $this;
    }
}
