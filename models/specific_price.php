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

    public static function getSpecificPrice($productId, $shopId, $currencyId, $countryId, $groupId, $quantity, $productAttributeId = null, $customerId = 0, $cartId = 0, $realQuantity = 0){
        if (!JeproshopSpecificPriceModelSpecificPrice::isFeaturePublished()){ return array(); }
        /*
        ** The date is not taken into account for the cache, but this is for the better because it keeps the consistency for the whole script.
        ** The price must not change between the top and the bottom of the page
        */

        $db = JFactory::getDBO();
        $key = ((int)$productId . '_' . (int)$shopId . '_' . (int)$currencyId . '_' . (int)$countryId . '_' . (int)$groupId . '_' . (int)$quantity . '_' . (int)$productAttributeId . '_'.(int)$cartId . '_' . (int)$customerId . '_' . (int)$realQuantity);
        if (!array_key_exists($key, JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache)) {
            $now = date('Y-m-d H:i:s');
            $query = "SELECT *, " . JeproshopSpecificPriceModelSpecificPrice::getScoreQuery($productId, $shopId, $currencyId, $countryId, $groupId, $customerId);
            $query .= " FROM " . $db->quoteName('#__jeproshop_specific_price') . " WHERE " . $db->quoteName('product_id') . " IN (0, " .(int)$productId . ") AND ";
            $query .= $db->quoteName('product_attribute_id') . " IN (0, " .(int)$productAttributeId . ") AND " . $db->quoteName('shop_id') . " IN (0, " . (int)$shopId;
            $query .= ") AND " . $db->quoteName('currency_id') . " IN (0, " .(int)$currencyId . ") AND " . $db->quoteName('country_id') . " IN (0, " .(int)$countryId ;
            $query .= ") AND " . $db->quoteName('group_id') . " IN (0, " .(int)$groupId . ") AND " . $db->quoteName('customer_id') . " IN (0, " .(int)$customerId . ") ";
            $query .= "AND ( (" . $db->quoteName('from') . " = '0000-00-00 00:00:00' OR '" . $now . "' >= " . $db->quoteName('from') . ") AND (" . $db->quoteName('to') ;
            $query .= " = '0000-00-00 00:00:00' OR '" . $now. "' <= " . $db->quoteName('to') . ") ) AND cart_id IN (0, ".(int)$cartId . ") AND IF(" . $db->quoteName('from_quantity');
            $query .= " > 1, " . $db->quoteName('from_quantity') . ", 0) <= " ;
            $query .= (JeproshopSettingModelSetting::getValue('qty_discount_on_combination') || !$cartId || !$realQuantity) ? (int)$quantity : max(1, (int)$realQuantity);
            $query .= " ORDER BY " . $db->quoteName('product_attribute_id') . " DESC, " . $db->quoteName('from_quantity') . " DESC, " . $db->quoteName('specific_price_rule_id');
            $query .= " ASC, " . $db->quoteName('score') . " DESC";

            $db->setQuery($query);
            JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache[$key] = $db->loadObject();

        }
        return JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache[$key];
    }

    /**
     * score generation for quantity discount
     * @param $productId
     * @param $shopId
     * @param $currencyId
     * @param $countryId
     * @param $groupId
     * @param $customerId
     * @return string
     */
    protected static function getScoreQuery($productId, $shopId, $currencyId, $countryId, $groupId, $customerId){
        $db = JFactory::getDBO();
        $now = date('Y-m-d H:i:s');
        $select = "( IF ('" .$now. "' >= " . $db->quoteName('from') . " AND '" . $now. "' <= " . $db->quoteName('to') . ", ".pow(2, 0).", 0) + ";

        $priority = JeproshopSpecificPriceModelSpecificPrice::getPriority($productId);
        foreach (array_reverse($priority) as $k => $field){
            if (!empty($field)){
                $select .= " IF (" . $db->quote($field, true) . " = ";
                if($field == 'country_id'){
                    $select .= (int)$countryId;
                }else if($field == 'currency_id') {
                    $select .= (int)$currencyId;
                }else if($field == 'group_id'){
                    $select .= (int)$groupId;
                }else if($field == 'customer_id'){
                    $select .= (int)$customerId;
                }else if($field == 'country_id'){
                    $select .= (int)$countryId;
                }else if($field == 'shop_id'){
                    $select.= (int)$shopId;
                }
                $select .= ", " .pow(2, $k + 1).", 0) + ";
            }
        }
        return rtrim($select, ' +'). ") AS " . $db->quoteName('score');
    }

}


class JeproshopSpecificPriceRuleModelSpecificPriceRule extends JeproshopModel {
    public $specific_price_rule_id;
    public $name;
    public $shop_id;
    public $currency_id;
    public $country_id;
    public $group_id;
    public $from_quantity;
    public $price;
    public $reduction;
    public $reduction_type;
    public $from;
    public $to;

    protected static $rules_application_enable = true;

    public function __construct($specificPriceRuleId = null){
        if($specificPriceRuleId){
            $cacheKey = "jeproshop_specific_price_rule_" . $specificPriceRuleId;
            if(JeproshopCache::isStored($cacheKey)){
                $specificPriceRuleData = JeproshopCache::retrieve($cacheKey);
            }else {
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_specific_price_rule') . " WHERE " . $db->quoteName('specific_price_rule_id');
                $query .= " = " . (int)$specificPriceRuleId;

                $db->setQuery($query);
                $specificPriceRuleData = $db->loadObject();
            }

            if($specificPriceRuleData){
                $this->specific_price_rule_id = $specificPriceRuleId;
                foreach($specificPriceRuleData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
            
        }
    }
}