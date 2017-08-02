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

require_once 'order_detail.php';
require_once 'order_message.php';
require_once 'order_return.php';
require_once 'order_status.php';
require_once 'order_payment.php';

class JeproshopOrderModelOrder extends JeproshopModel{
    public $order_id;

    public $delivery_address_id;
    public $delivery_address;

    public $invoice_address_id;
    public $invoice_address;

    public $shop_group_id;

    public $shop_id;

    public $cart_id;

    public $currency_id;

    public $lang_id;

    public $customer_id;

    public $carrier_id;

    public $current_status;

    public $secure_key;

    public $payment;

    public $conversion_rate;

    public $recyclable = 1;

    public $gift = 0;

    public $gift_message;

    public $mobile_theme;

    public $shipping_number;

    public $total_discounts;

    public $total_discounts_tax_incl;
    public $total_discounts_tax_excl;

    public $total_paid;
    public $total_paid_tax_incl;
    public $total_paid_tax_excl;

    public $total_paid_real;

    public $total_products;

    public $total_products_with_tax;

    public $total_shipping;
    public $total_shipping_tax_excl;
    public $total_shipping_tax_incl;

    public $carrier_tax_rate;

    public $total_wrapping;
    public $total_wrapping_tax_incl;
    public $total_wrapping_tax_excl;

    public $invoice_number;
    public $invoice_date;

    public $delivery_number;
    public $delivery_date;

    public $valid;

    public $date_add;
    public $date_upd;

    public $reference;

    public $multishop_context = -1;
    public $multishop_context_group = true;

    protected $context;

    protected $_taxCalculationMethod = COM_JEPROSHOP_TAX_EXCLUDED;
    protected static $_historyCache = array();

    public function __construct($orderId = null, $langId = null){
        if($langId !== NULL){
            $this->lang_id = JeproshopLanguageModelLanguage::getLanguage($langId) !== FALSE ? (int)$langId : (int)JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($orderId){
            $cacheKey = 'jeproshop_order_model_' . $orderId . '_' . $langId . ( $this->shop_id ? '_' . $this->shop_id : '');
            if(!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord ";
                $where = "";
                /** get language information **/
                if($langId){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_order_lang') . " AS order_lang ";
                    $query .= " ON (ord.order_id = order_lang.order_id AND order_lang.lang_id = " . (int)$langId . ") ";
                    if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND order_lang.shop_id = " . $this->shop_id;
                    }
                }

                /** Get shop information **/
                if(JeproshopShopModelShop::isTableAssociated('order')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_order_shop') . " AS order_shop ON (";
                    $query .= "ord.order_id = order_shop.order_id AND order_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE ord.order_id = " . (int)$orderId . $where;

                $db->setQuery($query);
                $orderData = $db->loadObject();

                if($orderData){
                    JeproshopCache::store($cacheKey, $orderData);
                }
            }else{
                $orderData = JeproshopCache::retrieve($cacheKey);
            }

            if($orderData){
                $orderData->order_id = $orderId;
                foreach($orderData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
        $this->_taxCalculationMethod = JeproshopGroupModelGroup::getDefaultPriceDisplayMethod();
    }


    public function getOrderList(JeproshopContext $context = NULL){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!$context){ $context = JeproshopContext::getContext(); }

        //$select = ;
        //$select .= ;
        //$select =  .  ;
        //$select .= "";
        //$select.= "=";

        //$join = ;
       // $join .= " = ") ;
        //$join  ;
        //$join .= " ";
        /*$join = "";
        $join .=  LEFT JOIN " . $db->quoteName('#__jeproshop_order_status');
        /*$join .=  ")";
        $join .=;
        $join .= ;
        $join .= "; */


        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limit_start = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'order_id', 'string');
        $order_way = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'DESC', 'string');
        $langId = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $deleted = false;
        /* Manage default params values */

        $use_limit = true;
        if ($limit === false)
            $use_limit = false;

        $testJoin = true;

        $select_shop = "";
        $join_shop = " ";

        /*if(JeproshopCountryModelCountry::isCurrentlyUsed()){
            $query = "SELECT DISTINCT country.country_id, country_lang." . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord ";
            $query .= " INNER JOIN " . $db->quoteName('#__jeproshop_address') . " AS address ON address.address_id = ord.delivery_address_id INNER JOIN " . $db->quoteName('#__jeproshop_country');
            $query .= " AS country ON address.country_id = country.country_id INNER JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country." . $db->quoteName('country_id');
            $query.= " = country_lang." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") ORDER BY country_lang.name ASC";

            $db->setQuery($query);
            $result = $db->loadObjectList();

            $shopLinkType = 'shop';
        }*/

        $where = "";
        if($this->multishop_context && JeproshopShopModelShop::isTableAssociated('order')){
            if(JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_ALL || !$context->employee->isSuperAdmin()){
                if(JeproshopShopModelShop::isFeaturePublished() && $testJoin && JeproshopShopModelShop::isTableAssociated('order')){
                    $where .= " AND ord.order_id IN (SELECT order_shop.shop_id FROM " . $db->quoteName('#__jeproshop_order_shop') . " AS order_shop WHERE order_shop.shop_id IN (" . implode(', ', JeproshopShopModelShop::getContextListShopIds()) . "))";
                }
            }
        }

        $lang_join = "";
        /*if($lang_id){
            $lang_join = " LEFT JOIN " . $db->quoteName('#__jeproshop_order');
        } */

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS ord." . $db->quoteName('order_id') . ", ord." .  $db->quoteName('reference');
            if(JeproshopSettingModelSetting::getValue('enable_b2b_mode')){ $query .= ", customer." .  $db->quoteName('company'); }
            $query .= ", ord." . $db->quoteName('customer_id') . ", ord." . $db->quoteName('total_paid_tax_incl') . ", ord.";
            $query .=  $db->quoteName('payment') . ", ord." . $db->quoteName('date_add') . ", CONCAT(LEFT(customer.";
            $query .= $db->quoteName('firstname') . ", 1), '. ',customer." . $db->quoteName('lastname') . ") AS " . $db->quoteName('customer_name');
            $query .= ", order_status_lang." . $db->quoteName('name') . " AS " . $db->quoteName('order_status_name') . ", order_status.";
            $query .= $db->quoteName('color') . ", ord." . $db->quoteName('currency_id') . ", order_status." . $db->quoteName('order_status_id');
            $query .= ", IF((SELECT COUNT(orders.order_id) FROM " . $db->quoteName('#__jeproshop_orders') . " AS orders WHERE orders.customer_id";
            $query .= " =  ord.customer_id) > 1, 0, 1) AS new, country_lang.name as country_name, IF(ord.valid, 1, 0) badge_success, shop.shop_name";
            $query .= " AS shop_name FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord " . " LEFT JOIN " . $db->quoteName('#__jeproshop_order_status');
            $query .= " AS order_status ON (order_status." . $db->quoteName('order_status_id') . " = ord." . $db->quoteName('current_status') . ")  LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_order_status_lang') . " order_status_lang ON (order_status." . $db->quoteName('order_status_id') . " = ";
            $query .= " order_status_lang." . $db->quoteName('order_status_id') . " AND order_status_lang." . $db->quoteName('lang_id') . " = " . $langId;
            $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_customer') . " AS customer ON (customer." . $db->quoteName('customer_id') . " = ord.";
            $query .=  $db->quoteName('customer_id') . ") INNER JOIN " . $db->quoteName('#__jeproshop_address') . " AS address ON (address.address_id = ord.";
            $query .= "delivery_address_id) INNER JOIN " . $db->quoteName('#__jeproshop_country') . " AS country ON (address.country_id = country.country_id) INNER JOIN ";
            $query .= $db->quoteName('#__jeproshop_country_lang') . " AS country_lang  ON (country." . $db->quoteName('country_id'). " = country_lang.";
            $query .= $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id') . " = " . $langId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_shop');
            $query .= " AS shop ON (ord.shop_id = shop.shop_id) " ;
            $query .= " WHERE 1 = 1 " . JeproshopShopModelShop::addSqlRestriction(JeproshopShopModelShop::SHARE_ORDER, 'ord', 'shop');
            $query .= ($deleted ? " AND ord." . $db->quoteName('deleted') . " = 0 " : "") . (isset($filter) ? $filter : "");
            $query .= (isset($group) ? $group : "") . " ORDER BY " . ((str_replace('`', '', $order_by) == 'order_id') ? "ord." : "");
            $query .= $order_by . " " . $order_way ;

            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($use_limit === true) ? " LIMIT " .(int)$limit_start . ", " .(int)$limit : "");

            $db->setQuery($query);
            $orders = $db->loadObjectList();
         
            if($use_limit == true){
                $limit_start = (int)$limit_start -(int)$limit;
                if($limit_start < 0){ break; }
            }else{ break; }
        }while(empty($orders));



        $this->pagination = new JPagination($total, $limit_start, $limit);
        return  $orders;
    }

    /**
     * Returns the correct product taxes breakdown.
     *
     * Get all documents linked to the current order
     *
     * @return array
     */
    public function getDocuments(){
        $invoices = $this->getInvoicesCollection();
        foreach($invoices as $key => $invoice){
            if (!$invoice->number){ unset($invoices[$key]); }
        }
        $delivery_slips = $this->getDeliverySlipsCollection();
        // @TODO review
        foreach ($delivery_slips as $key => $delivery){
            $delivery->is_delivery = true;
            $delivery->date_add = $delivery->delivery_date;
            if (!$delivery->delivery_number){
                unset($delivery_slips[$key]);
            }
        }
        $order_slips = $this->getOrderSlipsCollection();

        $documents = array_merge($invoices, $order_slips, $delivery_slips);
        usort($documents, array('JeproshopOrderModelOrder', 'sortDocuments'));

        return $documents;
    }

    /**
     *
     * Get all order_slips for the current order
     * @since 1.5.0.2
     * @return Array Collection of OrderSlip
     */
    public function getOrderSlipsCollection(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_slip') . " WHERE order_id = " . (int)$this->order_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     *
     * Get all invoices for the current order
     * @since 1.5.0.1
     * @return Array Collection of OrderInvoice
     */
    public function getInvoicesCollection(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_invoice') . " WHERE order_id = " . (int)$this->order_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     *
     * Get all delivery slips for the current order
     * @since 1.5.0.2
     * @return Array Collection of OrderInvoice
     */
    public function getDeliverySlipsCollection(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_invoice') . " WHERE order_id = " . (int)$this->order_id;
        $query .= " AND delivery_number != 0";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function sortDocuments($a, $b){
        if ($a->date_add == $b->date_add){	return 0; }
        return ($a->date_add < $b->date_add) ? -1 : 1;
    }

    public function getProducts() {
        $db = JFactory::getDBO();

        $query = "SELECT *, osd.product_quantity FROM " . $db->quoteName('#__jeproshop_order_slip_detail') . " AS osd INNER JOIN ";
        $query .= $db->quoteName('#__jeproshop_order_detail') . " AS od ON osd." . $db->quoteName('order_detail_id') . " = od.";
        $query .= $db->quoteName('order_detail_id') . " WHERE osd." . $db->quoteName('order_slip_id') . " = " .(int)$this->order_id;

        $db->setQuery($query);
        $result = $db->loadObjectList();

        $order = new JeproshopOrderModelOrder($this->order_id);
        $products = array();
        foreach ($result as $row)
        {
            $order->setProductPrices($row);
            $products[] = $row;
        }
        return $products;
    }

    /**
     * Marked as deprecated but should not throw any "deprecated" message
     * This function is used in order to keep front office backward compatibility 14 -> 1.5
     * (Order History)
     *
     * @param $row
     */
    public function setProductPrices($row){
        $tax_calculator = JeproshopOrderDetailModelOrderDetail::getStaticTaxCalculator((int)$row->order_detail_id);
        $row->tax_calculator = $tax_calculator;
        $row->tax_rate = $tax_calculator->getTotalRate();

        $row->product_price = JeproshopTools::roundPrice($row->unit_price_tax_excl, 2);
        $row->product_price_with_tax = JeproshopTools::roundPrice($row->unit_price_tax_incl, 2);

        $group_reduction = 1;
        if ($row->group_reduction > 0){
            $group_reduction = 1 - $row->group_reduction / 100;
        }
        $row->product_price_with_tax_but_ecotax = $row->product_price_with_tax - $row->ecotax;

        $row->total_with_tax = $row->total_price_tax_incl;
        $row->total_price = $row->total_price_tax_excl;
    }

    public function getTaxCalculationMethod(){
        return (int)($this->_taxCalculationMethod);
    }

    /**
     * Get the an order detail list of the current order
     * @return array
     */
    public function getOrderDetailList(){
        return JeproshopOrderDetailModelOrderDetail::getOderDetails($this->order_id);
    }

    /**
     *
     * @return JeproshopOrderStatusModelOrderStatus or null if Order haven't a state
     */
    public function getCurrentOrderStatus(){
        if ($this->current_status){
            return new JeproshopOrderStatusModelOrderStatus($this->current_status);
        }
        return null;
    }

    /**
     * Get order history
     *
     * @param integer $lang_id Language id
     * @param bool|int $order_status_id Filter a specific order status
     * @param bool|int $no_hidden Filter no hidden status
     * @param integer $filters Flag to use specific field filter
     * @return array History entries ordered by date DESC
     */
    public function getHistory($lang_id, $order_status_id = false, $no_hidden = false, $filters = 0){
        if (!$order_status_id){ $order_status_id = 0; }

        $logable = false;
        $delivery = false;
        $paid = false;
        $shipped = false;
        if ($filters > 0){
            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_NO_HIDDEN){ $no_hidden = true; }

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_DELIVERY){ $delivery = true; }

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_LOGABLE){ $logable = true; }

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_PAID){ $paid = true;}

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_SHIPPED){ $shipped = true; }
        }

        if (!isset(self::$_historyCache[$this->order_id.'_'.$order_status_id .'_'.$filters]) || $no_hidden){
            $db = JFactory::getDBO();
            $lang_id = $lang_id ? (int)($lang_id) : 'o.`id_lang`';

            $query = "SELECT order_status.*, order_history.*, employee." . $db->quoteName('username') . " AS employee_firstname,";
            $query .= " employee." .$db->quoteName('name') . " AS employee_lastname, order_status_lang." . $db->quoteName('name');
            $query .= " AS order_status_name FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_order_history') . " AS order_history ON ord." . $db->quoteName('order_id');
            $query .= " = order_history." . $db->quoteName('order_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_order_status');
            $query .= " AS order_status ON order_status." . $db->quoteName('order_status_id') . " = order_history." . $db->quoteName('order_status_id');
            $query .= "	LEFT JOIN " . $db->quoteName('#__jeproshop_order_status_lang') . " AS order_status_lang ON (order_status.";
            $query .= $db->quoteName('order_status_id') . " = order_status_lang." . $db->quoteName('order_status_id') . " AND order_status_lang.";
            $query .= $db->quoteName('lang_id') . " = " . (int)($lang_id) . ") LEFT JOIN " . $db->quoteName('#__users') . " AS employee ON";
            $query .= " employee." . $db->quoteName('id') . " = order_history." . $db->quoteName('employee_id') . " WHERE order_history.order_id = ";
            $query .= (int)($this->order_id) . ($no_hidden ? " AND order_status.hidden = 0" : "") . ($logable ? " AND order_status.logable = 1" : "");
            $query .= ($delivery ? " AND order_status.delivery = 1" : "") . ($paid ? " AND order_status.paid = 1" : "") . ($shipped ? " AND order_status.shipped = 1" : "");
            $query .= ((int)($order_status_id) ? " AND order_history." . $db->quoteName('order_status_id') . " = " . (int)($order_status_id) : "");
            $query .= " ORDER BY order_history.date_add DESC, order_history.order_history_id DESC";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            if ($no_hidden)
                return $result;
            self::$_historyCache[$this->order_id.'_'.$order_status_id .'_'.$filters] = $result;
        }
        return self::$_historyCache[$this->order_id.'_'.$order_status_id.'_'.$filters];
    }

    public function getCartRules(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_cart_rule') . " AS order_cart_rule WHERE order_cart_rule.";
        $query .= $db->quoteName('order_id') . " = " . (int)$this->order_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get the sum of total_paid_tax_incl of the orders with similar reference
     *
     * @since 1.5.0.1
     * @return float
     */
    public function getOrdersTotalPaid(){
        $db = JFactory::getDBO();

        $query = "SELECT SUM(total_paid_tax_incl) FROM " . $db->quoteName('#__jeproshop_orders') . " WHERE ";
        $query .= $db->quoteName('reference') . " = " . $db->quote($this->reference) . " AND " . $db->quoteName('cart_id');
        $query .= " = " . (int)$this->cart_id;

        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Get total paid
     *
     * @param JeproshopCurrencyModelCurrency $currency currency used for the total paid of the current order
     * @return float amount in the $currency
     */
    public function getTotalPaid($currency = null)  {
        if (!$currency){
            $currency = new JeproshopCurrencyModelCurrency($this->currency_id);
        }

        $total = 0;
        // Retrieve all payments
        $payments = $this->getOrderPaymentCollection();
        foreach ($payments as $payment){
            if ($payment->currency_id == $currency->currency_id){
                $total += $payment->amount;
            }else{
                $amount = JeproshopTools::convertPrice($payment->amount, $payment->currency_id, false);
                if ($currency->currency_id == JeproshopSettingModelSetting::getValue('default_currency', null, null, $this->shop_id)){
                    $total += $amount;
                }else{
                    $total += JeproshopTools::convertPrice($amount, $currency->currency_id, true);
                }
            }
        }
        return JeproshopTools::roundPrice($total, 2);
    }

    /**
     * This method allows to get all Order Payment for the current order
     * @since 1.5.0.1
     * @return list of OrderPayment
     */
    public function getOrderPaymentCollection(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_payment') . " WHERE order_reference = ";
        $query .= $db->quote($this->reference);

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * This method return the ID of the previous order
     *
     * @return int
     */
    public function getPreviousOrderId(){
        $db = JFactory::getDBO();

        $query = "SELECT order_id FROM " . $db->quoteName('#__jeproshop_orders') . " WHERE order_id < ";
        $query .= (int)$this->order_id . " ORDER BY order_id DESC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * This method return the ID of the next order
     *
     * @return int
     */
    public function getNextOrderId(){
        $db = JFactory::getDBO();

        $query = "SELECT order_id FROM " . $db->quoteName('#__jeproshop_orders') . " WHERE order_id > ";
        $query .= (int)$this->order_id . " ORDER BY order_id ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get all not paid invoices for the current order
     *
     * @return Array Collection of Order invoice not paid
     */
    public function getNotPaidInvoicesCollection(){
        $invoices = $this->getInvoicesCollection();
        foreach ($invoices as $key => $invoice)
            if ($invoice->isPaid())
                unset($invoices[$key]);
        return $invoices;
    }

    /**
     * @return array return all shipping method for the current order
     * state_name sql var is now deprecated - use order_status_name for the state name and carrier_name for the carrier_name
     */
    public function getShipping() {
        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT order_carrier." . $db->quoteName('order_invoice_id') . ", order_carrier." . $db->quoteName('weight');
        $query .= ", order_carrier." . $db->quoteName('shipping_cost_tax_excl') . ", order_carrier." . $db->quoteName('shipping_cost_tax_incl');
        $query .= ", carrier." . $db->quoteName('url') . ", order_carrier." . $db->quoteName('carrier_id') . ", carrier." . $db->quoteName('name');
        $query .= " AS carrier_name, order_carrier." . $db->quoteName('date_add') . ", \"Delivery\" AS " . $db->quoteName('type') . ", \"true\" AS";
        $query .= " can_edit, order_carrier." . $db->quoteName('tracking_number') . ", order_carrier." . $db->quoteName('order_carrier_id');
        $query .= ", order_status_lang." . $db->quoteName('name') . " AS order_status_name, carrier." . $db->quoteName('name') . " AS state_name ";
        $query .= " FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord LEFT JOIN " . $db->quoteName('#__jeproshop_order_history');
        $query .= " AS order_history ON (ord." . $db->quoteName('order_id') . " = order_history." . $db->quoteName('order_id') . ") LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_order_carrier') . " AS order_carrier ON (ord." . $db->quoteName('order_id') . " = order_carrier.";
        $query .= $db->quoteName('order_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_carrier') . " AS carrier ON (order_carrier.";
        $query .= $db->quoteName('carrier_id') . " = carrier." . $db->quoteName('carrier_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_order_status_lang');
        $query .= " AS order_status_lang ON (order_history." . $db->quoteName('order_status_id') . " = order_status_lang." . $db->quoteName('order_status_id');
        $query .= " AND order_status_lang." . $db->quoteName('lang_id') . " = " . (int)JeproshopContext::getContext()->language->lang_id . ") WHERE ord.";
        $query .= $db->quoteName('order_id') . " = " .(int)$this->order_id . " GROUP BY carrier.carrier_id ";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getReturn()
    {
        return JeproshopOrderReturnModelOrderReturn::getOrdersReturn($this->customer_id, $this->order_id);
    }

    /**
     * Check if order contains (only) virtual products
     *
     * @param boolean $strict If false return true if there are at least one product virtual
     * @return boolean true if is a virtual order or false
     *
     */
    public function isVirtual($strict = true) {
        $products = $this->getProducts();
        if (count($products) < 1){	return false; }
        $virtual = true;
        foreach ($products as $product){
            $pd = JeproshopProductDownloadModelProductDownload::getProductDownloadIdFromProductId((int)($product->product_id));
            if ($pd && JeproshopTools::isUnsignedInt($pd) && $product->download_hash && $product->display_filename != ''){
                if ($strict === false){ return true; }
            }
            else
                $virtual &= false;
        }
        return $virtual;
    }

    /**
     * Get a collection of order payments
     *
     */
    public function getOrderPayments() {
        return JeproshopOrderPaymentModelOrderPayment::getByOrderReference($this->reference);
    }

    public function hasBeenPaid(){
        return count($this->getHistory((int)($this->lang_id), false, false, JeproshopOrderStatusModelOrderStatus::FLAG_PAID));
    }

    public function hasBeenShipped() {
        return count($this->getHistory((int)($this->lang_id), false, false, JeproshopOrderStatusModelOrderStatus::FLAG_SHIPPED));
    }

    public function hasBeenDelivered(){
        return count($this->getHistory((int)($this->lang_id), false, false, JeproshopOrderStatusModelOrderStatus::FLAG_DELIVERY));
    }

    /**
     * Has products returned by the merchant or by the customer?
     */
    public function hasProductReturned(){
        $db = JFactory::getDBO();

        $query = "SELECT IFNULL(SUM(order_return_detail.product_quantity), SUM(product_quantity_return)) FROM ";
        $query .= $db->quoteName('#__jeproshop_orders') . " AS ord INNER JOIN " . $db->quoteName('#__jeproshop_order_detail');
        $query .= " AS order_detail ON order_detail.order_id = ord.order_id LEFT JOIN " . $db->quoteName('#__jeproshop_order_return_detail');
        $query .= " AS order_return_detail ON order_return_detail.order_detail_id = order_detail.order_detail_id WHERE ord.order_id = ";
        $query .= (int)$this->order_id;

        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     *
     * Has invoice return true if this order has already an invoice
     * @return bool
     */
    public function hasInvoice(){
        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('order_invoice_id') . "	FROM " . $db->quoteName('#__jeproshop_order_invoice');
        $query .= "	WHERE " . $db->quoteName('order_id') . " =  " .(int)$this->order_id . "	AND " . $db->quoteName('number') . " > 0";

        $db->setQuery($query);
        $result = $db->loadResult();

        return ( $result ? $result : false);
    }

    /**
     * Get warehouse associated to the order
     *
     * return array List of warehouse
     */
    public function getWarehouseList(){
        $db = JFactory::getDBO();

        $query = "SELECT warehouse_id FROM " . $db->quoteName('#__jeproshop_order_detail') . " WHERE " . $db->quoteName('order_id');
        $query .= " = " . (int)$this->order_id . " GROUP BY warehouse_id ";

        $db->setQuery($query);
        $results = $db->loadObjectList();
        if (!$results){ return array(); }

        $warehouse_list = array();
        foreach ($results as $row){
            $warehouse_list[] = $row->warehouse_id;
        }
        return $warehouse_list;
    }

    /**
     * Get customer orders
     *
     * @param integer $customerId Customer id
     * @param boolean $showHiddenStatus Display or not hidden order statuses
     * @param JeproshopContext $context
     * @return array Customer orders
     */
    public static function getCustomerOrders($customerId, $showHiddenStatus = false, JeproshopContext $context = null){
        if (!$context){ $context = JeproshopContext::getContext(); }
        $db = JFactory::getDBO();

        $query = "SELECT ord.*, (SELECT SUM(order_detail." . $db->quoteName('product_quantity') . ") FROM " . $db->quoteName('#__jeproshop_order_detail');
        $query .= " AS order_detail WHERE order_detail." . $db->quoteName('order_id') . " = ord." . $db->quoteName('order_id') . ") nb_products FROM ";
        $query .= $db->quoteName('#__jeproshop_orders') . " AS ord WHERE ord." . $db->quoteName('customer_id') . " = " .(int)$customerId . " GROUP BY ord.";
        $query .= $db->quoteName('order_id') . " ORDER BY ord." . $db->quoteName('date_add') . " DESC";

        $db->setQuery($query);
        $res = $db->loadObjectList();
        if (!$res)
            return array();

        foreach($res as $key => $val){
            $query = "SELECT order_status." . $db->quoteName('order_status_id') . ", order_status_lang." . $db->quoteName('name') . " AS order_status, order_status.";
            $query .= $db->quoteName('invoice') . ", order_status." . $db->quoteName('color') . " as order_status_color FROM ";
            $query .= $db->quoteName('#__jeproshop_order_history') . " AS order_history LEFT JOIN " . $db->quoteName('#__jeproshop_order_status') . " AS order_status ";
            $query .= "ON (order_status." . $db->quoteName('order_status_id') . " = order_history." . $db->quoteName('order_status_id') . ") INNER JOIN ";
            $query .= $db->quoteName('#__jeproshop_order_status_lang') . " AS order_status_lang ON (order_status." . $db->quoteName('order_status_id') . " = order_status_lang.";
            $query .= $db->quoteName('order_status_id') . " AND order_status_lang." . $db->quoteName('lang_id') . " = " . (int)$context->language->lang_id . ") WHERE order_history.";
            $query .= $db->quoteName('order_id') . " = " . (int)($val->order_id).(!$showHiddenStatus ? " AND order_status." . $db->quoteName('hidden') . " != 1" : "");
            $query .= " ORDER BY order_history." . $db->quoteName('date_add') . " DESC, order_history." . $db->quoteName('order_history_id') . " DESC LIMIT 1";

            $db->setQuery($query);
            $res2 = $db->loadObjectList();

            if ($res2){
                $res[$key] = array_merge($res[$key], $res2[0]);
            }
        }
        return $res;
    }

    /**
     * Get an order by its cart id
     *
     * @param integer $cartId JeproshopCartModelCart id
     * @return array Order details
     */
    public static function getOrderIdByCartId($cartId){
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('order_id') . " FROM " . $db->quoteName('#__jeproshop_orders') . " WHERE " ;
        $query .= $db->quoteName('cart_id') . " = " . (int)($cartId) . JeproshopShopModelShop::addSqlRestriction();

        $db->setQuery($query);
        $result = $db->loadOject();

        return isset($result->order_id) ? $result->order_id : 0;
    }
}