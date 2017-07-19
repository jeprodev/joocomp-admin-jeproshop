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

class JeproshopOrderViewOrder extends JeproshopViewLegacy {
    public $order;

    public function renderDetails($tpl = null){
        $input = JFactory::getApplication()->input;
        $render = $input->get('render');
        if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){

        }else{
            $orderModel = new JeproshopOrderModelOrder();
            $orders = $orderModel->getOrderList();

            $this->assignRef('orders', $orders);
            $pagination = $orderModel->getPagination();
            $this->assignRef('pagination', $pagination);
        }

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $input = JFactory::getApplication()->input;
        $render = $input->get('render');
        if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){

        }else{

        }
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        $input = JFactory::getApplication()->input;
        $render = $input->get('render');
        if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){}else{}
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderViewForm($tpl = null){
        $input = JFactory::getApplication()->input;
        $render = $input->get('render');
        if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){

        }else{
            $this->renderOrderViewForm();
        }
        $this->addToolBar();
        parent::display($tpl);
    }

    private function renderOrderViewForm(){
        if(!isset($this->context) || $this->context == null){ $this->context = JeproshopContext::getContext(); }

        $app = JFactory::getApplication();

        if (!JeproshopTools::isLoadedObject($this->order, 'order_id')){
            JError::raiseError(500, JText::_('COM_JEPROSHOP_THE_ORDER_CANNOT_BE_FOUND_WITHIN_YOUR_DATA_BASE_MESSAGE'));
        }

        $customer = new JeproshopCustomerModelCustomer($this->order->customer_id);
        $carrier = new JeproshopCarrierModelCarrier($this->order->carrier_id);
        $products = $this->getProducts($this->order);
        $currency = new JeproshopCurrencyModelCurrency((int)$this->order->currency_id);

        // Carrier module call
        $carrier_module_call = null;
        if ($carrier->is_module){
            /*$module = Module::getInstanceByName($carrier->external_module_name);
            if (method_exists($module, 'displayInfoByCart'))
                $carrier_module_call = call_user_func(array($module, 'displayInfoByCart'), $order->id_cart); */
        }

        // Retrieve addresses information
        $addressInvoice = new JeproshopAddressModelAddress($this->order->invoice_address_id, $this->context->language->lang_id);
        if (JeproshopTools::isLoadedObject($addressInvoice, 'address_id') && $addressInvoice->state_id){
            $invoiceState = new JeproshopStateModelState((int)$addressInvoice->state_id);
        }

        if ($this->order->invoice_address_id == $this->order->delivery_address_id){
            $addressDelivery = $addressInvoice;
            if (isset($invoiceState)){ $deliveryState = $invoiceState; }
        }else{
            $addressDelivery = new JeproshopAddressModelAddress($this->order->address_delivery_id, $this->context->language->lang_id);
            if(JeproshopTools::isLoadedObject($addressDelivery, 'address_id') && $addressDelivery->state_id){
                $deliveryState = new JeproshopStateModelState((int)($addressDelivery->state_id));
            }
        }

        if (JeproshopShopModelShop::isFeaturePublished()){
            $shop = new JeproshopShopModelShop((int)$this->order->shop_id);
            //$this->toolbar_title .= ' - '.sprintf($this->l('Shop: %s'), $shop->name);
        }

        $order_details = $this->order->getOrderDetailList();
        foreach ($order_details as $order_detail){
            $product = new JeproshopProductModelProduct($order_detail->product_id );
            if (JeproshopSettingModelSetting::getValue('advanced_stock_management') && $product->advanced_stock_management){
                $warehouses = JeproshopWarehouseModelWarehouse::getWarehousesByProductId($order_detail->product_id, $order_detail->product_attribute_id);
                foreach ($warehouses as $warehouse){
                    if (!isset($warehouse_list[$warehouse->warehouse_id])){ $warehouse_list[$warehouse->warehouse_id] = $warehouse; }
                }
            }
        }

        $payment_methods = array();
        /*foreach (PaymentModule::getInstalledPaymentModules() as $payment)
        {
            $module = Module::getInstanceByName($payment['name']);
            if (Validate::isLoadedObject($module) && $module->active)
                $payment_methods[] = $module->displayName;
        }*/

        // display warning if there are products out of stock
        $display_out_of_stock_warning = false;
        $current_order_status = $this->order->getCurrentOrderStatus();
        if(JeproshopSettingModelSetting::getValue('stock_management') && (!JeproshopTools::isLoadedObject($current_order_status, 'order_id') || ($current_order_status->delivery != 1 && $current_order_status->shipped != 1))){
            $display_out_of_stock_warning = true;
        }
        // products current stock (from stock_available)
        foreach ($products as &$product){
            $product->current_stock = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($product->product_id, $product->product_attribute_id, $product->shop_id);

            $resume = JeproshopOrderSlipModelOrderSlip::getProductSlipResume($product->order_detail_id);
            $product->quantity_refundable = $product->product_quantity - $resume->product_quantity;
            $product->amount_refundable = $product->total_price_tax_incl - $resume->amount_tax_incl;
            $product->amount_refund = JeproshopTools::displayPrice($resume->amount_tax_incl, $currency);
            $product->refund_history = JeproshopOrderSlipModelOrderSlip::getProductSlipDetail($product->order_detail_id);
            $product->return_history = JeproshopOrderReturnModelOrderReturn::getProductReturnDetail($product->order_detail_id);

            // if the current stock requires a warning
            if ($product->current_stock == 0 && $display_out_of_stock_warning)
                JError::raiseWarning(500, JText::_('COM_JEPROSHOP_THIS_PRODUCT_IS_OUT_OF_STOCK_LABEL'). ' : '.$product->product_name);
            if ($product->warehouse_id != 0){
                $warehouse = new JeproshopWarehouseModelWarehouse((int)$product->warehouse_id);
                $product->warehouse_name = $warehouse->name;
            }else{
                $product->warehouse_name = '--';
            }
        }

        $history = $this->order->getHistory($this->context->language->lang_id);

        foreach ($history as &$order_state){
            $order_state->text_color = JeproshopTools::getBrightness($order_state->color) < 128 ? 'white' : 'black';
        }

        $this->setLayout('view');
        //$this->assignRef('order', $order);
        $cart = new JeproshopCartModelCart($this->order->cart_id);
        $this->assignRef('cart', $cart);
        $this->assignRef('customer', $customer);
        $customer_addresses = $customer->getAddresses($this->context->language->lang_id);
        $this->assignRef('customer_addresses', $customer_addresses);

        $this->assignRef('delivery_address', $addressDelivery);
        $deliveryState =  isset($deliveryState) ? $deliveryState : null;
        $this->assignRef('deliveryState', $deliveryState);
        $this->assignRef('invoice_address', $addressInvoice);
        $invoiceState = isset($invoiceState) ? $invoiceState : null;
        $this->assignRef('invoiceState', $invoiceState);
        $customerStats = $customer->getStats();
        $this->assignRef('customerStats', $customerStats);
        $this->assignRef('products', $products);
        $discounts = $this->order->getCartRules();
        $this->assignRef('discounts',$discounts);
        $orderTotalPaid = $this->order->getOrdersTotalPaid();
        $this->assignRef('orders_total_paid_tax_incl', $orderTotalPaid); // Get the sum of total_paid_tax_incl of the order with similar reference
        $totalPaid = $this->order->getTotalPaid();
        $this->assignRef('total_paid', $totalPaid);
        $returns =  JeproshopOrderReturnModelOrderReturn::getOrdersReturn($this->order->customer_id, $this->order->order_id);
        $this->assignRef('returns',$returns);
        $customerThreads =  JeproshopCustomerThreadModelCustomerThread::getCustomerMessages($this->order->customer_id);
        $this->assignRef('customer_thread_message', $customerThreads);
        $orderMessages = JeproshopOrderMessageModelOrderMessage::getOrderMessages($this->order->lang_id);
        $this->assignRef('order_messages', $orderMessages);
        $messages = JeproshopMessageModelMessage::getMessagesByOrderId($this->order->order_id, true);
        $this->assignRef('messages', $messages);
        $carrier = new JeproshopCarrierModelCarrier($this->order->carrier_id);
        $this->assignRef('carrier', $carrier);
        $this->assignRef('history', $history);
        $statues = JeproshopOrderStatusModelOrderStatus::getOrderStatus($this->context->language->lang_id);
        $this->assignRef('order_statues', $statues);
        $this->assignRef('warehouse_list', $warehouse_list);
        $sources = JeproshopConnectionSourceModelConnectionSource::getOrderSources($this->order->order_id);
        $this->assignRef('sources', $sources);
        $orderStatus = $this->order->getCurrentOrderStatus();
        $this->assignRef('current_status', $orderStatus);
        $currency = new JeproshopCurrencyModelCurrency($this->order->currency_id);
        $this->assignRef('currency', $currency);
        $currencies = JeproshopCurrencyModelCurrency::getCurrenciesByShopId($this->order->shop_id);
        $this->assignRef('currencies', $currencies);
        $previousOrder = $this->order->getPreviousOrderId();
        $this->assignRef('previousOrder', $previousOrder);
        $nextOrder = $this->order->getNextOrderId();
        $this->assignRef('nextOrder', $nextOrder);
        //$this->assignRef('current_index', self::$currentIndex);
        $this->assignRef('carrier_module_call', $carrier_module_call);
        $this->assignRef('iso_code_lang', $this->context->language->iso_code);
        $this->assignRef('lang_id', $this->context->language->lang_id);
        $can_edit = true;
        $this->assignRef('can_edit', $can_edit); //($this->tabAccess['edit'] == 1));
        $this->assignRef('current_id_lang', $this->context->language->lang_id);
        $invoiceCollection = $this->order->getInvoicesCollection();
        $this->assignRef('invoices_collection', $invoiceCollection);
        $unPaid = $this->order->getNotPaidInvoicesCollection();
        $this->assignRef('not_paid_invoices_collection', $unPaid);
        $this->assignRef('payment_methods', $payment_methods);
        $invoiceAllowed = JeproshopSettingModelSetting::getValue('invoice_allowed');
        $this->assignRef('invoice_management_active', $invoiceAllowed);
        $display_warehouse = (int)JeproshopSettingModelSetting::getValue('advanced_stock_management');
        $this->assignRef('display_warehouse', $display_warehouse);
        $stockManagement = JeproshopSettingModelSetting::getValue('stock_management');
        $this->assignRef('stock_management', $stockManagement);
    }

    public function addToolBar(){
        $input = JFactory::getApplication()->input;
        $task = $input->get('task');
        $render = $input->get('render');

        switch ($task){
            case 'add' :
                if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){}else{}
                break;
            case 'edit' :
                if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){}else{}
                break;
            default :
                if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){}else{}
                break;
        }
        $this->addSideBar('order');
    }


    protected function getProducts($order){
        $products = $order->getProducts();

        foreach ($products as &$product){
            if ($product->image != null){
                $name = 'product_mini_'. (int)$product->product_id .(isset($product->product_attribute_id) ? '_'.(int)$product->product_attribute_id : '').'.jpg';
                // generate image cache, only for back office
                $product->image_tag = JeproshopImageManager::thumbnail(COM_JEPROSHOP_IMAGE_DIR .'products/'.$product->image->getExistingImagePath().'.jpg', $name, 45, 'jpg');
                if (file_exists(COM_JEPROSHOP_IMAGE_DIR . $name)){
                    $product->image_size = getimagesize(COM_JEPROSHOP_IMAGE_DIR . $name);
                }else{ $product->image_size = false; }
            }
        }
        return $products;
    }



    public function loadObject($option = false){
        $input = JFactory::getApplication()->input;
        $task = $input->get('task');
        $render = $input->get('render');
        $isLoaded = false;

        if($render == 'invoices'){}elseif ($render == 'returns'){} elseif ($render == 'delivery'){}elseif ($render == 'refund'){}elseif ($render == 'status'){}elseif ($render == 'messages'){

        }else{
            $orderId = $input->get('order_id');
            if($orderId && JeproshopTools::isUnsignedInt($orderId)){
                if(!$this->order){
                    $this->order = new JeproshopOrderModelOrder($orderId);
                }

                if(!JeproshopTools::isLoadedObject($this->order, 'order_id')){
                    JError::raiseError(500, JText::_('COM_JEPROSHOP_ORDER_NOT_FOUND_MESSAGE'));
                    $isLoaded = false;
                }else { $isLoaded = true; }
            }elseif($option){
                if(!$this->order){
                    $this->order = new JeproshopOrderModelOrder();
                }
            }else{
                JError::raiseError(500, JText::_('COM_JEPROSHOP_ORDER_DOES_NOT_EXIST_MESSAGE'));
                $isLoaded = false;
            }

        }
        return $isLoaded;
    }

}

/*
 * public function loadObject($option = false){
        $app = JFactory::getApplication();
        $productId = $app->input->get('product_id');
        $isLoaded = false;
        if($productId && JeproshopTools::isUnsignedInt($productId)){
            if(!$this->product){
                $this->product = new JeproshopProductModelProduct($productId);
            }

            if(!JeproshopTools::isLoadedObject($this->product, "product_id")){
                JError::raiseError(500, JText::_('COM_JEPROSHOP_PRODUCT_NOT_FOUND_MESSAGE'));
                $isLoaded = false;
            }else{
                $isLoaded = true;
            }
        }elseif($option){
            if(!$this->product){
                $this->product = new JeproshopProductModelProduct();
            }
        }else{
            JError::raiseError(500, JText::_('COM_JEPROSHOP_PRODUCT_DOES_NOT_EXIST_MESSAGE'));
            $isLoaded = false;
        }

        //specified
        if($isLoaded && JeproshopTools::isLoadedObject($this->product, 'product_id')){
            if(JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && JeproshopShopModelShop::isFeaturePublished() && !$this->product->isAssociatedToShop()){
                $this->product = new JeproshopProductModelProduct((int)$this->product->product_id, false, null, (int)$this->product->default_shop_id);
            }
            if($this->product->advanced_stock_management) {
                $this->product->loadStockData();
            }
        }
        return $isLoaded;
    }
 */