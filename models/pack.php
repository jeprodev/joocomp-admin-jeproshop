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

class JeproshopProductPack extends JeproshopProductModelProduct{
    protected static $cachePackItems = array();
    protected static $cacheIsPack = array();
    protected static $cacheIsPacked = array();

    /**
     * Is product a pack?
     *
     * @static
     * @param $productId
     * @return bool
     */
    public static function isPack($productId){
        if (!JeproshopProductPack::isFeaturePublished()){
            return false;
        }

        if (!$productId){ return false; }

        if (!array_key_exists($productId, self::$cacheIsPack)){
            $db = JFactory::getDBO();

            $query = "SELECT COUNT(*) FROM " .$db->quoteName('#__jeproshop_product_pack') . " WHERE " . $db->quoteName('product_pack_id') . " = " . (int)$productId;
            $db->setQuery($query);
            $result = $db->loadResult();
            self::$cacheIsPack[$productId] = ($result > 0);
        }
        return self::$cacheIsPack[$productId];
    }

    /**
     * Is product in a pack?
     *
     * @static
     * @param $productId
     * @return bool
     */
    public static function isPacked($productId){
        if (!JeproshopProductPack::isFeaturePublished()){
            return false;
        }
        if (!array_key_exists($productId, self::$cacheIsPacked)){
            $db = JFactory::getDBO();

            $query = "SELECT COUNT(*) FROM " . $db->quoteName('#__jeproshop_product_pack') . " WHERE product_item_id = " . (int)$productId;

            $db->setQuery($query);
            $result = $db->loadResult();
            self::$cacheIsPacked[$productId] = ($result > 0);
        }
        return self::$cacheIsPacked[$productId];
    }

    /**
     * This method is allow to know if a feature is used or active
     *
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('pack_feature_active');
    }
}