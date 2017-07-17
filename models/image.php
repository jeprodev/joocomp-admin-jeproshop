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

    public $position;

    public $cover;

    public $legend;

    public $image_format ='jpg';

    /** @var string image folder */
    protected $folder;

    protected static $_cacheGetSize = array();

    /** @var string image path without extension */
    protected $existing_path;

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
}