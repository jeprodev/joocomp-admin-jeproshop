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

    const BO_ORDER_CODE_PREFIX = 'BO_ORDER_';

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
}