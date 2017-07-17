<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
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

class JeproshopSpecificPriceModelSpecificPrice extends JeproshopModel{
    public $product_id;

    public $specific_price_rule_id = 0;

    public $cart_id = 0;

    public $product_attribute_id;

    public $specific_price_id;

    public $shop_id;

    public $shop_group_id;

    public $currency_id;

    public $country_id;

    public $group_id;

    public $customer_id;

    public $price;

    public $from_quantity;

    public $reduction;

    public $reduction_type;

    public $from;

    public $to;

    protected static $_specific_price_cache = array();
    protected static $_cache_priorities = array();

    public static function getSpecificPricesByProductId($productId, $productAttributeId = false, $cartId = FALSE){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_specific_price') . " WHERE " . $db->quoteName('product_id');
        $query .= " = " . (int)$productId . ($productAttributeId ? " AND " . $db->quoteName('product_attribute_id') . " = " . (int)$productAttributeId : " ");
        $query .= " AND cart_id = " . (int)$cartId;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getPriority($productId){
        if(!JeproshopSpecificPriceModelSpecificPrice::isFeaturePublished()){
            return explode(';', JeproshopSettingModelSetting::getValue('specific_price_priorities'));
        }

        if(!isset(JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$productId])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('priority') . ", " . $db->quoteName('specific_price_priority_id') . " FROM ";
            $query .= $db->quoteName('#__jeproshop_specific_price_priority') ." WHERE " . $db->quoteName('product_id') . " = ";
            $query .= (int)$productId . " ORDER BY " . $db->quoteName('specific_price_priority_id') . " DESC ";

            $db->setQuery($query);
            JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$productId] = $db->loadObject();
        }
        $priorities = JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$productId];
        if(!$priorities){
            $priority = JeproshopSettingModelSetting::getValue('specific_price_priorities');
            $priorities = 'customer_id;' . $priority;
        }else{
            $priorities = $priorities->priority;
        }

        return preg_split('/;/', $priorities);
    }

    public static function isFeaturePublished(){
        static $feature_active = NULL;
        if($feature_active === NULL){
            $feature_active = JeproshopSettingModelSetting::getValue('specific_price_feature_active');
        }
        return $feature_active;
    }
}