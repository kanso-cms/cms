<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\utility;

use InvalidArgumentException;

/**
 * GD image manager
 *
 * @author Joe J. Howard
 */
class Image
{
    /**
     * Default jpg quality
     *
     * @var int
     */
    private $quality_jpg = 75;

    /**
     * Default jpg quality
     *
     * @var int
     */
    private $quality_png = 0;

    /**
     * Image source type
     *
     * @var int
     */
    private $source_type;

    /**
     * Image resource
     *
     * @var array
     */
    private $source_image;

    /**
     * Original width in px
     *
     * @var int
     */
    private $source_w;

    /**
     * Original height in px
     *
     * @var int
     */
    private $source_h;

    /**
     * Source x-axis crop position in px
     *
     * @var int
     */
    private $source_x;

    /**
     * Source y-axis crop position in px
     *
     * @var int
     */
    private $source_y;

    /**
     * Destination x-axis crop position in px
     *
     * @var int
     */
    private $dest_x = 0;

    /**
     * Destination y-axis crop position in px
     *
     * @var int
     */
    private $dest_y = 0;

    /**
     * Destination width in px
     *
     * @var int
     */
    private $dest_w;

    /**
     * Destination height in px
     *
     * @var int
     */
    private $dest_h;

    /**
     * Constructor
     *
     * @access public
     * @param string $filename Absolute path to file
     */
    public function __construct(string $filename)
    {
        $this->load($filename);
    }   

    /**
     * Get the image width in px
     *
     * @access public
     * @return int
     */
    public function width(): int
    {
        if ($this->dest_w > 0)
        {
            return $this->dest_w;
        }

        return $this->source_w;
    }

    /**
     * Get the image height in px
     *
     * @access public
     * @return int
     */
    public function height(): int
    {
        if ($this->dest_h > 0)
        {
            return $this->dest_h;
        }

        return $this->source_h;
    }

    /**
     * Save the new file to disk
     *
     * @access public
     * @param  string $filename     Absolute path to file
     * @param  mixed  $image_type   PHP image type constant (optional) (default NULL)
     * @param  int    $quality      Quality of image to save (optional)
     * @param  int    $permissions  File permissions to save with (optional)
     * @return \kanso\framework\utility\Image
     */
    public function save(string $filename, int $image_type = null, int $quality = null, int $permissions = null)
    {
        $image_type = $image_type ?: $this->source_type;

        $dest_image = imagecreatetruecolor($this->dest_w, $this->dest_h);

        switch ($image_type) {
            case IMAGETYPE_GIF:
                $background = imagecolorallocatealpha($dest_image, 255, 255, 255, 1);
                imagecolortransparent($dest_image, $background);
                imagefill($dest_image, 0, 0 , $background);
                imagesavealpha($dest_image, true);
            break;

            case IMAGETYPE_JPEG:
                $background = imagecolorallocate($dest_image, 255, 255, 255);
                imagefilledrectangle($dest_image, 0, 0, $this->dest_w, $this->dest_h, $background);
            break;

            case IMAGETYPE_PNG:
                imagealphablending($dest_image, false);
                imagesavealpha($dest_image, true);
            break;
        }

        imagecopyresampled(
            $dest_image,
            $this->source_image,
            $this->dest_x,
            $this->dest_y,
            $this->source_x,
            $this->source_y,
            $this->dest_w,
            $this->dest_h,
            $this->source_w,
            $this->source_h
        );

        switch ($image_type) {
            case IMAGETYPE_GIF:
                imagegif($dest_image, $filename);
            break;

            case IMAGETYPE_JPEG:
                if ($quality === null) {
                    $quality = $this->quality_jpg;
                }

                imagejpeg($dest_image, $filename, $quality);
            break;

            case IMAGETYPE_PNG:
                if ($quality === null) {
                    $quality = $this->quality_png;
                }

                imagepng($dest_image, $filename, $quality);
            break;
        }

        if ($permissions) {
            chmod($filename, $permissions);
        }

        return $this;
    }

    /**
     * Resize to height
     *
     * @access public
     * @param  int  $height        Height in px
     * @param  bool $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function resizeToHeight(int $height, bool $allow_enlarge = false)
    {
        $ratio = $height / $this->source_h;
        
        $width = $this->source_w * $ratio;

        $this->resize($width, $height, $allow_enlarge);

        return $this;
    }

    /**
     * Resize to width
     *
     * @access public
     * @param  int                  $width         Width in px
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function resizeToWidth(int $width, bool $allow_enlarge = false)
    {
        $ratio  = $width / $this->source_w;
        
        $height = $this->source_h * $ratio;

        $this->resize($width, $height, $allow_enlarge);

        return $this;
    }

    /**
     * Scale image by a percentage
     *
     * @param  int                  $scale         Scale percentage
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function scale(int $scale)
    {
        $width  = $this->source_w * $scale / 100;
        
        $height = $this->source_h * $scale / 100;

        $this->resize($width, $height, true);

        return $this;
    }

    /**
     * Resize image to height and width
     *
     * @param  int                  $width         Width in px
     * @param  int                  $height        Height in px
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function resize(int $width, int $height, bool $allow_enlarge = false)
    {
        if (!$allow_enlarge) {
            // if the user hasn't explicitly allowed enlarging,
            // but either of the dimensions are larger then the original,
            // then just use original dimensions - this logic may need rethinking

            if ($width > $this->source_w || $height > $this->source_h)
            {
                $width  = $this->source_w;
                $height = $this->source_h;
            }
        }

        $this->source_x = 0;
        $this->source_y = 0;

        $this->dest_w = $width;
        $this->dest_h = $height;

        $this->source_w = $this->source_w;
        $this->source_h = $this->source_h;

        return $this;
    }

    /**
     * Crop to width and height
     *
     * @param  int                  $width         Width in px
     * @param  int                  $height        Height in px
     * @param  bool                 $allow_enlarge Allow image to be enlarged ? (optional) (default FALSE)
     * @return \kanso\framework\utility\Image
     */
    public function crop(int $width, int $height, bool $allow_enlarge = false)
    {
        if (!$allow_enlarge) {
            // this logic is slightly different to resize(),
            // it will only reset dimensions to the original
            // if that particular dimenstion is larger

            if ($width > $this->source_w) {
                $width  = $this->source_w;
            }

            if ($height > $this->source_h) {
                $height = $this->source_h;
            }
        }

        $ratio_source = $this->source_w / $this->source_h;
        $ratio_dest = $width / $height;

        if ($ratio_dest < $ratio_source) {
            $this->resizeToHeight($height, $allow_enlarge);

            $excess_width = ($this->dest_w - $width) / $this->dest_w * $this->source_w;

            $this->source_w = $this->source_w - $excess_width;
            $this->source_x = $excess_width / 2;

            $this->dest_w = $width;
        } else {
            $this->resizeToWidth($width, $allow_enlarge);

            $excess_height = ($this->dest_h - $height) / $this->dest_h * $this->source_h;

            $this->source_h = $this->source_h - $excess_height;
            $this->source_y = $excess_height / 2;

            $this->dest_h = $height;
        }

        return $this;
    }

    /**
     * Load image parameters for internal use
     *
     * @access private
     * @param  string         $filename Absolute path to file
     * @throws Exception      If file is not an image
     * @return Exception|null
     */
    private function load(string $filename)
    {
        $image_info = getimagesize($filename);

        if (!$image_info)
        {
            throw new InvalidArgumentException("$filename : The provided file path is not an image.");
        }

        list (
            $this->source_w,
            $this->source_h,
            $this->source_type
        ) = $image_info;

        switch ($this->source_type)
        {
            case IMAGETYPE_GIF:
                $this->source_image = imagecreatefromgif($filename);
            break;

            case IMAGETYPE_JPEG:
                $this->source_image = imagecreatefromjpeg($filename);
            break;

            case IMAGETYPE_PNG:
                $this->source_image = imagecreatefrompng($filename);
            break;

            default:
            throw new InvalidArgumentException("$filename : The provided file path is not a supported image.");
            break;
        }
    }

   
}