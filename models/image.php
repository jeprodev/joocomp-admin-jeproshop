<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net/index.php?option=com_jeproshop&view=product&product_id=1
 *
 * @copyright (C)   2009 - 2011
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of,
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JeproshopImageModelImage extends  JeproshopModel{
    public $image_id;

    public $product_id;

    public $lang_id;

    public $position;

    public $cover;

    public $legend;

    public $image_format ='jpg';
    public $image_dir;
    public $source_index;

    /** @var string image folder */
    protected $folder;

    protected static $_cacheGetSize = array();

    /** @var string image path without extension */
    protected $existing_path;

    public function __construct($imageId = null, $langId = null){
        if($langId !== null){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($langId) !== false) ? $langId : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($imageId){
            $cacheKey = 'jeproshop_image_model_' . (int)$imageId . '_' . (int)$langId;
            if(!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_image') . " AS image ";
                if($langId){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON ";
                    $query .= "(image." . $db->quoteName('image_id') . " = image_lang." . $db->quoteName('image_id');
                    $query .= " AND image." . $db->quoteName('lang_id') . " = " . (int)$langId . ") ";
                }
                $query .= "WHERE image." . $db->quoteName('image_id') . " = " . (int)$imageId;

                $db->setQuery($query);
                $imageData = $db->loadObject();

                if($imageData){
                    if(!$langId && isset($this->multilang) && $this->multilang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_image_lang');
                        $query .= " WHERE image_id = " . (int)$imageId;

                        $db->setQuery($query);
                        $imageLangData = $db->loadObjectList();
                        if($imageLangData){
                            foreach ($imageLangData as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'image_id'){
                                        if(!isset($imageData->{$key}) || !is_array($imageData->{$key})){
                                            $imageData->{$key} = array();
                                        }
                                        $imageData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                        JeproshopCache::store($cacheKey, $imageData);
                    }
                }
            }else{
                $imageData = JeproshopCache::retrieve($cacheKey);
            }

            if($imageData){
                $imageData->image_id = $imageId;
                foreach($imageData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
        $this->image_dir = COM_JEPROSHOP_PRODUCT_IMAGE_DIR;
        $this->source_index = COM_JEPROSHOP_PRODUCT_IMAGE_DIR.'index.php';
    }

    /**
     * Return available images for a product
     *
     * @param integer $langId Language ID
     * @param integer $productId Product ID
     * @param integer $productAttributeId Product Attribute ID
     * @return array Images
     */
    public static function getImages($langId, $productId, $productAttributeId = NULL){
        $db = JFactory::getDBO();
        $attribute_filter = ($productAttributeId ? " AND attribute_image." . $db->quoteName('product_attribute_id') . " = " . (int)$productAttributeId : "");

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_image') . " AS image LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang');
        $query .= " AS image_lang ON (image." . $db->quoteName('image_id') . " = image_lang." . $db->quoteName('image_id') .") ";

        if ($productAttributeId){
            $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_image') . " AS attribute_image ON (image.";
            $query .= $db->quoteName('image_id') . " = attribute_image." . $db->quoteName('image_id') . ")";
        }
        $query .= " WHERE image." . $db->quoteName('product_id') . " = " . (int)$productId . " AND image_lang.";
        $query .= $db->quoteName('lang_id') . " = " .(int)$langId . $attribute_filter. " ORDER BY image." . $db->quoteName('position') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Returns image path in the old or in the new filesystem
     *
     * @ returns string image path
     */
    public function getExistingImagePath(){
        if (!$this->image_id){ return false; }

        if (!$this->existing_path){
            if (JeproshopSettingModelSetting::getValue('legacy_images') && file_exists(COM_JEPROSHOP_PRODUCT_IMAGE_DIR . $this->product_id . '_' . $this->image_id . '.' . $this->image_format)){
                $this->existing_path = $this->product_id . DIRECTORY_SEPARATOR . $this->image_id;
            }else{
                $this->existing_path = $this->getImagePath();
            }
        }
        return $this->existing_path;
    }

    /**
     * Returns the path to the image without file extension
     *
     * @return string path
     */
    public function getImagePath() {
        if (!$this->image_id){ return false; }

        $path = $this->getImageFolder() . $this->image_id;
        return $path;
    }

    /**
     * Returns the path to the folder containing the image in the new filesystem
     *
     * @return string path to folder
     */
    public function getImageFolder() {
        if (!$this->image_id){ return false; }

        if (!$this->folder){
            $this->folder = JeproshopImageModelImage::getStaticImageFolder($this->image_id);
        }
        return $this->folder;
    }

    /**
     * Returns the path to the folder containing the image in the new filesystem
     *
     * @param mixed $imageId
     * @return string path to folder
     */
    public static function getStaticImageFolder($imageId){
        if (!is_numeric($imageId)){ return false; }
        $folders = str_split((string)$imageId);
        return implode('/', $folders).'/';
    }

    /**
     * Checks if current object is associated to a shop.
     *
     * @param int|null $shopId
     * @return bool
     */
    public function isAssociatedToShop($shopId = null){
        if ($shopId === null) {
            $shopId = JeproshopContext::getContext()->shop->shop_id;
        }

        //$cache_id = 'jeproshop_image_shop_'.(int)$this->image_id.'-'.(int)$shopId;

        $db = JFactory::getDBO();
        $query = "SELECT shop_id FROM " . $db->quoteName("#__jeproshop_image_shop") . " WHERE " . $db->quoteName("image_id");
        $query .= " = " . (int)$this->image_id . " AND " . $db->quoteName("shop_id") . " = " .(int)$shopId;

        $db->setQuery($query);
        return $db->loadResult();
    }

}

class JeproshopImageManager
{
    const ERROR_FILE_NOT_EXIST = 1;
    const ERROR_FILE_WIDTH = 2;
    const ERROR_MEMORY_LIMIT = 3;

    /**
     * Generate a cached thumbnail for object lists (eg. carrier, order statuses...etc)
     *
     * @param string $image Real image filename
     * @param string $cache_image Cached filename
     * @param int $size Desired size
     * @param string $image_type Image type
     * @param bool $disable_cache When turned on a timestamp will be added to the image URI to disable the HTTP cache
     * @param bool $regenerate When turned on and the file already exist, the file will be regenerated
     * @return string
     */
    public static function thumbnail($image, $cache_image, $size, $image_type = 'jpg', $disable_cache = true, $regenerate = false){
        if (!file_exists($image)){ return ''; }

        if (file_exists(COM_JEPROSHOP_TMP_IMAGE_DIR . $cache_image) && $regenerate){
            @unlink(COM_JEPROSHOP_TMP_IMAGE_DIR . $cache_image);
        }

        if ($regenerate || !file_exists(COM_JEPROSHOP_TMP_IMAGE_DIR . $cache_image)){
            $info = getimagesize($image);

            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
            if (!JeproshopImageManager::checkImageMemoryLimit($image)){
                return false;
            }
            $x = $info[0];
            $y = $info[1];
            $max_x = $size * 3;

            // Size is already ok
            if ($y < $size && $x <= $max_x){
                copy($image, COM_JEPROSHOP_TMP_IMAGE_DIR . $cache_image);
                // We need to resize */
            }else{
                $ratio_x = $x / ($y / $size);
                if ($ratio_x > $max_x)
                {
                    $ratio_x = $max_x;
                    $size = $y / ($x / $max_x);
                }

                JeproshopImageManager::resize($image, COM_JEPROSHOP_TMP_IMAGE_DIR . $cache_image, $ratio_x, $size, $image_type);
            }
        }
        // Relative link will always work, whatever the base uri set in the admin
        //if (JeproshopContext::getContext()->controller->controller_type == 'admin')
        return '<img src="../img/tmp/'.$cache_image.($disable_cache ? '?time='.time() : '').'" alt="" class="img img-thumbnail" />';
        /*else
            return '<img src="'. COM_JEPROSHOP_TMP_IMAGE_DIR .$cache_image.($disable_cache ? '?time='.time() : '').'" alt="" class="img img-thumbnail" />'; */
    }

    /**
     * Check if memory limit is too long or not
     *
     * @static
     * @param $image
     * @return bool
     */
    public static function checkImageMemoryLimit($image){
        $info = @getimagesize($image);

        if (!is_array($info) || !isset($info['bits']))
            return true;

        $memoryLimit = JeproshopTools::getMemoryLimit();
        // memory_limit == -1 => unlimited memory
        if (function_exists('memory_get_usage') && (int)$memoryLimit != -1){
            $currentMemory = memory_get_usage();
            $channel = isset($info['channels']) ? ($info['channels'] / 8) : 1;

            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
            if (($info[0] * $info[1] * $info['bits'] * $channel + pow(2, 16)) * 1.8 + $currentMemory > $memoryLimit - 1024 * 1024)
                return false;
        }

        return true;
    }

    /**
     * Resize, cut and optimize image
     *
     * @param string $srcFile Image object from $_FILE
     * @param string $dstFile Destination filename
     * @param integer $dstWidth Desired width (optional)
     * @param integer $dstHeight Desired height (optional)
     * @param string $fileType
     * @param bool $forceType
     * @param int $error
     * @return bool Operation result
     */
    public static function resize($srcFile, $dstFile, $dstWidth = null, $dstHeight = null, $fileType = 'jpg', $forceType = false, &$error = 0){
        if (PHP_VERSION_ID < 50300)
            clearstatcache();
        else
            clearstatcache(true, $srcFile);

        if (!file_exists($srcFile) || !filesize($srcFile))
            return !($error = self::ERROR_FILE_NOT_EXIST);

        list($tmpWidth, $tmpHeight, $type) = getimagesize($srcFile);
        $srcImage = JeproshopImageManager::create($type, $srcFile);

        if (function_exists('exif_read_data') && function_exists('mb_strtolower')){
            $exif = @exif_read_data($srcFile);
            if ($exif && isset($exif['Orientation'])){
                switch ($exif['Orientation']){
                    case 3:
                        $srcWidth = $tmpWidth;
                        $srcHeight = $tmpHeight;
                        $srcImage = imagerotate($srcImage, 180, 0);
                        break;

                    case 6:
                        $srcWidth = $tmpHeight;
                        $srcHeight = $tmpWidth;
                        $srcImage = imagerotate($srcImage, -90, 0);
                        break;

                    case 8:
                        $srcWidth = $tmpHeight;
                        $srcHeight = $tmpWidth;
                        $srcImage = imagerotate($srcImage, 90, 0);
                        break;

                    default:
                        $srcWidth = $tmpWidth;
                        $srcHeight = $tmpHeight;
                }
            }else{
                $srcWidth = $tmpWidth;
                $srcHeight = $tmpHeight;
            }
        }else{
            $srcWidth = $tmpWidth;
            $srcHeight = $tmpHeight;
        }

        // If PS_IMAGE_QUALITY is activated, the generated image will be a PNG with .jpg as a file extension.
        // This allow for higher quality and for transparency. JPG source files will also benefit from a higher quality
        // because JPG re-encoding by GD, even with max quality setting, degrades the image.
        if (JeproshopSettingModelSetting::getValue('image_quality') == 'png_all' || (JeproshopSettingModelSetting::getValue('image_quality') == 'png' && $type == IMAGETYPE_PNG) && !$forceType)
            $fileType = 'png';

        if (!$srcWidth)
            return !($error = self::ERROR_FILE_WIDTH);
        if (!$dstWidth)
            $dstWidth = $srcWidth;
        if (!$dstHeight)
            $dstHeight = $srcHeight;

        $widthDiff = $dstWidth / $srcWidth;
        $heightDiff = $dstHeight / $srcHeight;

        if ($widthDiff > 1 && $heightDiff > 1){
            $nextWidth = $srcWidth;
            $nextHeight = $srcHeight;
        }else{
            if (JeproshopSettingModelSetting::getValue('image_generation_method') == 2 || (!JeproshopSettingModelSetting::getValue('image_generation_method') && $widthDiff > $heightDiff))
            {
                $nextHeight = $dstHeight;
                $nextWidth = round(($srcWidth * $nextHeight) / $srcHeight);
                $dstWidth = (int)(!JeproshopSettingModelSetting::getValue('image_generation_method') ? $dstWidth : $nextWidth);
            }
            else
            {
                $nextWidth = $dstWidth;
                $nextHeight = round($srcHeight * $dstWidth / $srcWidth);
                $dstHeight = (int)(!JeproshopSettingModelSetting::getValue('image_generation_method') ? $dstHeight : $nextHeight);
            }
        }

        if (!JeproshopImageManager::checkImageMemoryLimit($srcFile))
            return !($error = self::ERROR_MEMORY_LIMIT);

        $destinationImage = imagecreatetruecolor($dstWidth, $dstHeight);

        // If image is a PNG and the output is PNG, fill with transparency. Else fill with white background.
        if ($fileType == 'png' && $type == IMAGETYPE_PNG)
        {
            imagealphablending($destinationImage, false);
            imagesavealpha($destinationImage, true);
            $transparent = imagecolorallocatealpha($destinationImage, 255, 255, 255, 127);
            imagefilledrectangle($destinationImage, 0, 0, $dstWidth, $dstHeight, $transparent);
        }
        else
        {
            $white = imagecolorallocate($destinationImage, 255, 255, 255);
            imagefilledrectangle($destinationImage, 0, 0, $dstWidth, $dstHeight, $white);
        }

        imagecopyresampled($destinationImage, $srcImage, (int)(($dstWidth - $nextWidth) / 2), (int)(($dstHeight - $nextHeight) / 2), 0, 0, $nextWidth, $nextHeight, $srcWidth, $srcHeight);
        return (JeproshopImageManager::write($fileType, $destinationImage, $dstFile));
    }

    /**
     * Create an image with GD extension from a given type
     *
     * @param string $type
     * @param string $filename
     * @return resource
     */
    public static function create($type, $filename){
        switch ($type){
            case IMAGETYPE_GIF :
                return imagecreatefromgif($filename);
                break;

            case IMAGETYPE_PNG :
                return imagecreatefrompng($filename);
                break;

            case IMAGETYPE_JPEG :
            default:
                return imagecreatefromjpeg($filename);
                break;
        }
    }

    /**
     * Create an empty image with white background
     *
     * @param int $width
     * @param int $height
     * @return resource
     */
    public static function createWhiteImage($width, $height){
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        return $image;
    }

    /**
     * Generate and write image
     *
     * @param string $type
     * @param resource $resource
     * @param string $filename
     * @return bool
     */
    public static function write($type, $resource, $filename){
        switch ($type)	{
            case 'gif':
                $success = imagegif($resource, $filename);
                break;

            case 'png':
                $quality = (JeproshopSettingModelSetting::getValue('png_quality') === false ? 7 : JeproshopSettingModelSetting::getValue('png_quality'));
                $success = imagepng($resource, $filename, (int)$quality);
                break;

            case 'jpg':
            case 'jpeg':
            default:
                $quality = (JeproshopSettingModelSetting::getValue('jpeg_quality') === false ? 90 : JeproshopSettingModelSetting::getValue('jpeg_quality'));
                imageinterlace($resource, 1); /// make it PROGRESSIVE
                $success = imagejpeg($resource, $filename, (int)$quality);
                break;
        }
        imagedestroy($resource);
        @chmod($filename, 0664);
        return $success;
    }

}