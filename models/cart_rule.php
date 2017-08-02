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


/*** ------- CART RULE -------- *****/
class JeproshopCartRuleModelCartRule extends JeproshopModel
{
    /* Filters used when retrieving the cart rules applied to a cart of when calculating the value of a reduction */
    const JEPROSHOP_FILTER_ACTION_ALL = 1;
    const JEPROSHOP_FILTER_ACTION_SHIPPING = 2;
    const JEPROSHOP_FILTER_ACTION_REDUCTION = 3;
    const JEPROSHOP_FILTER_ACTION_GIFT = 4;
    const JEPROSHOP_FILTER_ACTION_ALL_NO_CAP = 5;

    const JEPROSHOP_BO_ORDER_CODE_PREFIX = 'BO_ORDER_';

    /* This variable controls that a free gift is offered only once, even when multi-shipping is activated and the same product is delivered in both addresses */
    protected static $only_one_gift = array();

    public $cart_rule_id;
    public $name;
    public $customer_id;
    public $date_from;
    public $date_to;
    public $description;
    public $quantity = 1;
    public $quantity_per_user = 1;
    public $priority = 1;
    public $partial_use = 1;
    public $code;
    public $minimum_amount;
    public $minimum_amount_tax;
    public $minimum_amount_currency;
    public $minimum_amount_shipping;
    public $country_restriction;
    public $carrier_restriction;
    public $group_restriction;
    public $cart_rule_restriction;
    public $product_restriction;
    public $shop_restriction;
    public $free_shipping;
    public $reduction_percent;
    public $reduction_amount;
    public $reduction_tax;
    public $reduction_currency;
    public $reduction_product;
    public $gift_product;
    public $gift_product_attribute;
    public $highlight;
    public $published = 1;
    public $date_add;
    public $date_upd;

    /**
     * @static
     * @return bool
     */
    public static function isFeaturePublished()
    {
        static $is_feature_active = null;
        if ($is_feature_active === null) {
            $is_feature_active = (bool)JeproshopSettingModelSetting::getValue('cart_rule_feature_active');
        }
        return $is_feature_active;
    }

    /**
     * @static
     * @param $langId
     * @param $customerId
     * @param bool $published
     * @param bool $includeGeneric
     * @param bool $inStock
     * @param JeproshopCartModelCart|null $cart
     * @return array
     */
    public static function getCustomerCartRules($langId, $customerId, $published = false, $includeGeneric = true, $inStock = false, JeproshopCartModelCart $cart = null){
        if (!JeproshopCartRuleModelCartRule::isFeaturePublished()){
            return array();
        }

        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_cart_rule') . " AS cart_rule LEFT JOIN " . $db->quoteName('#__jeproshop_cart_rule_lang');
        $query .= " AS cart_rule_lang ON (cart_rule." .  $db->quoteName('cart_rule_id') . " = cart_rule_lang." . $db->quoteName('cart_rule_id') . " AND ";
        $query .= "cart_rule_lang." . $db->quoteName('lang_id') . " = " . (int)$langId . ") WHERE ( cart_rule." . $db->quoteName('customer_id') . " = ";
        $query .= (int)$customerId . " OR cart_rule.group_restriction = 1 " . ($includeGeneric ?  "OR cart_rule." . $db->quoteName('customer_id') . " = 0" : "");
        $query .= ") AND cart_rule.date_from < '" . date('Y-m-d H:i:s') . "' AND cart_rule.date_to > '" . date('Y-m-d H:i:s') . "'";
        $query .= ($published ? " AND cart_rule." . $db->quoteName('published') . " = 1" : ""). ($inStock ? " AND cart_rule." . $db->quoteName('quantity') . " > 0" :  "");

        $db->setQuery($query);
        $result = $db->loadObjectList();

        // Remove cart rule that does not match the customer groups
        $customerGroups = JeproshopCustomerModelCustomer::getStaticGroups($customerId);
        foreach ($result as $key => $cart_rule){
            if ($cart_rule->group_restriction){
                $query = "SELECT " . $db->quoteName('group_id') . " FROM " . $db->quoteName('#__jeproshop_cart_rule_group');
                $query .= " WHERE " . $db->quoteName('cart_rule_id') . " = " . (int)$cart_rule->cart_rule_id;
                $db->setQuery($query);
                $cartRuleGroups = $db->loadObjectList();
                foreach ($cartRuleGroups as $cartRuleGroup){
                    if (in_array($cartRuleGroup->group_id, $customerGroups)){
                        continue 2;
                    }
                }
                unset($result[$key]);
            }
        }

        foreach ($result as &$cart_rule){
            if ($cart_rule->quantity_per_user){
                $quantity_used = JeproshopOrderModelOrder::getCustomerDiscounts((int)$customerId, (int)$cart_rule->cart_rule_id);
                if (isset($cart) && isset($cart->cart_id)){
                    $quantity_used += $cart->getDiscountsCustomer((int)$cart_rule->cart_rule_id);
                }
                $cart_rule->quantity_for_user = $cart_rule->quantity_per_user - $quantity_used;
            }else{
                $cart_rule->quantity_for_user = 0;
            }
        }
        unset($cart_rule);

        foreach ($result as $cart_rule){
            if ($cart_rule->shop_restriction){
                $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_cart_rule_shop') . " WHERE cart_rule_id = " . (int)$cart_rule->cart_rule_id;
                $db->setQuery($query);
                $cartRuleShops = $db->loadObjectList();
                foreach ($cartRuleShops as $cartRuleShop){
                    if (JeproshopShopModelShop::isFeatureActive() && ($cartRuleShop->shop_id == JeproshopContext::getContext()->shop->shop_id)){
                        continue 2;
                    }
                }
                unset($result[$key]);
            }
        }

        // RetroCompatibility with 1.4 discounts
        foreach ($result as &$cart_rule){
            $cart_rule->value = 0;
            $cart_rule->minimal = JeproshopTools::convertPriceFull($cart_rule->minimum_amount, new JeproshopCurrencyModelCurrency($cart_rule->minimum_amount_currency), JeproshopContext::getContext()->currency);
            $cart_rule->cumulable = !$cart_rule->cart_rule_restriction;
            $cart_rule->discount_type_id = false;
            if ($cart_rule->free_shipping){
                $cart_rule->discount_type_id = Discount::FREE_SHIPPING;
            }elseif ($cart_rule->reduction_percent > 0){
                $cart_rule->discount_type_id = Discount::PERCENT;
                $cart_rule->value = $cart_rule->reduction_percent;
            }elseif ($cart_rule->reduction_amount > 0){
                $cart_rule->discount_type_id = Discount::AMOUNT;
                $cart_rule->value = $cart_rule->reduction_amount;
            }
        }
        return $result;
    }
}