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

class JeproshopImageTypeModelImageType extends JeproshopModel{
    public $image_type_id;

    /** @var string Name */
    public $name;

    /** @var integer Width */
    public $width;

    /** @var integer Height */
    public $height;

    /** @var boolean Apply to products */
    public $products;

    /** @var integer Apply to categories */
    public $categories;

    /** @var integer Apply to manufacturers */
    public $manufacturers;

    /** @var integer Apply to suppliers */
    public $suppliers;

    /** @var integer Apply to scenes */
    public $scenes;

    /** @var integer Apply to store */
    public $stores;

    /**
     * @var array Image types cache
     */
    protected static $images_types_cache = array();

    protected static $images_types_name_cache = array();

    protected $webserviceParameters = array();

    /**
     * Returns image type definitions
     *
     * @param string|null Image type
     * @return array Image type definitions
     */
    public static function getImagesTypes($type = null){
        if (!isset(self::$images_types_cache[$type])){
            $db = JFactory::getDBO();
            $where = " WHERE 1";
            if (!empty($type)){
                $where .= " AND " . $db->quoteName($db->escape($type)) . " = 1 ";
            }
            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_image_type') . $where . " ORDER BY " . $db->quoteName('name') . " ASC";

            $db->setQuery($query);
            self::$images_types_cache[$type] = $db->loadObjectList();
        }
        return self::$images_types_cache[$type];
    }

    /**
     * Finds image type definition by name and type
     * @param string $name
     * @param string $type
     * @param JeproshopOrderModelOrder $order
     * @return mixed
     */
    public static function getByNameNType($name, $type = null, $order = null){
        if (!isset(self::$images_types_name_cache[$name.'_'.$type.'_'.$order]))	{
            $db = Jfactory::getDBO();

            $query = "SELECT " . $db->quoteName('image_type_id') . ", " . $db->quoteName('name') . ", ";
            $query .= $db->quoteName('width') . ", " . $db->quoteName('height') . ", " . $db->quoteName('products');
            $query .= ", " . $db->quoteName('categories') . ", " . $db->quoteName('manufacturers') . ", ";
            $query .= $db->quoteName('suppliers') . ", " . $db->quoteName('scenes') . " FROM " . $db->quoteName('#__jeproshop_image_type');
            $query .= "	WHERE " . $db->quoteName('name') . " LIKE " . $db->quote($db->escape($name));
            $query .= (!is_null($type) ? " AND " . $db->quoteName($db->escape($type)) . " = 1" : "");
            $query .= (!is_null($order) ? " ORDER BY " . $db->quoteName($db->escape($order)) . " ASC" : "" );

            $db->setQuery($query);
            self::$images_types_name_cache[$name.'_'.$type.'_'.$order] = $db->loadObject();
        }
        return self::$images_types_name_cache[$name.'_'.$type.'_'.$order];
    }

}