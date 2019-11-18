<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\pixl\processor;

use RuntimeException;

/**
 * GD image manager.
 *
 * @author Joe J. Howard
 */
class GD implements ProcessorInterface
{
    /**
     * Default jpg quality.
     *
     * @var int
     */
    private $quality_jpg = 75;

    /**
     * Default jpg quality.
     *
     * @var int
     */
    private $quality_png = 0;

    /**
     * Image source type.
     *
     * @var int|null
     */
    private $source_type;

    /**
     * Image source resource.
     *
     * @var resource|null
     */
    private $source_image;

    /**
     * Image destination resource.
     *
     * @var resource|null
     */
    private $dest_image;

    /**
     * Original width in px.
     *
     * @var int|null
     */
    private $source_w;

    /**
     * Original height in px.
     *
     * @var int|null
     */
    private $source_h;

    /**
     * Source x-axis crop position in px.
     *
     * @var int|null
     */
    private $source_x;

    /**
     * Source y-axis crop position in px.
     *
     * @var int|null
     */
    private $source_y;

    /**
     * Destination x-axis crop position in px.
     *
     * @var int
     */
    private $dest_x = 0;

    /**
     * Destination y-axis crop position in px.
     *
     * @var int
     */
    private $dest_y = 0;

    /**
     * Destination width in px.
     *
     * @var int|null
     */
    private $dest_w;

    /**
     * Destination height in px.
     *
     * @var int|null
     */
    private $dest_h;

    /**
     * Constructor.
     *
     * @param string|null $filename     Absolute path to file (optional) (default null)
     * @param int|null    $imageQuality Default image quality to use (optional) (default null)
     */
    public function __construct(string $filename = null, int $imageQuality = null)
    {
        if ($filename)
        {
            $this->load($filename);
        }

        if ($imageQuality)
        {
            $this->quality_png = $imageQuality;

            $this->quality_jpg = $imageQuality === 0 ? 100 : 100 - ($imageQuality * 10 - 10);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $filename): void
    {
        $image_info = getimagesize($filename);

        if (!$image_info)
        {
            throw new RuntimeException("$filename : The provided file path is not an image.");
        }

         [
            $this->source_w,
            $this->source_h,
            $this->source_type
        ] = $image_info;

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
                throw new RuntimeException("$filename : The provided file path is not a supported image.");
            break;
        }
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function save(string $filename, int $image_type = null, int $quality = null, int $permissions = null)
    {
        $addedbg    = true;
        $image_type = $image_type ?: $this->source_type;

        if (!$this->dest_image)
        {
            $addedbg          = false;
            $this->dest_image = imagecreatetruecolor($this->width(), $this->height());
        }

        if (!$addedbg)
        {
            switch ($this->source_type)
            {
                case IMAGETYPE_GIF:
                    $background = imagecolorallocatealpha($this->dest_image, 255, 255, 255, 1);
                    imagecolortransparent($this->dest_image, $background);
                    imagefill($this->dest_image, 0, 0, $background);
                    imagesavealpha($this->dest_image, true);
                break;

                case IMAGETYPE_JPEG:
                    $background = imagecolorallocate($this->dest_image, 255, 255, 255);
                    imagefilledrectangle($this->dest_image, 0, 0, $this->width(), $this->height(), $background);
                break;

                case IMAGETYPE_PNG:
                    imagealphablending($this->dest_image, false);
                    imagesavealpha($this->dest_image, true);
                break;
            }
        }

        imagecopyresampled(
            $this->dest_image,
            $this->source_image,
            $this->dest_x,
            $this->dest_y,
            $this->source_x,
            $this->source_y,
            $this->width(),
            $this->height(),
            $this->source_w,
            $this->source_h
        );

        switch ($image_type)
        {
            case IMAGETYPE_GIF:
                imagegif($this->dest_image, $filename);
            break;

            case IMAGETYPE_JPEG:
                if ($quality === null)
                {
                    $quality = $this->quality_jpg;
                }

                imagejpeg($this->dest_image, $filename, $quality);
            break;

            case IMAGETYPE_PNG:
                if ($quality === null)
                {
                    $quality = $this->quality_png;
                }

                imagepng($this->dest_image, $filename, $quality);
            break;
        }

        if ($permissions)
        {
            chmod($filename, $permissions);
        }

        $this->reset();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resizeToHeight(int $height, bool $allow_enlarge = false)
    {
        $ratio  = $this->source_h / $height;

        $width = $ratio === 0 ? $this->source_w : $this->source_w / $ratio;

        $this->resize($width, $height, $allow_enlarge);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resizeToWidth(int $width, bool $allow_enlarge = false)
    {
        $ratio  = $this->source_w / $width;

        $height = $ratio === 0 ?  : $this->source_h / $ratio;

        $this->resize($width, $height, $allow_enlarge);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function scale(int $scale)
    {
        $width  = $this->source_w * $scale / 100;

        $height = $this->source_h * $scale / 100;

        $this->resize($width, $height, true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resize(int $width, int $height, bool $allow_enlarge = false)
    {
        if (!$allow_enlarge)
        {
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
     * {@inheritdoc}
     */
    public function crop(int $max_width, int $max_height, bool $allow_enlarge = false)
    {
        // If the origional image is already a square
        // And the dest is a square
        // Simply resize to width
        if ($this->source_w === $this->source_h && $max_width === $max_height)
        {
            $this->resizeToWidth($max_width);

            return $this;
        }

        $new_width  = $this->source_h * $max_width / $max_height;
        $new_height = $this->source_w * $max_height / $max_width;

        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if($new_width > $this->source_w)
        {
            //cut point by height
            $h_point = (($this->source_h - $new_height) / 2);

            $this->dest_x = 0;

            $this->dest_y = 0;

            $this->source_x = 0;

            $this->source_y = $h_point;

            $this->dest_w = $max_width;

            $this->dest_h = $max_height;

            $this->source_h = $new_height;
        }
        else
        {
            //cut point by width
            $w_point = (($this->source_w - $new_width) / 2);

            $this->dest_x = 0;

            $this->dest_y = 0;

            $this->source_x = $w_point;

            $this->source_y = 0;

            $this->dest_w = $max_width;

            $this->dest_h = $max_height;

            $this->source_w = $new_width;

        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addBackground(int $red, int $green, int $blue)
    {
        if (!$this->dest_image)
        {
            $this->dest_image = imagecreatetruecolor($this->width(), $this->height());
        }

        switch ($this->source_type)
        {
            case IMAGETYPE_GIF:
                $background = imagecolorallocate($this->dest_image, $red, $green, $blue);
                imagefill($this->dest_image, 0, 0, $background);
            break;

            case IMAGETYPE_JPEG:
                $background = imagecolorallocate($this->dest_image, $red, $green, $blue);
                imagefill($this->dest_image, 0, 0, $background);
            break;

            case IMAGETYPE_PNG:
                $background = imagecolorallocate($this->dest_image, $red, $green, $blue);
                imagefill($this->dest_image, 0, 0, $background);
            break;

            default:
                throw new RuntimeException('Error adding background to image. No image loaded in Pixl.');
            break;
        }

        return $this;
    }

    /**
     * Reset defaults after saving.
     */
    private function reset(): void
    {
        $this->source_type  = null;
        $this->source_image = null;
        $this->dest_image   = null;
        $this->source_w     = null;
        $this->source_h     = null;
        $this->source_x     = null;
        $this->source_y     = null;
        $this->dest_x       = 0;
        $this->dest_y       = 0;
        $this->dest_w       = null;
        $this->dest_h       = null;
    }
}
