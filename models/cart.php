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
            die(JeproshopTools::displayError(500, ''));

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
            if (JeproshopSettingModelSetting::getValue('tax_address_type') == 'invoice_address_id')
                $addressId = (int)$this->invoice_address_id;
            else
                $addressId = (int)$product->delivery_address_id; // Get delivery address of the product from the cart
            if (!JeproshopAddressModelAddress::addressExists($addressId))
                $addressId = null;

            $specificPriceOut = null;
            if ($this->_taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED)
            {
                // Here taxes are computed only once the quantity has been applied to the product price
                $price = JeproshopProductModelProduct::getStaticPrice(
                    (int)$product->product_id, false, (int)$product->product_attribute_id, 2, false, true, $product->cart_quantity,
                    (int)$this->customer_id ? (int)$this->customer_id : null, (int)$this->cart_id, $addressId, $specificPriceOut, true, true, $virtualContext
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
                        (int)$product->product_id, true, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity,
                        ((int)$this->customer_id ? (int)$this->customer_id : null),  (int)$this->cart_id, ((int)$addressId ? (int)$addressId : null),
                        $specificPriceOut, true, true, $virtualContext
                    );
                else
                    $price = JeproshopProductModelProduct::getStaticPrice(
                        (int)$product->product_id, false, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity,
                        ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$addressId ? (int)$addressId : null),
                        $specificPriceOut,  true, true, $virtualContext
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

    public static function getCustomerCarts($customerId, $withOrder = true){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_cart') . " AS cart WHERE cart." . $db->quoteName('customer_id') . " = " . (int)$customerId;
        $query .= (!$withOrder ? "AND cart_id NOT IN (SELECT cart_id FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord )" : "") . " ORDER BY cart.";
        $query .= $db->quoteName('date_add') . " DESC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Return useful information for cart
     *
     * @param null $langId
     * @param bool $refresh
     * @return array Cart details
     */
    public function getSummaryDetails($langId = null, $refresh = false){
        $context = JeproshopContext::getContext();
        $app = JFactory::getApplication();
        if (!$langId)
            $langId = $context->language->lang_id;

        $delivery = new JeproshopAddressModelAddress((int)$this->delivery_address_id);
        $invoice = new JeproshopAddressModelAddress((int)$this->invoice_address_id);

        // New layout system with personalization fields
        $formattedAddresses = array(
            'delivery' => JeproshopAddressFormatModelAddressFormat::getFormattedLayoutData($delivery),
            'invoice' => JeproshopAddressFormatModelAddressFormat::getFormattedLayoutData($invoice)
        );

        $baseTotalTaxInc = $this->getOrderTotal(true);
        $baseTotalTaxExc = $this->getOrderTotal(false);

        $totalTax = $baseTotalTaxInc - $baseTotalTaxExc;

        if ($totalTax < 0){ $totalTax = 0; }

        $currency = new JeproshopCurrencyModelCurrency($this->currency_id);

        $products = $this->getProducts($refresh);
        $giftProducts = array();
        $cartRules = $this->getCartRules();
        $totalShipping = $this->getTotalShippingCost();
        $totalShippingTaxExc = $this->getTotalShippingCost(null, false);
        $totalProductsWt = $this->getOrderTotal(true, JeproshopCartModelCart::ONLY_PRODUCTS);
        $totalProducts = $this->getOrderTotal(false, JeproshopCartModelCart::ONLY_PRODUCTS);
        $totalDiscounts = $this->getOrderTotal(true, JeproshopCartModelCart::ONLY_DISCOUNTS);
        $totalDiscountsTaxExc = $this->getOrderTotal(false, JeproshopCartModelCart::ONLY_DISCOUNTS);

        // The cart content is altered for display
        foreach ($cartRules as &$cartRule){
            // If the cart rule is automatic (without any code) and include free shipping, it should not be displayed as a cart rule but only set the shipping cost to 0
            if ($cartRule->free_shipping && (empty($cartRule->code) || preg_match('/^'. JeproshopCartRuleModelCartRule::JEPROSHOP_BO_ORDER_CODE_PREFIX.'[0-9]+/', $cartRule->code))){
                $cartRule->value_real -= $totalShipping;
                $cartRule->value_tax_exc -= $totalShippingTaxExc;
                $cartRule->value_real = JeproshopTools::roundPrice($cartRule->value_real, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                $cartRule->value_tax_exc = JeproshopTools::roundPrice($cartRule->value_tax_exc, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                if ($totalDiscounts > $cartRule->value_real)
                    $totalDiscounts -= $totalShipping;
                if ($totalDiscountsTaxExc > $cartRule->value_tax_exc)
                    $totalDiscountsTaxExc -= $totalShippingTaxExc;

                // Update total shipping
                $totalShipping = 0;
                $totalShippingTaxExc = 0;
            }

            if ($cartRule->gift_product) {
                foreach ($products as $key => &$product) {
                    if (empty($product->gift) && $product->product_id == $cartRule->gift_product && $product->product_attribute_id == $cartRule->gift_product_attribute) {
                        // Update total products
                        $totalProducts_wt = JeproshopTools::roundPrice($totalProductsWt - $product->price_with_tax, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $totalProducts = JeproshopTools::roundPrice($totalProducts - $product->price, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);

                        // Update total discounts
                        $totalDiscounts = JeproshopTools::roundPrice($totalDiscounts - $product->price_with_tax, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $totalDiscountsTaxExc = JeproshopTools::roundPrice($totalDiscountsTaxExc - $product->price, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);

                        // Update cart rule value
                        $cartRule->value_real = JeproshopTools::roundPrice($cartRule->value_real - $product->price_with_tax, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $cartRule->value_tax_exc = JeproshopTools::roundPrice($cartRule->value_tax_exc - $product->price, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);

                        // Update product quantity
                        $product->total_wt = JeproshopTools::roundPrice($product->total_wt - $product->price_with_tax, (int)$currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $product->total = JeproshopTools::roundPrice($product->total - $product->price, (int)$currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $product->cart_quantity--;

                        if (!$product->cart_quantity)
                            unset($products[$key]);

                        // Add a new product line
                        $giftProduct = $product;
                        $giftProduct->cart_quantity = 1;
                        $giftProduct->price = 0;
                        $giftProduct->price_with_tax = 0;
                        $giftProduct->total_wt = 0;
                        $giftProduct->total = 0;
                        $giftProduct->gift = true;
                        $giftProducts[] = $giftProduct;

                        break; // One gift product per cart rule
                    }
                }
            }
        }

        foreach ($cartRules as $key => &$cartRule)
            if ($cartRule->value_real == 0)
                unset($cartRules[$key]);

        $summary = new JObject();

        $summary->set('delivery', $delivery);
        $summary->set('delivery_state', JeproshopStateModelState::getNameByStateId($delivery->state_id));
        $summary->set('invoice', $invoice);
        $summary->set('invoice_state', JeproshopStateModelState::getNameByStateId($invoice->state_id));
        $summary->set('formattedAddresses', $formattedAddresses);
        $summary->set('products', array_values($products));
        $summary->set('gift_products', $giftProducts);
        $summary->set('discounts', array_values($cartRules));
        $summary->set('is_virtual_cart', (int)$this->isVirtualCart());
        $summary->set('total_discounts', $totalDiscounts);
        $summary->set('total_discounts_tax_exc', $totalDiscountsTaxExc);
        $summary->set('total_wrapping', $this->getOrderTotal(true, JeproshopCartModelCart::ONLY_WRAPPING));
        $summary->set('total_wrapping_tax_exc', $this->getOrderTotal(false, JeproshopCartModelCart::ONLY_WRAPPING));
        $summary->set('total_shipping', $totalShipping);
        $summary->set('total_shipping_tax_exc', $totalShippingTaxExc);
        $summary->set('total_products_wt', $totalProductsWt);
        $summary->set('total_products', $totalProducts);
        $summary->set('total_price', $baseTotalTaxInc);
        $summary->set('total_tax', $totalTax);
        $summary->set('total_price_without_tax', $baseTotalTaxExc);
        $summary->set('is_multi_address_delivery', $this->isMultiAddressDelivery() || ((int)$app->input->get('multi-shipping') == 1));
        $summary->set('free_ship', $totalShipping ? 0 : 1);
        $summary->set('carrier', new JeproshopCarrierModelCarrier($this->carrier_id, $langId));

        return $summary;
    }

    /**
     * Check if cart contains only virtual products
     * @return bool true if is a virtual cart or false
     */
    public function isVirtualCart(){
        if (!JeproshopProductDownloadModelProductDownload::isFeaturePublished()){ return false; }

        if (!isset(self::$_isVirtualCart[$this->cart_id])){
            $products = $this->getProducts();
            if (!count($products))
                return false;

            $is_virtual = 1;
            foreach ($products as $product){
                if (empty($product->is_virtual))
                    $is_virtual = 0;
            }
            self::$_isVirtualCart[$this->cart_id] = (int)$is_virtual;
        }
        return self::$_isVirtualCart[$this->cart_id];
    }

    /**
     * Return cart products
     *
     * @result array Products
     * @param bool $refresh
     * @param bool $productId
     * @param null $countryId
     * @return array|null
     */
    public function getProducts($refresh = false, $productId = false, $countryId = null){
        if (!$this->cart_id)
            return array();
        // Product cache must be strictly compared to NULL, or else an empty cart will add dozens of queries
        if ($this->_products !== null && !$refresh){
            // Return product row with specified ID if it exists
            if (is_int($productId)){
                foreach ($this->_products as $product) {
                    if ($product->product_id == $productId) {
                        return array($product);
                    }
                }
                return array();
            }
            return $this->_products;
        }

        $db = JFactory::getDBO();
        $select = $leftJoin = "";

        if (JeproshopCustomization::isFeaturePublished()) {
            $select .= (', customization.' . $db->quoteName('customization_id') . ', customization.' . $db->quoteName('quantity') . ' AS customization_quantity');
            $leftJoin .= " LEFT JOIN " . $db->quoteName('#__jeproshop_customization') . " AS customization ON (product." . $db->quoteName('product_id');
            $leftJoin .= " = customization." . $db->quoteName('product_id') . " AND cart_product." . $db->quoteName('product_attribute_id') . " = customization.";
            $leftJoin .= $db->quoteName('product_attribute_id') . " AND customization." . $db->quoteName('cart_id') . " = " .(int)$this->cart_id . ") ";
        } else {
            $select .= ', NULL AS customization_quantity, NULL AS customization_id';
        }

        if (JeproshopCombinationModelCombination::isFeaturePublished()){
            $select .= ", product_attribute_shop." . $db->quoteName('price') . " AS price_attribute, product_attribute_shop." . $db->quoteName('ecotax');
            $select .= " AS ecotax_attr, IF (IFNULL(product_attribute." . $db->quoteName('reference') . ", '') = '', product." . $db->quoteName('reference');
            $select .= ", product_attribute." . $db->quoteName('reference') . ") AS reference, (product." . $db->quoteName('weight') . " + product_attribute.";
            $select .= $db->quoteName('weight') . ") weight_attribute, IF (IFNULL(product_attribute." . $db->quoteName('ean13') . ", '') = '', product.";
            $select .= $db->quoteName('ean13') . ", product_attribute." . $db->quoteName('ean13') . ") AS ean13, IF (IFNULL(product_attribute." . $db->quoteName('upc');
            $select .= ", '') = '', product." . $db->quoteName('upc') . ", product_attribute." . $db->quoteName('upc') . ") AS upc, product_attribute_image." ;
            $select .= $db->quoteName('image_id') . " AS product_attribute_id_image_id, image_lang." . $db->quoteName('legend') . " AS product_attribute_image_legend,";
            $select .= " IFNULL(product_attribute_shop." . $db->quoteName('minimal_quantity') . ", product_shop." . $db->quoteName('minimal_quantity') . ") as minimal_quantity ";

            $leftJoin .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ON (product_attribute.";
            $leftJoin .= $db->quoteName('product_attribute_id') . " = cart_product." . $db->quoteName('product_attribute_id') . ") LEFT JOIN ";
            $leftJoin .= $db->quoteName('#__jeproshop_product_attribute_shop') . " AS product_attribute_shop ON (product_attribute_shop.";
            $leftJoin .= $db->quoteName('shop_id') . " = cart_product." . $db->quoteName('shop_id') . " AND product_attribute_shop." ;
            $leftJoin .= $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") LEFT JOIN ";
            $leftJoin .= $db->quoteName('#__jeproshop_product_attribute_image') . " AS product_attribute_image ON (product_attribute_image." ;
            $leftJoin .= $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") LEFT JOIN ";
            $leftJoin .= $db->quoteName('#__jeproshop_image_lang')  . " AS image_lang ON (image_lang." . $db->quoteName('image_id') . " = product_attribute_image.";
            $leftJoin .= $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " . (int)$this->lang_id . ") ";
        }else {
            $select .= ", product." . $db->quoteName('reference') . " AS reference, product." . $db->quoteName('ean13') . ", product.";
            $select .= $db->quoteName('upc') . " AS upc, product_shop." . $db->quoteName('minimal_quantity') . " AS minimal_quantity";
        }

        $query = "SELECT cart_product." . $db->quoteName('product_attribute_id') . ", cart_product." . $db->quoteName('product_id') . ", cart_product.";
        $query .= $db->quoteName('quantity') . " AS cart_quantity, cart_product." . $db->quoteName('shop_id') . ", product_lang." .$db->quoteName('name');
        $query .= ", product." . $db->quoteName('is_virtual') . ", product_lang." . $db->quoteName('short_description') . ", product_lang." . $db->quoteName('available_now');
        $query .= ", product_lang." . $db->quoteName('available_later') . ", product_shop." . $db->quoteName('default_category_id') . ", product.";
        $query .= $db->quoteName('supplier_id') . ", product." . $db->quoteName('manufacturer_id') . ", product_shop." . $db->quoteName('on_sale') .", product_shop.";
        $query .= $db->quoteName('ecotax') . ", product_shop." . $db->quoteName('additional_shipping_cost') . ", product_shop." . $db->quoteName('available_for_order');
        $query .= ", product_shop." . $db->quoteName('price') . ", product_shop." . $db->quoteName('published') . ", product_shop." . $db->quoteName('unity');
        $query .= ", product_shop." . $db->quoteName('unit_price_ratio') . ", stock." . $db->quoteName('quantity') . " AS quantity_available, product." . $db->quoteName('width');;
        $query .= ", product." . $db->quoteName('height') . ", product." . $db->quoteName('depth') . ", stock." . $db->quoteName('out_of_stock') . ", product.";
        $query .= $db->quoteName('weight') . ", product." . $db->quoteName('date_add') . ", product." . $db->quoteName('date_upd') . ", IFNULL(stock.quantity, 0) as quantity, ";
        $query .= "product_lang." . $db->quoteName('link_rewrite') . ", category_lang." . $db->quoteName('link_rewrite') . " AS category, CONCAT(LPAD(cart_product.";
        $query .= $db->quoteName('product_id') . ", 10, 0), LPAD(IFNULL(cart_product." . $db->quoteName('product_attribute_id') . ", 0), 10, 0), IFNULL(cart_product.";
        $query .= $db->quoteName('delivery_address_id') . ", 0)) AS unique_id, cart_product.delivery_address_id, product_shop." . $db->quoteName('wholesale_price');
        $query .= ", product_shop.advanced_stock_management, product_supplier.product_supplier_reference supplier_reference, IFNULL(specific_price." . $db->quoteName('reduction_type');
        $query .= ", 0) AS reduction_type " . $select . " FROM " . $db->quoteName('#__jeproshop_cart_product') . " AS cart_product LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ";
        $query .= " ON (product." . $db->quoteName('product_id') . " = cart_product." . $db->quoteName('product_id') . ") INNER JOIN " . $db->quoteName('#__jeproshop_product_shop') ;
        $query .= " AS product_shop ON (product_shop." . $db->quoteName('shop_id') . " = cart_product." . $db->quoteName('shop_id') . " AND product_shop." . $db->quoteName('product_id');
        $query .= " = product." . $db->quoteName('product_id') . ") " . $leftJoin . " LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON (product." . $db->quoteName('product_id');
        $query .= " = product_lang." . $db->quoteName('product_id') . "	AND product_lang." . $db->quoteName('lang_id') . " = " . (int)$this->lang_id ;
        $query .= JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang', 'cart_product.shop_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang');
        $query .= " AS category_lang ON(product_shop." . $db->quoteName('default_category_id') . " = category_lang." . $db->quoteName('category_id') . " AND category_lang." . $db->quoteName('lang_id');
        $query .= " = " . (int)$this->lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang', 'cart_product.shop_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_supplier');
        $query .= " AS product_supplier ON (product_supplier." . $db->quoteName('product_id') . " = cart_product." . $db->quoteName('product_id') . " AND product_supplier.";
        $query .= $db->quoteName('product_attribute_id') . " = cart_product." . $db->quoteName('product_attribute_id') . " AND product_supplier." . $db->quoteName('supplier_id') ;
        $query .= " = product." . $db->quoteName('supplier_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_specific_price') . " AS specific_price ON (specific_price.";
        $query .= $db->quoteName('product_id') . " = cart_product." . $db->quoteName('product_id') . ") " . JeproshopProductModelProduct::sqlStock('cart_product'); // AND 'sp.`id_shop` = cart_product.`id_shop`

        // @todo test if everything is ok, then re-factorise call of this method
        //$sql->join(Product::sqlStock('cart_product', 'cart_product'));

        $query .= " WHERE cart_product." . $db->quoteName('cart_id') . " = " .(int)$this->cart_id;
        if ($productId) {
            $query .= " AND cart_product." . $db->quoteName('product_id') . " = " . (int)$productId;
        }
        $query .= " AND product." . $db->quoteName('product_id') . " IS NOT NULL GROUP BY unique_id ORDER BY cart_product." . $db->quoteName('date_add');
        $query .= ", product." . $db->quoteName('product_id') . ", cart_product." . $db->quoteName('product_attribute_id') . " ASC";

        $db->setQuery($query);
        $result = $db->loadObjectList();

        // Reset the cache before the following return, or else an empty cart will add dozens of queries
        $productsIds = array();
        $productAttributeIds = array();
        if ($result) {
            foreach ($result as $row) {
                $productsIds[] = $row->product_id;
                $productAttributeIds[] = $row->product_attribute_id;
            }
        }
        // Thus you can avoid one query per product, because there will be only one query for all the products of the cart
        JeproshopProductModelProduct::cacheProductsFeatures($productsIds);
        JeproshopCartModelCart::cacheSomeAttributesLists($productAttributeIds, $this->lang_id);

        $this->_products = array();
        if (empty($result))
            return array();

        $cartShopContext = JeproshopContext::getContext()->cloneContext();
        foreach ($result as &$row){
            if (isset($row->ecotax_attr) && $row->ecotax_attr > 0){
                $row->ecotax = (float)$row->ecotax_attr;
            }
            $row->stock_quantity = (int)$row->quantity;
            // for compatibility with 1.2 themes
            $row->quantity = (int)$row->cart_quantity;

            if (isset($row->product_attribute_id) && (int)$row->product_attribute_id && isset($row->weight_attribute)) {
                $row->weight = (float)$row->weight_attribute;
            }

            if (JeproshopSettingModelSetting::getValue('tax_address_type') == 'invoice_address_id')
                $addressId = (int)$this->invoice_address_id;
            else
                $addressId = (int)$row->delivery_address_id;

            if (!JeproshopAddressModelAddress::addressExists($addressId))
                $addressId = null;

            if ($cartShopContext->shop->shop_id != $row->shop_id)
                $cartShopContext->shop = new JeproshopShopModelShop((int)$row->shop_id);

            $specificPriceOutput = null;
            $specificPrice = null;

            if ($this->_taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED){
                $row->price = JeproshopProductModelProduct::getStaticPrice(
                    (int)$row->product_id, false, (isset($row->product_attribute_id) ? (int)$row->product_attribute_id : null), 2,  false, true,
                    (int)$row->cart_quantity, ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id,
                    ((int)$addressId ? (int)$addressId : null), $specificPriceOutput, true, true, $cartShopContext
                ); // Here taxes are computed only once the quantity has been applied to the product price

                $row->price_with_tax = JeproshopProductModelProduct::getStaticPrice(
                    (int)$row->product_id, true, (isset($row->product_attribute_id) ? (int)$row->product_attribute_id : null), 2, false, true,
                    (int)$row->cart_quantity, ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id,
                    ((int)$addressId ? (int)$addressId : null), $specificPrice, true, true, $cartShopContext
                );

                $taxRate = JeproshopTaxModelTax::getProductTaxRate((int)$row->product_id, (int)$addressId);

                $row->total_with_tax = JeproshopTools::roundPrice($row->price * (float)$row->cart_quantity * (1 + (float)$taxRate / 100), 2);
                $row->total = $row->price * (int)$row->cart_quantity;
            } else {
                $row->price = JeproshopProductModelProduct::getStaticPrice(
                    (int)$row->product_id, false, (int)$row->product_attribute_id, 2, false, true, $row->cart_quantity,
                    ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$addressId ? (int)$addressId : null),
                    $specificPriceOutput, true, true, $cartShopContext
                );

                $row->price_with_tax = JeproshopProductModelProduct::getStaticPrice(
                    (int)$row->product_id, true,  (int)$row->product_attribute_id, 2, false, true, $row->cart_quantity,
                    ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$addressId ? (int)$addressId : null),
                    $specificPrice, true,  true, $cartShopContext
                );

                // In case when you use QuantityDiscount, getPriceStatic() can be return more of 2 decimals
                $row->price_with_tax = JeproshopTools::roundPrice($row->price_with_tax, 2);
                $row->total_with_tax = $row->price_with_tax * (int)$row->cart_quantity;
                $row->total = JeproshopTools::roundPrice($row->price * (int)$row->cart_quantity, 2);
                $row->short_description = JeproshopTools::nl2br($row->short_description);
            }

            if (!isset($row->product_attribute_id_image_id) || $row->product_attribute_id_image_id == 0){
                $cacheKey = 'jeproshop_cart_get_products_product_attribute_id_image_id_'.(int)$row->product_id .'_'.(int)$this->lang_id.'_'.(int)$row->shop_id;
                if (!JeproshopCache::isStored($cacheKey)) {
                    $db = JFactory::getDBO();

                    $query = "SELECT image_shop." . $db->quoteName('image_id') . " AS image_id, image_lang." . $db->quoteName('legend') .  " FROM ";
                    $query .= $db->quoteName('#__jeproshop_image') . " AS image JOIN " . $db->quoteName('#__jeproshop_image_shop') . " AS image_shop ON (";
                    $query .= " image.image_id = image_shop.image_id AND image_shop.cover=1 AND image_shop.shop_id = " .(int)$row->shop_id . ") LEFT JOIN ";
                    $query .= $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image_shop." . $db->quoteName('image_id') . " = image_lang.";
                    $query .= $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " .(int)$this->lang_id . ") WHERE image.";
                    $query .= $db->quoteName('product_id') . " = " .(int)$row->product_id . " AND image_shop." . $db->quoteName('cover') . " = 1";

                    $db->setQuery($query);
                    $row2 = $db->loadObject();
                    JeproshopCache::store($cacheKey, $row2);
                }
                $row2 = JeproshopCache::retrieve($cacheKey);
                if (!$row2) {
                    $row2 = new JObject();
                    $row2->image_id = false;
                    $row2->legend = false;
                }else {
                    //$row = (!is_array($row) ? array($row) : $row);
                    $row = (object)array_merge((array)$row, (array)$row2);
                }
            } else {
                $row->image_id = $row->product_attribute_id_image_id;
                $row->legend = $row->product_attribute_image_legend;
            }

            $row->reduction_applies = ($specificPriceOutput && (float)$specificPriceOutput->reduction);
            $row->quantity_discount_applies = ($specificPriceOutput && $row->cart_quantity >= (int)$specificPriceOutput->from_quantity);
            $row->image_id = JeproshopProductModelProduct::defineProductImage($row, $this->lang_id);
            $row->allow_out_of_sp = JeproshopProductModelProduct::isAvailableWhenOutOfStock($row->out_of_stock);
            $row->features = JeproshopProductModelProduct::getStaticFeatures((int)$row->product_id);

            if (array_key_exists($row->product_attribute_id .'_'.$this->lang_id, self::$_attributesLists)) {
                $row = (object)array_merge((array)$row, (array)self::$_attributesLists[$row->product_attribute_id . '_' . $this->lang_id]);
            }
            $row = JeproshopProductModelProduct::getTaxesInformation($row, $cartShopContext);

            $this->_products[] = $row;
        }

        return $this->_products;
    }

    public static function cacheSomeAttributesLists($productAttributeList, $langId) {
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return; }

        $productAttributeImplode = array();

        foreach ($productAttributeList as $productAttributeId)
            if ((int)$productAttributeId && !array_key_exists($productAttributeId.'_'.$langId, self::$_attributesLists)){
                $productAttributeImplode[] = (int)$productAttributeId;
                $attribute = new JObject();
                $attribute->attributes = '';
                $attribute->attributes_small = '';
                self::$_attributesLists[(int)$productAttributeId.'_'.$langId] = $attribute;
            }

        if (!count($productAttributeImplode))   return;

        $db = JFactory::getDBO();

        $query = "SELECT product_attribute_combination." . $db->quoteName('product_attribute_id') . ", attribute_group_lang." . $db->quoteName('public_name');
        $query .= " AS public_group_name, attribute_lang." . $db->quoteName('name') . " AS attribute_name FROM " . $db->quoteName('#__jeproshop_product_attribute_combination');
        $query .= " AS product_attribute_combination LEFT JOIN " . $db->quoteName('#__jeproshop_attribute') . " AS attribute ON (attribute." . $db->quoteName('attribute_id');
        $query .= " = product_attribute_combination." . $db->quoteName('attribute_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ";
        $query .= " ON (attribute_group." . $db->quoteName('attribute_group_id') . " = attribute." . $db->quoteName('attribute_group_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang');
        $query .= " AS attribute_lang ON ( attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id') . " AND attribute_lang.";
        $query .= $db->quoteName('lang_id') . " = " .(int)$langId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON ( ";
        $query .= "attribute_group." . $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') . " AND attribute_group_lang.";
        $query .= $db->quoteName('lang_id') . " = " . (int)$langId . ") WHERE product_attribute_combination." . $db->quoteName('product_attribute_id') . " IN (" ;
        $query .= implode(',', $productAttributeImplode) . ") ORDER BY attribute_group_lang." . $db->quoteName('public_name') . " ASC ";

        $db->setQuery($query);

        $result = $db->loadObjectList();

        foreach ($result as $row) {
            self::$_attributesLists[$row->product_attribute_id .'_'.$langId]->attributes .= $row->public_group_name .' : '.$row->attribute_name.', ';
            self::$_attributesLists[$row->product_attribute_id .'_'.$langId]->attributes_small .= $row->attribute_name . ', ';
        }

        foreach ($productAttributeImplode as $productAttributeId) {
            self::$_attributesLists[$productAttributeId.'_'.$langId]->attributes = rtrim(
                self::$_attributesLists[$productAttributeId.'_'.$langId]->attributes,
                ', '
            );

            self::$_attributesLists[$productAttributeId.'_'.$langId]->attributes_small = rtrim(
                self::$_attributesLists[$productAttributeId.'_'.$langId]->attributes_small,
                ', '
            );
        }
    }

    /**
     * Return shipping total for the cart
     *
     * @param array $deliveryOption Array of the delivery option for each address
     * @param bool $useTax
     * @param JeproshopCountryModelCountry $defaultCountry
     * @return float Shipping total
     */
    public function getTotalShippingCost($deliveryOption = null, $useTax = true, JeproshopCountryModelCountry $defaultCountry = null){
        if(isset(JeproshopContext::getContext()->cookie->country_id)){
            $defaultCountry = new JeproshopCountryModelCountry(JeproshopContext::getContext()->cookie->country_id);
        }
        if (is_null($deliveryOption)){
            $deliveryOption = $this->getDeliveryOption($defaultCountry, false, false);
        }
        $totalShipping = 0;
        $deliveryOptionList = $this->getDeliveryOptionList($defaultCountry);
        foreach ($deliveryOption as $addressId => $key){
            if (!isset($deliveryOptionList[$addressId]) || !isset($deliveryOptionList[$addressId][$key]))
                continue;
            if ($useTax)
                $totalShipping += $deliveryOptionList[$addressId][$key]->total_price_with_tax;
            else
                $totalShipping += $deliveryOptionList[$addressId][$key]->total_price_without_tax;
        }

        return $totalShipping;
    }

    public function getDeliveryOptionList(JeproshopCountryModelCountry $defaultCountry = null, $flush = false)
    {
        static $cache = null;
        if ($cache !== null && !$flush)
            return $cache;

        $deliveryOptionList = array();
        $carriersPrice = array();
        $carrierCollection = array();
        $packageList = $this->getPackageList();

        // Foreach addresses
        foreach ($packageList as $addressId => $packages) {
            // Initialize vars
            $deliveryOptionList[$addressId] = array();
            $carriersPrice[$addressId] = array();
            $commonCarriers = null;
            $bestPriceCarriers = array();
            $bestGradeCarriers = array();
            $carriersInstance = array();

            // Get country
            if ($addressId) {
                $address = new JeproshopAddressModelAddress($addressId);
                $country = new JeproshopCountryModelCountry($address->country_id);
            } else {
                $country = $defaultCountry;
            }

            // Foreach packages, get the carriers with best price, best position and best grade
            foreach ($packages as $packageId => $package) {
                // No carriers available
                if (count($package->carrier_list) == 1 && current($package->carrier_list) == 0) {
                    $cache = array();
                    return $cache;
                }

                $carriersPrice[$addressId][$packageId] = array();

                // Get all common carriers for each packages to the same address
                if (is_null($commonCarriers)) {
                    $commonCarriers = $package->carrier_list;
                } else {
                    $commonCarriers = array_intersect($commonCarriers, $package->carrier_list);
                }

                $bestPrice = null;
                $bestPriceCarrier = null;
                $bestGrade = null;
                $bestGradeCarrier = null;

                // Foreach carriers of the package, calculate his price, check if it the best price, position and grade
                foreach ($package->carrier_list as $carrierId) {
                    if (!isset($carriers_instance[$carrierId])) {
                        $carriers_instance[$carrierId] = new JeproshopCarrierModelCarrier($carrierId);
                    }
                    $priceWithTax = $this->getPackageShippingCost($carrierId, true, $country, $package->product_list);
                    $priceWithoutTax = $this->getPackageShippingCost($carrierId, false, $country, $package->product_list);
                    if (is_null($bestPrice) || $priceWithTax < $bestPrice) {
                        $bestPrice = $priceWithTax;
                        $bestPriceCarrier = $carrierId;
                    }
                    $carriers_price[$addressId][$packageId][$carrierId] = array(
                        'without_tax' => $priceWithoutTax,
                        'with_tax' => $priceWithTax);

                    $grade = $carriersInstance[$carrierId]->grade;
                    if (is_null($bestGrade) || $grade > $bestGrade) {
                        $bestGrade = $grade;
                        $bestGradeCarrierId = $carrierId;
                    }
                }

                $best_price_carriers[$packageId] = $bestPriceCarrier;
                $best_grade_carriers[$packageId] = $bestGradeCarrier;
            }

            // Reset $best_price_carrier, it's now an array
            $bestPriceCarrier = array();
            $key = '';

            // Get the delivery option with the lower price
            foreach ($bestPriceCarriers as $packageId => $carrierId) {
                $key .= $carrierId . ',';
                if (!isset($bestPriceCarrier[$carrierId])) {
                    $bestPriceCarrier[$carrierId] = new JObject();
                    $bestPriceCarrier[$carrierId]->price_with_tax = 0;
                    $bestPriceCarrier[$carrierId]->price_without_tax = 0;
                    $bestPriceCarrier[$carrierId]->package_list = array();
                    $bestPriceCarrier[$carrierId]->product_list = array();
                }

                $bestPriceCarrier[$carrierId]->price_with_tax += $carriersPrice[$addressId][$packageId][$carrierId]->with_tax;
                $bestPriceCarrier[$carrierId]->price_without_tax += $carriersPrice[$addressId][$packageId][$carrierId]->without_tax;
                $bestPriceCarrier[$carrierId]->package_list[] = $packageId;
                $bestPriceCarrier[$carrierId]->product_list = array_merge($bestPriceCarrier[$carrierId]->product_list, $packages[$packageId]->product_list);
                $bestPriceCarrier[$carrierId]->instance = $carriersInstance[$carrierId];
            }

            // Add the delivery option with best price as best price
            $deliveryOptionList[$addressId][$key] = new JObject();
            $deliveryOptionList[$addressId][$key]->carrier_list = $bestPriceCarrier;
            $deliveryOptionList[$addressId][$key]->is_best_price = true;
            $deliveryOptionList[$addressId][$key]->is_best_grade = false;
            $deliveryOptionList[$addressId][$key]->unique_carrier = (count($bestPriceCarriers) <= 1);


            // Reset $best_grade_carrier, it's now an array
            $bestGradeCarrier = array();
            $key = '';

            // Get the delivery option with the best grade
            foreach ($bestGradeCarriers as $packageId => $carrierId) {
                $key .= $carrierId . ',';
                if (!isset($bestGradeCarrier[$carrierId])) {
                    $bestGradeCarrier[$carrierId] = new JObject();
                    $bestGradeCarrier[$carrierId]->price_with_tax = 0;
                    $bestGradeCarrier[$carrierId]->price_without_tax = 0;
                    $bestGradeCarrier[$carrierId]->package_list = array();
                    $bestGradeCarrier[$carrierId]->product_list = array();
                }
                $bestGradeCarrier[$carrierId]->price_with_tax += $carriersPrice[$addressId][$packageId][$carrierId]->with_tax;
                $bestGradeCarrier[$carrierId]->price_without_tax += $carriersPrice[$addressId][$packageId][$carrierId]->without_tax;
                $bestGradeCarrier[$carrierId]->package_list[] = $packageId;
                $bestGradeCarrier[$carrierId]->product_list = array_merge($bestGradeCarrier[$carrierId]->product_list, $packages[$packageId]->product_list);
                $bestGradeCarrier[$carrierId]->instance = $carriersInstance[$carrierId];
            }

            // Add the delivery option with best grade as best grade
            if (!isset($deliveryOptionList[$addressId][$key])) {
                $deliveryOptionList[$addressId][$key] = new JObject();
                $deliveryOptionList[$addressId][$key]->carrier_list = $bestGradeCarrier;
                $deliveryOptionList[$addressId][$key]->is_best_price = false;
                $deliveryOptionList[$addressId][$key]->unique_carrier = (count($bestGradeCarrier) <= 1);
            }
            $deliveryOptionList[$addressId][$key]->is_best_grade = true;

            // Get all delivery options with a unique carrier
            if(isset($commonCarriers)){
                foreach ($commonCarriers as $carrierId) {
                    $key = '';
                    $packageList = array();
                    $productList = array();
                    $priceWithTax = 0;
                    $priceWithoutTax = 0;

                    foreach ($packages as $packageId => $package) {
                        $key .= $carrierId . ',';
                        $priceWithTax += $carriersPrice[$addressId][$packageId][$carrierId]->with_tax;
                        $priceWithoutTax += $carriersPrice[$addressId][$packageId][$carrierId]->without_tax;
                        $packageList[] = $packageId;
                        $productList = array_merge($productList, $package->product_list);
                    }

                    if (!isset($deliveryOptionList[$addressId][$key])) {
                        $deliveryOptionList[$addressId][$key] = new JObject();
                        $deliveryOptionList[$addressId][$key]->is_best_price = false;
                        $deliveryOptionList[$addressId][$key]->is_best_grade = false;
                        $deliveryOptionList[$addressId][$key]->unique_carrier = true;
                        $deliveryOptionList[$addressId][$key]->carrier_list = array(
                            $carrierId => array(
                                'price_with_tax' => $priceWithTax,
                                'price_without_tax' => $priceWithoutTax,
                                'instance' => $carriersInstance[$carrierId],
                                'package_list' => $packageList,
                                'product_list' => $productList
                            )

                        );
                    } else {
                        $deliveryOptionList[$addressId][$key]->unique_carrier = (count($deliveryOptionList[$addressId][$key]->carrier_list) <= 1);
                    }
                }
            }
        }

        $cartRules = JeproshopCartRuleModelCartRule::getCustomerCartRules(JeproshopContext::getContext()->cookie->lang_id, JeproshopContext::getContext()->cookie->customer_id, true, true, false, $this);

        $freeCarriersRules = array();
        foreach ($cartRules as $cartRule) {
            if ($cartRule->free_shipping && $cartRule->carrier_restriction) {
                $cartRule = new JeproshopCartRuleModelCartRule((int)$cartRule->cart_rule_id);
                if (JeproshopTools::isLoadedObject($cartRule, 'cart_rule_id')) {
                    $carriers = $cartRule->getAssociatedRestrictions('carrier', true, false);
                    if (is_array($carriers) && count($carriers) && isset($carriers['selected'])) {
                        foreach ($carriers['selected'] as $carrier) {
                            if (isset($carrier->carrier_id) && $carrier->carrier_id) {
                                $free_carriers_rules[] = (int)$carrier->carrier_id;
                            }
                        }
                    }
                }
            }
        }

        // For each delivery options :
        //    - Set the carrier list
        //    - Calculate the price
        //    - Calculate the average position
        foreach ($deliveryOptionList as $addressId => $deliveryOption) {
            foreach ($deliveryOption as $key => $value) {
                $totalPriceWithTax = 0;
                $totalPriceWithoutTax = 0;
                $totalPriceWithoutTaxWithRules = 0;
                $position = 0;
                foreach ($value->carrier_list as $carrierId => $data) {
                    $totalPriceWithTax += $data->price_with_tax;
                    $totalPriceWithoutTax += $data->price_without_tax;
                    $totalPriceWithoutTaxWithRules = (in_array($carrierId, $freeCarriersRules)) ? 0 : $totalPriceWithoutTax;

                    if (!isset($carrierCollection[$carrierId]))
                        $carrierCollection[$carrierId] = new JeproshopCarrierModelCarrier($carrierId);
                    $deliveryOptionList[$addressId][$key]->carrier_list[$carrierId]->instance = $carrierCollection[$carrierId];

                    if (file_exists(COM_JEPROSHOP_CARRIER_IMAGE_DIR . $carrierId . '.jpg'))
                        $deliveryOptionList[$addressId][$key]->carrier_list[$carrierId]->logo = COM_JEPROSHOP_CARRIER_IMAGE_DIR . $carrierId . '.jpg';
                    else
                        $deliveryOptionList[$addressId][$key]->carrier_list[$carrierId]->logo = false;

                    $position += $carrierCollection[$carrierId]->position;
                }
                $deliveryOptionList[$addressId][$key]->total_price_with_tax = $totalPriceWithTax;
                $deliveryOptionList[$addressId][$key]->total_price_without_tax = $totalPriceWithoutTax;
                $deliveryOptionList[$addressId][$key]->is_free = !$totalPriceWithoutTaxWithRules ? true : false;
                //$countCarriers = (count($value->carrier_list) ? count($value->carrier_list) : 1)
                $deliveryOptionList[$addressId][$key]->position = count($value->carrier_list) ? $position /count($value->carrier_list) : 1 ;
            }
        }

        // Sort delivery option list
        foreach ($deliveryOptionList as &$array)
            uasort($array, array('JeproshopCartModelCart', 'sortDeliveryOptionList'));

        $cache = $deliveryOptionList;
        return $deliveryOptionList;
    }

    /**
     * Get the delivery selected, or if no delivery option was selected, the cheapest option for each address
     * @param null $defaultCountry
     * @param bool $doNotAutoSelectOptions
     * @param bool $useCache
     * @return array delivery option
     */
    public function getDeliveryOption($defaultCountry = null, $doNotAutoSelectOptions = false, $useCache = true){
        static $cache = array();
        $cacheKey = (int)(is_object($defaultCountry) ? $defaultCountry->country_id : 0).'_'.(int)$doNotAutoSelectOptions;
        if (isset($cache[$cacheKey]) && $useCache){
            return $cache[$cacheKey];
        }
        $deliveryOptionList = $this->getDeliveryOptionList($defaultCountry);

        // The delivery option was selected
        if (isset($this->delivery_option) && $this->delivery_option != '') {
            $deliveryOption = JeproshopTools::unSerialize($this->delivery_option);
            $validated = true;
            foreach ($deliveryOption as $addressId => $key) {
                if (!isset($deliveryOptionList[$addressId][$key])) {
                    $validated = false;
                    break;
                }
            }

            if ($validated){
                $cache[$cacheKey] = $deliveryOption;
                return $deliveryOption;
            }
        }

        if ($doNotAutoSelectOptions){ return false; }

        // No delivery option selected or delivery option selected is not valid, get the better for all options
        $deliveryOption = array();
        foreach ($deliveryOptionList as $addressId => $options){
            foreach ($options as $key => $option) {
                if (JeproshopSettingModelSetting::getValue('default_carrier') == -1 && $option->is_best_price) {
                    $delivery_option[$addressId] = $key;
                    break;
                } elseif (JeproshopSettingModelSetting::getValue('default_carrier') == -2 && $option->is_best_grade) {
                    $deliveryOption[$addressId] = $key;
                    break;
                } elseif ($option->unique_carrier && in_array(JeproshopSettingModelSetting::getValue('default_carrier'), array_keys($option->carrier_list))) {
                    $delivery_option[$addressId] = $key;
                    break;
                }
            }

            reset($options);
            if (!isset($deliveryOption[$addressId]))
                $deliveryOption[$addressId] = key($options);
        }

        $cache[$cacheKey] = $deliveryOption;

        return $deliveryOption;
    }

    public function getPackageList($flush = false){
        static $cache = array();
        if (isset($cache[(int)$this->cart_id . '_' .(int)$this->delivery_address_id]) && $cache[(int)$this->cart_id.'_'.(int)$this->delivery_address_id] !== false && !$flush) {
            return $cache[(int)$this->cart_id . '_' . (int)$this->delivery_address_id];
        }
        $productList = $this->getProducts();

        // Step 1 : Get product information (warehouse_list and carrier_list), count warehouse
        // Determine the best warehouse to determine the packages
        // For that we count the number of time we can use a warehouse for a specific delivery address
        $warehouseCountByAddress = array();
        $warehouseCarrierList = array();

        $stockManagementActive = JeproshopSettingModelSetting::getValue('advanced_stock_management');

        foreach ($productList as &$product){
            if ((int)$product->delivery_address_id == 0){
                $product->delivery_address_id = (int)$this->delivery_address_id;
            }

            if (!isset($warehouseCountByAddress[$product->delivery_address_id])){
                $warehouseCountByAddress[$product->delivery_address_id] = array();
            }

            $product->warehouse_list = array();

            if ($stockManagementActive && ((int)$product->advanced_stock_management == 1 || JeproshopProductPack::usesAdvancedStockManagement((int)$product->product_id))) {
                $warehouseList = JeproshopWarehouseModelWarehouse::getProductWarehouseList($product->product_id, $product->roduct_attribute_id, $this->shop_id);
                if (count($warehouseList) == 0)
                    $warehouseList = JeproshopWarehouseModelWarehouse::getProductWarehouseList($product->product_id, $product->roduct_attribute_id);
                // Does the product is in stock ?
                // If yes, get only warehouse where the product is in stock

                $warehouseInStock = array();
                $manager = JeproshopStockManagerFactory::getManager();

                foreach ($warehouseList as $key => $warehouse){
                    $productRealQuantities = $manager->getProductRealQuantities(
                        $product->product_id,
                        $product->product_attribute_id,
                        array($warehouse->warehouse_id),
                        true
                    );

                    if ($productRealQuantities > 0 || JeproshopProductPack::isPack((int)$product->product_id)){
                        $warehouseInStock[] = $warehouse;
                    }
                }

                if (!empty($warehouseInStock)){
                    $warehouseList = $warehouseInStock;
                    $product->in_stock = true;
                }else {
                    $product->in_stock = false;
                }
            }
            else{
                //simulate default warehouse
                $warehouseList = array();
                $product->in_stock = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($product->product_id, $product->product_attribute_id) > 0;
            }

            foreach ($warehouseList as $warehouse){
                if (!isset($warehouseCarrierList[$warehouse->warehouse_id])){
                    $warehouseObject = new JeproshopWarehouseModelWarehouse($warehouse->warehouse_id);
                    $warehouseCarrierList[$warehouse->warehouse_id] = $warehouseObject->getCarriers();
                }

                $product->warehouse_list[] = $warehouse->warehouse_id;
                if (!isset($warehouseCountByAddress[$product->delivery_address_id][$warehouse->warehouse_id]))
                    $warehouseCountByAddress[$product->deliverby_address_id][$warehouse->warehouse_id] = 0;

                $warehouseCountByAddress[$product->deliverby_address_id][$warehouse->warehouse_id]++;
            }
        }
        unset($product);

        arsort($warehouseCountByAddress);

        // Step 2 : Group product by warehouse
        $groupedByWarehouse = array();
        foreach ($productList as &$product){
            if (!isset($groupedByWarehouse[$product->delivery_address_id])) {
                $groupedByWarehouse[$product->delivery_address_id] = new JObject();
                $groupedByWarehouse[$product->delivery_address_id]->in_stock = array();
                $groupedByWarehouse[$product->delivery_address_id]->out_of_stock = array();
            }

            $product->carrier_list = array();
            $warehouseId = 0;
            foreach ($warehouseCountByAddress[$product->delivery_address_id] as $warehouseId => $val){
                if (in_array((int)$warehouseId, $product->warehouse_list)){
                    $product->carrier_list = array_merge($product->carrier_list, JeproshopCarrierModelCarrier::getAvailableCarrierList(new JeproshopProductModelProduct($product->product_id), $warehouseId, $product->deliverby_address_id, null, $this));
                    if (!$warehouseId)
                        $warehouseId = (int)$warehouseId;
                }
            }

            if (!isset($groupedByWarehouse[$product->delivery_address_id]->in_stock[$warehouseId])) {
                $groupedByWarehouse[$product->delivery_address_id]->in_stock[$warehouseId] = array();
                $groupedByWarehouse[$product->delivery_address_id]->out_of_stock[$warehouseId] = array();
            }

            if (!$this->allow_separated_package)
                $key = 'in_stock';
            else
                $key = $product->in_stock ? 'in_stock' : 'out_of_stock';

            if (empty($product->carrier_list))
                $product->carrier_list = array(0);

            $groupedByWarehouse[$product->delivery_address_id]->{$key}[$warehouseId][] = $product;
        }
        unset($product);

        // Step 3 : grouped product from grouped_by_warehouse by available carriers
        $groupedByCarriers = array();
        foreach ($groupedByWarehouse as $deliveryAddressId => $productsInStockList) {
            if (!isset($groupedByCarriers[$deliveryAddressId]))
                $groupedByCarriers[$deliveryAddressId] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            foreach ($productsInStockList as $key => $warehouseList){
                if (!isset($groupedByCarriers[$deliveryAddressId][$key]))
                    $groupedByCarriers[$deliveryAddressId][$key] = array();
                foreach ($warehouseList as $warehouseId => $productList){
                    if (!isset($groupedByCarriers[$deliveryAddressId][$key][$warehouseId]))
                        $groupedByCarriers[$deliveryAddressId][$key][$warehouseId] = array();
                    foreach ($productList as $product){
                        $packageCarriersKey = implode(',', $product->carrier_list);

                        if (!isset($groupedByCarriers[$deliveryAddressId][$key][$warehouseId][$packageCarriersKey])) {
                            $groupedByCarriers[$deliveryAddressId][$key][$warehouseId][$packageCarriersKey] =new JObject();
                            $groupedByCarriers[$deliveryAddressId][$key][$warehouseId][$packageCarriersKey]->product_list = array();
                            $groupedByCarriers[$deliveryAddressId][$key][$warehouseId][$packageCarriersKey]->carrier_list = $product->carrier_list;
                            $groupedByCarriers[$deliveryAddressId][$key][$warehouseId][$packageCarriersKey]->warehouse_list = $product->warehouse_list;

                        }
                        $groupedByCarriers[$deliveryAddressId][$key][$warehouseId][$packageCarriersKey]->product_list[] = $product;
                    }
                }
            }
        }

        $packageList = array();
        // Step 4 : merge product from grouped_by_carriers into $package to minimize the number of package
        foreach ($groupedByCarriers as $deliveryAddressId => $productsInStockList){
            if (!isset($package_list[$deliveryAddressId])) {
                $packageList[$deliveryAddressId] = new JObject();
                $packageList[$deliveryAddressId]->in_stock = array();
                $packageList[$deliveryAddressId]->out_of_stock = array();
            }

            foreach ($productsInStockList as $key => $warehouseList){
                if (!isset($package_list[$deliveryAddressId][$key]))
                    $package_list[$deliveryAddressId][$key] = array();
                // Count occurrence of each carriers to minimize the number of packages
                $carrierCount = array();
                foreach ($warehouseList as $warehouseId => $productsGroupedByCarriers){
                    foreach ($productsGroupedByCarriers as $data){
                        foreach ($data->carrier_list as $carrierId) {
                            if (!isset($carrierCount[$carrierId])) {
                                $carrierCount[$carrierId] = 0;
                            }
                            $carrierCount[$carrierId]++;
                        }
                    }
                }
                arsort($carrierCount);
                foreach ($warehouseList as $warehouseId => $productsGroupedByCarriers)
                {
                    if (!isset($packageList[$deliveryAddressId]->{$key}[$warehouseId])) {
                        $packageList[$deliveryAddressId]->{$key}[$warehouseId] = array();
                    }
                    foreach ($productsGroupedByCarriers as $data)
                    {
                        foreach ($carrierCount as $carrierId => $rate)
                        {
                            if (in_array($carrierId, $data->carrier_list))
                            {
                                if (!isset($packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId])) {
                                    $packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId] = new JObject();
                                    $packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId]->carrier_list = $data->carrier_list;
                                    $packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId]->warehouse_list = $data->warehouse_list;
                                    $packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId]->product_list = array();

                                }
                                $packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId]->carrier_list =
                                    array_intersect($packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId]->carrier_list, $data->carrier_list);
                                $packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId]->product_list =
                                    array_merge($packageList[$deliveryAddressId]->{$key}[$warehouseId][$carrierId]->product_list, $data->product_list);

                                break;
                            }
                        }
                    }
                }
            }
        }

        // Step 5 : Reduce depth of $package_list
        $finalPackageList = array();
        foreach ($packageList as $deliveryAddressId => $productsInStockList){
            if (!isset($finalPackageList[$deliveryAddressId])){
                $finalPackageList[$deliveryAddressId] = array();
            }

            foreach ($productsInStockList as $key => $warehouseList){
                foreach ($warehouseList as $warehouseId => $productsGroupedByCarriers){
                    foreach ($productsGroupedByCarriers as $data){
                        $pack = new JObject();
                        $pack->set('product_list', $data->product_list);
                        $pack->set('carrier_list' , $data->carrier_list);
                        $pack->set('warehouse_list', $data->warehouse_list);
                        $pack->set('warehouse_id', $warehouseId);
                        $nalPackageList[$deliveryAddressId][] = $pack;
                    }
                }
            }
        }
        $cache[(int)$this->cart_id] = $finalPackageList;
        return $finalPackageList;
    }

    public function getCartRules($filter = JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_ALL) {
        // If the cart has not been saved, then there can't be any cart rule applied
        if (!JeproshopCartRuleModelCartRule::isFeaturePublished() || !$this->cart_id){ return array(); }


        $cacheKey = 'jeproshop_cart_get_cart_rules_'. $this->cart_id . '_' . $filter;
        if (!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_cart_cart_rule') . " AS cd LEFT JOIN " . $db->quoteName('#__jeproshop_cart_rule');
            $query .= " AS cart_rule ON cd." . $db->quoteName('cart_rule_id') . " = cart_rule." . $db->quoteName('cart_rule_id') . " LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_cart_rule_lang') . " AS cart_rule_lang ON ( cd." . $db->quoteName('cart_rule_id') . " = cart_rule_lang.";
            $query .= $db->quoteName('cart_rule_id') . " AND cart_rule_lang.lang_id = " .(int)$this->lang_id . ") WHERE " . $db->quoteName('cart_id') . " = " .(int)$this->cart_id;
            $query .= ($filter == JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_SHIPPING ? " AND free_shipping = 1" : ""). ($filter == JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_GIFT ? " AND gift_product != 0" : "");
            $query .= ($filter == JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_REDUCTION ? " AND (reduction_percent != 0 OR reduction_amount != 0)"  : "") . " ORDER by cart_rule.priority ASC ";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cacheKey, $result);
        }
        $result = JeproshopCache::retrieve($cacheKey);

        // Define virtual context to prevent case where the cart is not the in the global context
        $virtual_context = JeproshopContext::getContext()->cloneContext();
        $virtual_context->cart = $this;

        foreach ($result as &$row){
            $row->obj = new JeproshopCartRuleModelCartRule($row->cart_rule_id, (int)$this->lang_id);
            $row->value_real = $row->obj->getContextualValue(true, $virtual_context, $filter);
            $row->value_tax_exc = $row->obj->getContextualValue(false, $virtual_context, $filter);

            // Retro compatibility < 1.5.0.2
            $row->discount_id = $row->cart_rule_id;
            $row->description = $row->name;
        }

        return $result;
    }

    /**
     * Does the cart use multiple address
     * @return boolean
     */
    public function isMultiAddressDelivery(){
        static $cache = null;

        if (is_null($cache)) {
            $db = JFactory::getDBO();

            $query = "SELECT count(distinct delivery_address_id) FROM " . $db->quoteName('#__jeproshop_cart_product');
            $query .= " AS cart_product WHERE cart_product.cart_id = " . (int)$this->cart_id;

            $db->setQuery($query);
            $cache = (bool)($db->loadResult() > 1);
        }
        return $cache;
    }

    /**
     *
     * Sort list of option delivery by parameters define in the BO
     * @param $option1
     * @param $option2
     * @return int -1 if $option 1 must be placed before and 1 if the $option1 must be placed after the $option2
     */
    public static function sortDeliveryOptionList($option1, $option2)
    {
        static $order_by_price = null;
        static $order_way = null;
        if (is_null($order_by_price))
            $order_by_price = !Configuration::get('PS_CARRIER_DEFAULT_SORT');
        if (is_null($order_way))
            $order_way = Configuration::get('PS_CARRIER_DEFAULT_ORDER');

        if ($order_by_price)
            if ($order_way)
                return ($option1['total_price_with_tax'] < $option2['total_price_with_tax']) * 2 - 1; // return -1 or 1
            else
                return ($option1['total_price_with_tax'] >= $option2['total_price_with_tax']) * 2 - 1; // return -1 or 1
        else
            if ($order_way)
                return ($option1['position'] < $option2['position']) * 2 - 1; // return -1 or 1
            else
                return ($option1['position'] >= $option2['position']) * 2 - 1; // return -1 or 1
    }

    public function save(){
        return ($this->cart_id > 0) ? $this->update() : $this->add();
    }

    /**
     * Set the delivery option and Carrier ID, if there is only one Carrier
     *
     * @param array $deliveryOption Delivery option array
     */
    public function setDeliveryOption($deliveryOption = null)
    {
        if (empty($deliveryOption) || count($deliveryOption) == 0) {
            $this->delivery_option = '';
            $this->carrier_id = 0;
            return;
        }
        JeproshopCache::clean('jeproshop_get_contextual_value_*');
        $deliveryOptionList = $this->getDeliveryOptionList(null, true);

        foreach ($deliveryOptionList as $addressId => $options) {
            if (!isset($deliveryOption[$addressId])) {
                foreach ($options as $key => $option) {
                    if ($option->is_best_price) {
                        $deliveryOption[$addressId] = $key;
                        break;
                    }
                }
            }
        }

        if (count($deliveryOption) == 1) {
            $this->carrier_id = $this->getCarrierIdFromDeliveryOption($deliveryOption);
        }

        $this->delivery_option = serialize($deliveryOption);
    }

    /**
     * Get Carrier ID from Delivery Option
     *
     * @param array $deliveryOption Delivery options array
     *
     * @return int|mixed Carrier ID
     */
    protected function getCarrierIdFromDeliveryOption($deliveryOption){
        $deliveryOptionList = $this->getDeliveryOptionList();
        foreach ($deliveryOption as $key => $value) {
            if (isset($deliveryOptionList[$key]) && isset($deliveryOptionList[$key][$value])) {
                if (count($deliveryOptionList[$key][$value]->carrier_list) == 1) {
                    return current(array_keys($deliveryOptionList[$key][$value]->carrier_list));
                }
            }
        }
        return 0;
    }

    /**
     * Check if order has already been placed
     *
     * @return boolean result
     */
    public function orderExists(){
        $cacheKey = 'jeproshop_cart_model_order_exists_'.(int)$this->cart_id;
        if (!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();

            $query = "SELECT count(*) total FROM " . $db->quoteName('#__jeproshop_orders') . " WHERE " . $db->quoteName('cart_id') . " = " .(int)$this->cart_id;
            $db->setQuery($query);
            $result = (bool)$db->loadObject()->total;
            JeproshopCache::store($cacheKey, $result);
        }
        return JeproshopCache::retrieve($cacheKey);
    }
}


