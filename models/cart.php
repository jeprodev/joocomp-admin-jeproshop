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

class JeproshopCartModelCart extends JeproshopModel {
    public $cart_id;

    public $shop_group_id;

    public $shop_id;

    /** @var integer Customer delivery address ID */
    public $delivery_address_id;

    /** @var integer Customer invoicing address ID */
    public $invoice_address_id;

    /** @var integer Customer currency ID */
    public $currency_id;

    /** @var integer Customer ID */
    public $customer_id;

    /** @var integer Guest ID */
    public $guest_id;

    /** @var integer Language ID */
    public $lang_id;

    /** @var boolean True if the customer wants a recycled package */
    public $recyclable = 0;

    /** @var boolean True if the customer wants a gift wrapping */
    public $gift = 0;

    /** @var string Gift message if specified */
    public $gift_message;

    /** @var boolean Mobile Theme */
    public $mobile_theme;

    /** @var string Object creation date */
    public $date_add;

    /** @var string secure_key */
    public $secure_key;

    /** @var integer Carrier ID */
    public $carrier_id = 0;

    /** @var string Object last modification date */
    public $date_upd;

    public $checkedTos = false;
    public $pictures;
    public $textFields;

    public $delivery_option;

    /** @var boolean Allow to separate order in multiple package in order to receive as soon as possible the available products */
    public $allow_separated_package = false;

    protected static $_nbProducts = array();
    protected static $_isVirtualCart = array();

    protected $_products = null;
    protected static $_totalWeight = array();
    protected $_taxCalculationMethod = COM_JEPROSHOP_TAX_EXCLUDED;
    protected static $_carriers = null;
    protected static $_taxes_rate = null;
    protected static $_attributesLists = array();

    const ONLY_PRODUCTS = 1;
    const ONLY_DISCOUNTS = 2;
    const BOTH = 3;
    const BOTH_WITHOUT_SHIPPING = 4;
    const ONLY_SHIPPING = 5;
    const ONLY_WRAPPING = 6;
    const ONLY_PRODUCTS_WITHOUT_SHIPPING = 7;
    const ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING = 8;

    public function __construct($cartId = null){
        if ($this->isMultiShop('cart', false) && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if ($cartId){
            // Load object from database if object id is present
            $cacheKey = 'jeproshop_model_cart_' . (int)$cartId.'_'.(int)$this->shop_id;
            if (!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_cart') . " AS cart WHERE cart.cart_id = " . (int)$cartId;



                // Get shop information
                if (JeproshopShopModelShop::isTableAssociated('cart')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_cart_shop') . " AS cart_shop ON(cart.cart_id =";
                    $query .= " = cart_shop.cart_id AND cart_shop.shop_id = " . (int)$this->shop_id . ")";
                }

                $db->setQuery($query);
                $cartData = $db->loadObject();
                if($cartData){

                    JeproshopCache::store($cacheKey, $cartData);
                }
            }else{
                $cartData = JeproshopCache::retrieve($cacheKey);
            }

            if ($cartData){
                $this->cart_id = (int)$cartId;
                foreach ($cartData as $key => $value){
                    if (array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        if ($this->customer_id){
            if (isset(JeproshopContext::getContext()->customer) && JeproshopContext::getContext()->customer->customer_id == $this->customer_id){
                $customer = JeproshopContext::getContext()->customer;
            }else{
                $customer = new JeproshopCustomerModelCustomer((int)$this->customer_id);
            }
            if ((!$this->secure_key || $this->secure_key == '-1') && $customer->secure_key)
            {
                $this->secure_key = $customer->secure_key;
                $this->save();
            }
        }
        $this->_taxCalculationMethod = JeproshopGroupModelGroup::getPriceDisplayMethod(JeproshopGroupModelGroup::getCurrent()->group_id);
    }

    public function getCartList(){
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');
        $context = JeproshopContext::getContext();

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'address_id', 'string');
        $order_way = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC' , 'string');

        $use_limit = true;
        if($limit === false){
            $use_limit = false;
        }

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS cart." . $db->quoteName('cart_id') . " AS total, cart." . $db->quoteName('cart_id') . ", cart." . $db->quoteName('date_add') . " AS date_add, CONCAT(LEFT(customer.";
            $query .= $db->quoteName('firstname') . ", 1), '. ', customer." . $db->quoteName('lastname') . ") AS customer_name, carrier." . $db->quoteName('name');
            $query .= " AS carrier_name, IF (IFNULL(ord.order_id, '" . JText::_('COM_JEPROSHOP_NOT_ORDERED_LABEL') . "') = '" . JText::_('COM_JEPROSHOP_NOT_ORDERED_LABEL');
            $query .= "', IF(TIME_TO_SEC(TIMEDIFF(NOW(), cart." . $db->quoteName('date_add') . ")) > 86400, '" . JText::_('COM_JEPROSHOP_ABANDONED_CART_LABEL') . "', '";
            $query .= JText::_('COM_JEPROSHOP_NOT_ORDERED_LABEL') . "'), ord." . $db->quoteName('order_id') . ") AS order_id, IF(ord." . $db->quoteName('order_id') . ", 1";
            $query .= ", 0) AS badge_success, IF(ord." . $db->quoteName('order_id') . ", 0, 1) badge_danger, IF(connection." . $db->quoteName('guest_id') . ", 1, 0) AS guest_id ";
            $query .= " FROM " . $db->quoteName('#__jeproshop_cart') . " AS cart LEFT JOIN " . $db->quoteName('#__jeproshop_customer') . " AS customer ON (customer.";
            $query .= $db->quoteName('customer_id') . " = cart." . $db->quoteName('customer_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_currency') . " AS currency";
            $query .= " ON (currency." . $db->quoteName('currency_id') . " = cart." . $db->quoteName('currency_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_carrier');
            $query .= " AS carrier ON(carrier." . $db->quoteName('carrier_id') . " = cart." . $db->quoteName('carrier_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_orders');
            $query .= " AS ord ON (ord." . $db->quoteName('cart_id') . " = cart." . $db->quoteName('cart_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_connection');
            $query .= " AS connection ON (cart." . $db->quoteName('guest_id') . " = connection." . $db->quoteName('guest_id') . " AND TIME_TO_SEC(TIMEDIFF(NOW(), connection.";
            $query .= $db->quoteName('date_add') . ")) < 1800)";

            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($use_limit == true) ? " LIMIT " . (int)$limitStart . ", " . (int)$limit : " ");

            $db->setQuery($query);
            $carts = $db->loadObjectList();

            if($use_limit == true){
                $limitStart = (int)$limitStart -(int)$limit;
                if($limitStart < 0){ break; }
            }else{ break; }
        }while(empty($carts));

        $this->pagination = new JPagination($total, $limitStart, $limit);
        return $carts;
    }

    public static function getOrderTotalUsingTaxCalculationMethod($cartId){
        $context = JeproshopContext::getContext();
        $context->cart = new JeproshopCartModelCart($cartId);
        $context->currency = new JeproshopCurrencyModelCurrency((int)$context->cart->currency_id);
        $context->customer = new JeproshopCustomerModelCustomer((int)$context->cart->customer_id);
        return JeproshopCartModelCart::getTotalCart($cartId, true, JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING);
    }


    public static function getTotalCart($cartId, $useTaxDisplay = false, $type = JeproshopCartModelCart::BOTH){
        $cart = new JeproshopCartModelCart($cartId);
        if (!JeproshopTools::isLoadedObject($cart, 'cart_id'))
            die(Tools::displayError());

        $withTaxes = $useTaxDisplay ? $cart->_taxCalculationMethod != COM_JEPROSHOP_TAX_EXCLUDED : true;
        return JeproshopTools::displayPrice($cart->getOrderTotal($withTaxes, $type), JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$cart->currency_id), false);
    }

    /**
     * This function returns the total cart amount
     *
     * Possible values for $type:
     * JeproshopCartModelCart::ONLY_PRODUCTS
     * JeproshopCartModelCart::ONLY_DISCOUNTS
     * JeproshopCartModelCart::BOTH
     * JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING
     * JeproshopCartModelCart::ONLY_SHIPPING
     * JeproshopCartModelCart::ONLY_WRAPPING
     * JeproshopCartModelCart::ONLY_PRODUCTS_WITHOUT_SHIPPING
     * JeproshopCartModelCart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING
     *
     * @param boolean $withTaxes With or without taxes
     * @param integer $type Total type
     * @param null $products
     * @param null $carrierId
     * @param boolean $useCache Allow using cache of the method CartRule::getContextualValue
     * @return float Order total
     */
    public function getOrderTotal($withTaxes = true, $type = JeproshopCartModelCart::BOTH, $products = null, $carrierId = null, $useCache = true){
        if (!$this->cart_id){ return 0; }

        $type = (int)$type;
        $arrayType = array(
            JeproshopCartModelCart::ONLY_PRODUCTS, JeproshopCartModelCart::ONLY_DISCOUNTS,
            JeproshopCartModelCart::BOTH, JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING,
            JeproshopCartModelCart::ONLY_SHIPPING, JeproshopCartModelCart::ONLY_WRAPPING,
            JeproshopCartModelCart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
            JeproshopCartModelCart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
        );

        // Define virtual context to prevent case where the cart is not the in the global context
        $virtualContext = JeproshopContext::getContext()->cloneContext();
        $virtualContext->cart = $this;

        if (!in_array($type, $arrayType))
            die(JeproshopTools::displayError(''));

        $withShipping = in_array($type, array(JeproshopCartModelCart::BOTH, JeproshopCartModelCart::ONLY_SHIPPING));

        // if cart rules are not used
        if ($type == JeproshopCartModelCart::ONLY_DISCOUNTS && !JeproshopCartRuleModelCartRule::isFeaturePublished())
            return 0;

        // no shipping cost if is a cart with only virtual products
        $virtual = $this->isVirtualCart();
        if ($virtual && $type == JeproshopCartModelCart::ONLY_SHIPPING)
            return 0;

        if ($virtual && $type == JeproshopCartModelCart::BOTH)
            $type = JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING;

        if ($withShipping || $type == JeproshopCartModelCart::ONLY_DISCOUNTS){
            if (is_null($products) && is_null($carrierId)) {
                $shippingFees = $this->getTotalShippingCost(null, (boolean)$withTaxes);
            }else {
                $shippingFees = $this->getPackageShippingCost($carrierId, (bool)$withTaxes, null, $products);
            }
        }else {
            $shippingFees = 0;
        }

        if ($type == JeproshopCartModelCart::ONLY_SHIPPING) {
            return $shippingFees;
        }

        if ($type == JeproshopCartModelCart::ONLY_PRODUCTS_WITHOUT_SHIPPING) {
            $type = JeproshopCartModelCart::ONLY_PRODUCTS;
        }

        $paramProduct = true;
        if (is_null($products)){
            $paramProduct = false;
            $products = $this->getProducts();
        }

        if ($type == JeproshopCartModelCart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING){
            foreach ($products as $key => $product) {
                if ($product->is_virtual)
                    unset($products[$key]);
            }

            $type = JeproshopCartModelCart::ONLY_PRODUCTS;
        }

        $orderTotal = 0;
        if (JeproshopTaxModelTax::taxExcludedOption()) {
            $withTaxes = false;
        }

        foreach ($products as $product) {
            // products refer to the cart details
            if ($virtualContext->shop->shop_id != $product->shop_id) {
                $virtualContext->shop = new JeproshopShopModelShop((int)$product->shop_id);
            }
            if (JeproshopSettingModelSetting::getValue('PS_TAX_ADDRESS_TYPE') == 'invoice_address_id')
                $addressId = (int)$this->invoice_address_id;
            else
                $addressId = (int)$product->delivery_address_id; // Get delivery address of the product from the cart
            if (!JeproshopAddressModelAddress::addressExists($addressId))
                $addressId = null;

            $null = null;
            if ($this->_taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED)
            {
                // Here taxes are computed only once the quantity has been applied to the product price
                $price = JeproshopProductModelProduct::getStaticPrice(
                    (int)$product->product_id, false, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity, false,
                    (int)$this->customer_id ? (int)$this->customer_id : null, (int)$this->cart_id, $addressId, $null, true, true, $virtualContext
                );

                $totalEcotax = $product->ecotax * (int)$product->cart_quantity;
                $totalPrice = $price * (int)$product->cart_quantity;

                if ($withTaxes) {
                    $productTaxRate = (float)JeproshopTaxModelTax::getProductTaxRate((int)$product->product_id, (int)$addressId, $virtualContext);
                    $productEcoTaxRate = JeproshopTaxModelTax::getProductEcotaxRate((int)$addressId);

                    $totalPrice = ($totalPrice - $totalEcotax) * (1 + $productTaxRate / 100);
                    $totalEcotax = $totalEcotax * (1 + $productEcoTaxRate / 100);
                    $totalPrice = JeproshopTools::roundPrice($totalPrice + $totalEcotax, 2);
                }
            } else {
                if ($withTaxes)
                    $price = JeproshopProductModelProduct::getStaticPrice(
                        (int)$product->product_id, true, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity, false,
                        ((int)$this->customer_id ? (int)$this->customer_id : null),  (int)$this->cart_id, ((int)$addressId ? (int)$addressId : null),
                        $null, true, true, $virtualContext
                    );
                else
                    $price = JeproshopProductModelProduct::getStaticPrice(
                        (int)$product->product_id, false, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity, false,
                        ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$addressId ? (int)$addressId : null),
                        $null,  true, true, $virtualContext
                    );

                $totalPrice = JeproshopTools::roundPrice($price * (int)$product->cart_quantity, 2);
            }
            $orderTotal += $totalPrice;
        }

        $orderTotalProducts = $orderTotal;

        if ($type == JeproshopCartModelCart::ONLY_DISCOUNTS)
            $orderTotal = 0;

        // Wrapping Fees
        $wrappingFees = 0;
        if ($this->gift)
            $wrappingFees = JeproshopTools::convertPrice(JeproshopTools::roundPrice($this->getGiftWrappingPrice($withTaxes), 2), JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$this->currency_id));
        if ($type == JeproshopCartModelCart::ONLY_WRAPPING)
            return $wrappingFees;

        $orderTotalDiscount = 0;
        if (!in_array($type, array(JeproshopCartModelCart::ONLY_SHIPPING, JeproshopCartModelCart::ONLY_PRODUCTS)) && JeproshopCartRuleModelCartRule::isFeaturePublished())
        {
            // First, retrieve the cart rules associated to this "getOrderTotal"
            if ($withShipping || $type == JeproshopCartModelCart::ONLY_DISCOUNTS)
                $cartRules = $this->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_ALL);
            else
            {
                $cartRules = $this->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_REDUCTION);
                // Cart Rules array are merged manually in order to avoid doubles
                foreach ($this->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_GIFT) as $tmpCartRule){
                    $flag = false;
                    foreach ($cartRules as $cart_rule)
                        if ($tmpCartRule->cart_rule_id == $cart_rule->cart_rule_id) {
                            $flag = true;
                        }
                    if (!$flag)
                        $cart_rules[] = $tmpCartRule;
                }
            }

            $deliveryAddressId = 0;
            if (isset($products[0]))
                $deliveryAddressId = (is_null($products) ? $this->delivery_address_id : $products[0]->delivery_address_id);
            $package = array('carrier_id' => $carrierId, 'address_id' => $deliveryAddressId, 'products' => $products);

            // Then, calculate the contextual value for each one
            foreach ($cartRules as $cartRule)
            {
                // If the cart rule offers free shipping, add the shipping cost
                if (($withShipping || $type == JeproshopCartModelCart::ONLY_DISCOUNTS) && $cartRule['obj']->free_shipping)
                    $orderTotalDiscount += JeproshopTools::roundPrice($cartRule['obj']->getContextualValue($withTaxes, $virtualContext, JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_SHIPPING, ($paramProduct ? $package : null), $useCache), 2);

                // If the cart rule is a free gift, then add the free gift value only if the gift is in this package
                if ((int)$cartRule['obj']->gift_product)
                {
                    $inOrder = false;
                    if (is_null($products))
                        $inOrder = true;
                    else
                        foreach ($products as $product)
                            if ($cartRule['obj']->gift_product == $product->product_id && $cartRule['obj']->gift_product_attribute == $product->product_attribute_id)
                                $inOrder = true;

                    if ($inOrder)
                        $orderTotalDiscount += $cartRule['obj']->getContextualValue($withTaxes, $virtualContext, JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_GIFT, $package, $useCache);
                }

                // If the cart rule offers a reduction, the amount is prorated (with the products in the package)
                if ($cartRule['obj']->reduction_percent > 0 || $cartRule['obj']->reduction_amount > 0)
                    $orderTotalDiscount += JeproshopTools::roundPrice($cartRule['obj']->getContextualValue($withTaxes, $virtualContext, JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_REDUCTION, $package, $useCache), 2);
            }
            $orderTotalDiscount = min(JeproshopTools::roundPrice($orderTotalDiscount, 2), $wrappingFees + $orderTotalProducts + $shippingFees);
            $orderTotal -= $orderTotalDiscount;
        }

        if ($type == JeproshopCartModelCart::BOTH) {
            $orderTotal += $shippingFees + $wrappingFees;
        }

        if ($orderTotal < 0 && $type != JeproshopCartModelCart::ONLY_DISCOUNTS) {
            return 0;
        }

        if ($type == JeproshopCartModelCart::ONLY_DISCOUNTS) {
            return $orderTotalDiscount;
        }

        return JeproshopTools::roundPrice((float)$orderTotal, 2);
    }

    public static function replaceZeroByShopName($echo){
        return ($echo == '0' ? JeproshopSettingModelSetting::getValue('shop_name') : $echo);
    }
}

