<?php

namespace Kanso\utility;

/**
 * Kanso Images
 *
 * This class provides a wrapper to resize/crop/enlarge/save images
 *
 */
class Images
{

    /**
     *
     * @var int
     */
    public $quality_jpg = 75;

    /**
     *
     * @var int
     */
    public $quality_png = 0;

    /**
     *
     * @var mixed
     */
    public $source_type;

     /**
     *
     * @var mixed
     */
    protected $source_image;

    /**
     *
     * @var int
     */
    protected $original_w;

    /**
     *
     * @var int
     */
    protected $original_h;

    /**
     *
     * @var int
     */
    protected $dest_x = 0;

    /**
     *
     * @var int
     */
    protected $dest_y = 0;

    /**
     *
     * @var int
     */
    protected $source_x;

    /**
     *
     * @var int
     */
    protected $source_y;

    /**
     *
     * @var int
     */
    protected $dest_w;

    /**
     *
     * @var int
     */
    protected $dest_h;

    /**
     *
     * @var int
     */
    protected $source_w;

    /**
     *
     * @var int
     */
    protected $source_h;

    /**
     *
     * Constructor
     * @param string $filename    Absolute path to file
     */
    public function __construct($filename)
    {
        $this->load($filename);
    }   

    /**
     * Load paramaters for use 
     * @param  string $filename    Absolute path to file
     * @return Exception|null
     */
    protected function load($filename)
    {
        $image_info = getimagesize($filename);

        if (!$image_info) {
            throw new \Exception('Could not read ' . $filename);
        }

        $this->setMemorySize($image_info);

        list (
            $this->original_w,
            $this->original_h,
            $this->source_type
        ) = $image_info;

        switch ($this->source_type) {
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
                throw new \Exception('Unsupported image type');
            break;
        }
    }

    /**
     * Set the memory size for image processing
     *
     * @param  array $image_info    image info
     */
    protected function setMemorySize($image_info) 
    {

        $user_limit     = 512000000;
        $real_limit     = $this->memoryLimitBytes();
        $needed         = 0;
        $width          = 0;
        $height         = 0;
 
        # getting the image width and height
        list($width, $height) = $image_info;

        $pixels  = (($width * $height) * 4) * 1.5;
 
        # calculating the needed memory
        $needed = $pixels + memory_get_usage(true);
 
        # We don't want to allocate an extremely large amount of memory
        # so its a good practice to define a limit 
        if ($needed > $user_limit) $needed = $user_limit;

        # We dont need to change the memory limit
        # if there is already enough set
        if ($needed < $real_limit) return;

        # Convert needed bytes to MB
        $needed = ceil($this->bytesToMB($needed));
        
        # Update ini default value
        ini_set('memory_limit',$needed.'M');
    }

    /**
     * Get bytes from INI
     *
     * @param  array $val value in GB/MB/KB
     */
    public function memoryLimitBytes() {

        $memory_limit = ini_get('memory_limit');
        $val          = trim($memory_limit);
        $last         = strtolower($val[strlen($val)-1]);
        $digits       = preg_replace('/[^0-9]/', '', $val);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                return $digits*pow(1024,3);
            case 'm':
                return $digits*pow(1024,2);
            case 'k':
                return $digits*1024;
        }
        return $digits;
    }

    /**
     * Convert bytes to MB
     *
     * @param  string  $bytes  Value in bytes
     * @return string  converted to MB
     */
    public function bytesToMB($bytes) 
    {
        return number_format($bytes/1048576, 2);  
    }

    /**
     * Save the new file to disk
     *
     * @param  string $filename         Absolute path to file
     * @param  string $image_type       Type of image to save (optional)
     * @param  int    $quality          Quality of image to save (optional)
     * @param  int    $permissions      File permissions to save with (optional)
     * @return Exception|null 
     */
    public function save($filename, $image_type = null, $quality = null, $permissions = null)
    {
        $image_type = $image_type ?: $this->source_type;

        $dest_image = imagecreatetruecolor($this->getDestWidth(), $this->getDestHeight());

        switch ($image_type) {
            case IMAGETYPE_GIF:
                $background = imagecolorallocatealpha($dest_image, 255, 255, 255, 1);
                imagecolortransparent($dest_image, $background);
                imagefill($dest_image, 0, 0 , $background);
                imagesavealpha($dest_image, true);
            break;

            case IMAGETYPE_JPEG:
                $background = imagecolorallocate($dest_image, 255, 255, 255);
                imagefilledrectangle($dest_image, 0, 0, $this->getDestWidth(), $this->getDestHeight(), $background);
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
            $this->getDestWidth(),
            $this->getDestHeight(),
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
     * @param  int    $height
     * @param  bool   $allow_enlarge (optional)
     * @return Kanso\Helper\Images
     */
    public function resizeToHeight($height, $allow_enlarge = false)
    {
        $ratio = $height / $this->getSourceHeight();
        $width = $this->getSourceWidth() * $ratio;

        $this->resize($width, $height, $allow_enlarge);

        return $this;
    }

    /**
     * Resize to width
     *
     * @param  int    $width
     * @param  bool   $allow_enlarge (optional)
     * @return Kanso\Helper\Images
     */
    public function resizeToWidth($width, $allow_enlarge = false)
    {
        $ratio  = $width / $this->getSourceWidth();
        $height = $this->getSourceHeight() * $ratio;

        $this->resize($width, $height, $allow_enlarge);

        return $this;
    }

    /**
     * Scale image 
     *
     * @param  int    $scale
     * @return Kanso\Helper\Images
     */
    public function scale($scale)
    {
        $width  = $this->getSourceWidth() * $scale / 100;
        $height = $this->getSourceHeight() * $scale / 100;

        $this->resize($width, $height, true);

        return $this;
    }

    /**
     * Resize to absolute
     *
     * @param  int    $width
     * @param  int    $height
     * @param  bool   $allow_enlarge (optional)
     * @return Kanso\Helper\Images
     */
    public function resize($width, $height, $allow_enlarge = false)
    {
        if (!$allow_enlarge) {
            // if the user hasn't explicitly allowed enlarging,
            // but either of the dimensions are larger then the original,
            // then just use original dimensions - this logic may need rethinking

            if ($width > $this->getSourceWidth() || $height > $this->getSourceHeight()) {
                $width  = $this->getSourceWidth();
                $height = $this->getSourceHeight();
            }
        }

        $this->source_x = 0;
        $this->source_y = 0;

        $this->dest_w = $width;
        $this->dest_h = $height;

        $this->source_w = $this->getSourceWidth();
        $this->source_h = $this->getSourceHeight();

        return $this;
    }

    /**
     * Crop to size
     *
     * @param  int    $width
     * @param  int    $height
     * @param  bool   $allow_enlarge (optional)
     * @return Kanso\Helper\Images
     */
    public function crop($width, $height, $allow_enlarge = false)
    {
        if (!$allow_enlarge) {
            // this logic is slightly different to resize(),
            // it will only reset dimensions to the original
            // if that particular dimenstion is larger

            if ($width > $this->getSourceWidth()) {
                $width  = $this->getSourceWidth();
            }

            if ($height > $this->getSourceHeight()) {
                $height = $this->getSourceHeight();
            }
        }

        $ratio_source = $this->getSourceWidth() / $this->getSourceHeight();
        $ratio_dest = $width / $height;

        if ($ratio_dest < $ratio_source) {
            $this->resizeToHeight($height, $allow_enlarge);

            $excess_width = ($this->getDestWidth() - $width) / $this->getDestWidth() * $this->getSourceWidth();

            $this->source_w = $this->getSourceWidth() - $excess_width;
            $this->source_x = $excess_width / 2;

            $this->dest_w = $width;
        } else {
            $this->resizeToWidth($width, $allow_enlarge);

            $excess_height = ($this->getDestHeight() - $height) / $this->getDestHeight() * $this->getSourceHeight();

            $this->source_h = $this->getSourceHeight() - $excess_height;
            $this->source_y = $excess_height / 2;

            $this->dest_h = $height;
        }

        return $this;
    }

    public function getSourceWidth()
    {
        return $this->original_w;
    }

    public function getSourceHeight()
    {
        return $this->original_h;
    }

    public function getDestWidth()
    {
        return $this->dest_w;
    }

    public function getDestHeight()
    {
        return $this->dest_h;
    }

}