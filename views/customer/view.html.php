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

class JeproshopCustomerViewCustomer extends JeproshopViewLegacy {
    protected $customer;

    protected $helper;

    protected $customers;

    public function renderDetails($tpl = null){
        $customerModel = new JeproshopCustomerModelCustomer();
        $this->customers = $customerModel->getCustomerList();
        $this->pagination = $customerModel->getPagination();
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        $groups = JeproshopGroupModelGroup::getStaticGroups($this->context->language->lang_id, true);

        $this->assignRef('groups', $groups);
        $this->helper = new JeproshopHelper();

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        $groups = JeproshopGroupModelGroup::getStaticGroups($this->context->language->lang_id, true);

        $this->assignRef('groups', $groups); 
        $this->helper = new JeproshopHelper();
        
        $this->addToolBar();
        parent::display($tpl);
    }
    
    public function renderViewForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }

        $this->loadObject();
        if(!JeproshopTools::isLoadedObject($this->customer, 'customer_id')){ return; }
        $this->context->customer = $this->customer;

        $customerStats = $this->customer->getStats();
        $this->assignRef('customer_stats', $customerStats);

        $lastVisit = JeproshopTools::displayDate($customerStats->last_visit, null); //, true);
        $this->assignRef('last_visit', $lastVisit);

        $orders = JeproshopOrderModelOrder::getCustomerOrders($this->customer->customer_id, true);
        $totalOrders = count($orders);

        for ($i = 0; $i < $totalOrders; $i++){
            $orders[$i]->total_paid_real_not_formated = $orders[$i]->total_paid_real;
            $orders[$i]->total_paid_real = JeproshopTools::displayPrice($orders[$i]->total_paid_real, new JeproshopCurrencyModelCurrency((int)$orders[$i]->currency_id));
        }

        $messages = JeproshopCustomerThreadModelCustomerThread::getCustomerMessages((int)$this->customer->customer_id);
        $total_messages = count($messages);
        for ($i = 0; $i < $total_messages; $i++){
            $messages[$i]->message = substr(strip_tags(html_entity_decode($messages[$i]->message, ENT_NOQUOTES, 'UTF-8')), 0, 75);
            $messages[$i]->date_add =JeproshopTools::displayDate($messages[$i]->date_add, null);
        }

        $customerGroups = $this->customer->getGroups();
        $totalGroups = count($customerGroups);
        $groups = [];
        for ($i = 0; $i < $totalGroups; $i++){
            $groups[$i] = new JeproshopGroupModelGroup($customerGroups[$i]);
        }
        $this->assignRef('customer_groups', $groups);

        $totalOk = 0;
        $ordersOk = array();
        $orders_ko = array();
        foreach ($orders as $order){
            if (!isset($order->order_state)){
                $order->order_state = JText::_('COM_JEPROSHOP_THERE_IS_NO_STATUS_DEFINED_FOR_THIS_ORDER_MESSAGE');
            }
            if ($order->valid){
                $orders_ok[] = $order;
                $totalOk += $order->total_paid_real_not_formated;
            }else{
                $orders_ko[] = $order;
            }
        }

        $products = $this->customer->getBoughtProducts();

        $carts = JeproshopCartModelCart::getCustomerCarts($this->customer->customer_id);
        $total_carts = count($carts);
        for ($i = 0; $i < $total_carts; $i++){
            $cart = new JeproshopCartModelCart((int)$carts[$i]->cart_id);
            $this->context->cart = $cart;
            $summary = $cart->getSummaryDetails();
            $currency = new JeproshopCurrencyModelCurrency((int)$carts[$i]->currency_id);
            $carrier = new JeproshopCarrierModelCarrier((int)$carts[$i]->carrier_id);
            $carts[$i]->cart_id = sprintf('%06d', $carts[$i]->cart_id);
            $carts[$i]->date_add = JeproshopTools::displayDate($carts[$i]->date_add, null);
            $carts[$i]->total_price = JeproshopTools::displayPrice($summary->total_price, $currency);
            $carts[$i]->name = $carrier->name;
        }

        $interested = $this->customer->getInterestedProducts();

        $total_interested = count($interested);
        for ($i = 0; $i < $total_interested; $i++){
            $product = new JeproshopProductModelProduct($interested[$i]->product_id, false,
                $this->context->controller->default_form_language, $interested[$i]->shop_id);
            if (!JeproshopTools::isLoadedObject($product, 'product_id')){ continue; }

            $interested[$i]->url = $this->context->controller->getProductLink(
                $product->product_id, $product->link_rewrite,
                JeproshopCategoryModelCategory::getLinkRewrite($product->default_category_id,
                    $this->context->controller->default_form_language), null, null, $interested[$i]->cart_product_shop_id
            );
            $interested[$i]->product_id = (int)$product->product_id;
            $interested[$i]->name = htmlentities($product->name);
        }

        $connections = $this->customer->getLastConnections();
        if (!is_array($connections))
            $connections = array();
        $total_connections = count($connections);

        for ($i = 0; $i < $total_connections; $i++){
            $connections[$i]->http_referer = $connections[$i]->http_referer ? preg_replace('/^www./', '', parse_url($connections[$i]->http_referer, PHP_URL_HOST)) : JText::_('COM_JEPROSHOP_DIRECT_LINK_LABEL');
        }
        $referrers = JeproshopReferrerModelReferrer::getReferrers($this->customer->customer_id);
        $total_referrers = count($referrers);
        for ($i = 0; $i < $total_referrers; $i++){
            $referrers[$i]->date_add = JeproshopTools::displayDate($referrers[$i]->date_add,null , true);
        }
        $customerLanguage = new JeproshopLanguageModelLanguage($this->customer->lang_id);
        $shop = new JeproshopShopModelShop($this->customer->shop_id);

        $registration = JeproshopTools::displayDate($this->customer->date_add,  null, true);
        $this->assignRef('registration_date', $registration);

        $this->assignRef('count_better_customers', $countBetterCustomers);
        $shop_feature_active = JeproshopShopModelShop::isFeaturePublished();
        $this->assignRef('shop_is_feature_active', $shop_feature_active);
        $this->assignRef('shop_name', $shop->shop_name);
        $customerBirthday = JeproshopTools::displayDate($this->customer->birthday);
        $this->assignRef('customer_birthday', $customerBirthday);
        $last_update = JeproshopTools::displayDate($this->customer->date_upd, null , true);
        $this->assignRef('last_update', $last_update);
        $customerExists = JeproshopCustomerModelCustomer::customerExists($this->customer->email);
        $this->assignRef('customer_exists', $customerExists);
        $this->assignRef('lang_id', $this->customer->lang_id);

        $this->assignRef('customerLanguage', $customerLanguage);
		// Add a Private note
        $customerNote = JeproshopTools::htmlentitiesUTF8($this->customer->note);
		$this->assignRef('customer_note', $customerNote);
		// Messages
		$this->assignRef('messages', $messages);
		// Groups

		// Orders
		$this->assignRef('orders', $orders);
		$this->assignRef('orders_ok', $orders_ok);
		$this->assignRef('orders_ko', $orders_ko);
        $total_ok = JeproshopTools::displayPrice($totalOk, $this->context->currency->currency_id);
		$this->assignRef('total_ok', $total_ok);
		// Products
		$this->assignRef('products', $products);
		// Addresses
        $addresses = $this->customer->getAddresses($this->context->controller->default_form_language);
		$this->assignRef('addresses', $addresses);
		// Discounts
        $discounts = JeproshopCartRuleModelCartRule::getCustomerCartRules($this->context->controller->default_form_language, $this->customer->customer_id, false, false);
		$this->assignRef('discounts', $discounts);
		// Carts
		$this->assignRef('carts', $carts);

        // Interested
        $this->assignRef('interested_products', $interested);
        // Connections
        $this->assignRef('connections', $connections);
        // Referrers
        $this->assignRef('referrers', $referrers);

        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch($task){
            case 'add' : break;
            case 'edit' :
                if(!$this->context->controller->can_add_customer){ }
                break;
            default : break;
        }

        $this->addSideBar('customers');
    }

    /**
     * Load class object using identifier in $_GET (if possible)
     * otherwise return an empty object, or die
     *
     * @param boolean $opt Return an empty object if load fail
     * @return object|boolean
     */
    public function loadObject($opt = false){
        $app = JFactory::getApplication();
        $customerId = (int)$app->input->get('customer_id');
        if ($customerId && JeproshopTools::isUnsignedInt($customerId)){
            if (!$this->customer)
                $this->customer = new JeproshopCustomerModelCustomer($customerId);
            if (JeproshopTools::isLoadedObject($this->customer, 'customer_id')){
                return $this->customer;
            }
            // throw exception
            //$this->errors[] = Tools::displayError('The object cannot be loaded (or found)');
            return false;
        }elseif ($opt){
            if (!$this->customer)
                $this->customer = new JeproshopCustomerModelCustomer();
            return $this->customer;
        }else{
            JeproshopTools::displayError('The object cannot be loaded (the identifier is missing or invalid)');
            return false;
        }
    }
}