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

class JeproshopCartController extends JeproshopController{
    public function search(){
        $app = JFactory::getApplication();
        $useAjax = $app->input->get('use_ajax');
        $tab = $app->input->getWord('tab');
        $jsonData = array("success" =>false, "found" => false);

        if(isset($tab) && $tab != '') {
            switch ($tab) {
                case 'carts' :
                    $jsonData = $this->retrieveCarts();
                    break;
                case 'vouchers' :
                    $jsonData = $this->retrieveVouchers();
            }
        }

        if($useAjax){
            $document = JFactory::getDocument();
            $document->setMimeEncoding('application/json');
            echo json_encode($jsonData);
            $app->close();
        }
    }


    public function summary(){
        $app = JFactory::getApplication();
        $useAjax = $app->input->get('use_ajax');
        $jsonData = array("success" =>false, "found" => false);

        $retrievedData = $this->getAjaxData();

        if(!empty($retrievedData)){ $jsonData = array_merge(array("success" =>false, "found" => false), $retrievedData); } 

        if($useAjax){
            $document = JFactory::getDocument();
            $document->setMimeEncoding('application/json');
            echo json_encode($jsonData);
            $app->close();
        }
    }

    private function retrieveVouchers(){
        $app = JFactory::getApplication();

        $vouchers = JeproshopCartRuleModelCartRule::getCartRulesByCode($app->input->getString('q'), JeproshopContext::getContext()->language->lang_id, true);
        if(count($vouchers)){
            $jsonData = array("success" => true, 'found' => true, 'vouchers' => $vouchers);
        }else{
            $jsonData = array("success" => false, 'found' => false, 'messages' => JText::_('JGLOBAL_NO_MATCHING_RESULTS_MESSAGES'));
        }

        return $jsonData;
    }

    private function retrieveCarts(){
        $app = JFactory::getApplication();
        $customerId = $app->input->get('customer_id');
        $carts = JeproshopCartModelCart::getCustomerCarts((int)$customerId);
        $orders = JeproshopOrderModelOrder::getCustomerOrders((int)$customerId);
        $customer = new JeproshopCustomerModelCustomer($customerId);
        $cartArray = array();
        $orderArray = array();
        $jsonData = array("success" =>false, "found" => false);
        $context = JeproshopContext::getContext();

        if(count($carts)){
            foreach ($carts as $key => $cart) {
                $cartItem = new JeproshopCartModelCart($cart->cart_id);
                if((isset($context->cart) && $cart->cart_id == $context->cart->cart_id) || !JeproshopTools::isLoadedObject($cartItem, 'cart_id') || $cartItem->orderExists()){
                    unset($carts[$key]);
                }
                $currency = new JeproshopCurrencyModelCurrency((int)$cart->currency_id);
                $cart->total_price = JeproshopTools::displayPrice($cartItem->getOrderTotal(), $currency);
                $cartData = array();
                foreach($cart as $k => $value){
                    $cartData[$k] =  $value;
                }
                $cartArray[] = $cartData;
            }
        }

        if(count($orders)){
            foreach ($orders as &$order){
                $currency = new JeproshopCurrencyModelCurrency($order->currency_id);
                $order->total_paid_real = JeproshopTools::displayPrice($order->total_paid_real, $currency);
                $orderData = array();
                foreach($order as $key => $value){
                    $orderData[$key] =  $value;
                }
                $orderArray[] = $orderData;
            }
        }

        if($orderArray || $cartArray){
            $context = JeproshopContext::getContext();
            if(!isset($context->cart)){ $context->cart = new JeproshopCartModelCart(); $context->cart->cart_id = 0;}
            $context->customer = $customer;
            $jsonData = array("success" => true, "found" => true, "carts" => $cartArray, "orders" => $orderArray);

            $cartId = $context->cart->cart_id;

            $messageContent = '';
            if ($messages = JeproshopMessageModelMessage::getMessagesByCartId($cartId)) {
                $messageContent = $messages[0]->message;
            }
            $cartRules = $context->cart->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_SHIPPING);
            $freeShipping = false;
            if(count($cartRules)){
                foreach($cartRules as $cartRule){
                    if($cartRule->cart_rule_id == JeproshopCartRuleModelCartRule::getCartRuleIdByCode(JeproshopCartRuleModelCartRule::JEPROSHOP_BO_ORDER_CODE_PREFIX . '_' . $context->cart->cart_id)){
                        $freeShipping = true;
                        break;
                    }
                }
            }

            $addresses = $context->customer->getAddresses((int)$context->language->lang_id);
            $addressArray = array();
            foreach ($addresses as $address){
                $data = new JeproshopAddressModelAddress($address->address_id);
                $address->formated_address = JeproshopAddressFormatModelAddressFormat::generateAddress($data, array(), '<br />');
                $addressData = array();
                foreach($address as $key => $value){
                    $addressData[$key] = $value;
                }
                $addressArray[] = $addressData;
            }

            $jsonData['addresses'] = $addressArray;
            $currencyArray = array();
            foreach(new JeproshopCurrencyModelCurrency((int)$context->cart->currency_id) as $key => $value){
                $currencyArray[$key] = $value;
            }
            $jsonData['currency'] = $currencyArray;

            $jsonData['cart'] = $cartArray;

            $jsonData['summary'] = $this->getCartSummary($context);
            $jsonData['delivery_option_list'] = $this->getDeliveryOptionList();
            $jsonData['cart_id'] = $cartId;
            $jsonData['order_message'] = $messageContent;
            $jsonData['free_shipping'] = (int)$freeShipping;
            $jsonData['order_link'] = $this->getPageLink(
                'order', false, (int)$context->cart->lang_id, 'step=3&recover_cart=' . $cartId . '&' . md5(COM_JEPROSHOP_COOKIE_KEY . '_recover_cart_' . $cartId) . '=1');

        }
        return $jsonData;
    }

    private function getAjaxData($context = null){
        if($context == null){ $context = JeproshopContext::getContext(); }
        $app = JFactory::getApplication();
        if(isset($context->cart) && $context->cart == null) {
            $cartId = $context->cart->cart_id;
        }else{
            $cartId = $app->input->getInt('cart_id', 0);
        }

        if($cartId > 0 && !isset($context->cart)){ $context->cart = new JeproshopCartModelCart($cartId); }

        if(isset($context->cart) && $context->cart->cart_id > 0){
            $jsonData = array();
            $messageContent = '';
            if ($messages = JeproshopMessageModelMessage::getMessagesByCartId($cartId)) {
                $messageContent = $messages[0]->message;
            }
            $cartRules = $context->cart->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_SHIPPING);
            $freeShipping = false;
            if(count($cartRules)){
                foreach($cartRules as $cartRule){
                    if($cartRule->cart_rule_id == JeproshopCartRuleModelCartRule::getCartRuleIdByCode(JeproshopCartRuleModelCartRule::JEPROSHOP_BO_ORDER_CODE_PREFIX . '_' . $context->cart->cart_id)){
                        $freeShipping = true;
                        break;
                    }
                }
            }

            $addresses = $context->customer->getAddresses((int)$context->language->lang_id);
            $addressArray = array();
            foreach ($addresses as $address){
                $data = new JeproshopAddressModelAddress($address->address_id);
                $address->formated_address = JeproshopAddressFormatModelAddressFormat::generateAddress($data, array(), '<br />');
                $addressData = array();
                foreach($address as $key => $value){
                    $addressData[$key] = $value;
                }
                $addressArray[] = $addressData;
            }

            $jsonData['addresses'] = $addressArray;
            $currencyArray = array();
            foreach(new JeproshopCurrencyModelCurrency((int)$context->cart->currency_id) as $key => $value){
                $currencyArray[$key] = $value;
            }
            $jsonData['currency'] = $currencyArray;

            $cartArray = array();
            foreach ($context->cart as $key => $value){
                $cartArray[$key] = $value;
            }
            $jsonData['cart'] = $cartArray;

            $jsonData['summary'] = $this->getCartSummary($context);
            $jsonData['delivery_option_list'] = $this->getDeliveryOptionList($context);
            $jsonData['cart_id'] = $cartId;
            $jsonData['order_message'] = $messageContent;
            $jsonData['free_shipping'] = (int)$freeShipping;
            $jsonData['order_link'] = $this->getPageLink(
                'order', false, (int)$context->cart->lang_id, 'step=3&recover_cart=' . $cartId . '&' . md5(COM_JEPROSHOP_COOKIE_KEY . '_recover_cart_' . $cartId) . '=1');

            return $jsonData;
        }else{
            $currencyArray = array();
            foreach(new JeproshopCurrencyModelCurrency((int)JeproshopSettingModelSetting::getValue('default_currency')) as $key => $value){
                $currencyArray[$key] = $value;
            }
            $jsonData['currency'] = $currencyArray;

            $jsonData['summary'] = $this->getCartSummary($context);
            $jsonData['delivery_option_list'] = $this->getDeliveryOptionList($context);
            $jsonData['cart_id'] = $cartId;
            $jsonData['order_message'] = "";
            $jsonData['free_shipping'] = 0;
            $jsonData['order_link'] = $this->getPageLink(
                'order', false, (int)$context->cart->lang_id, '&step=3&recover_cart=' . $cartId . '&' . md5(COM_JEPROSHOP_COOKIE_KEY . '_recover_cart_' . $cartId) . '=1');

            return $jsonData;
        }
    }

    protected function getCartSummary($context){
        $summaryArray = array();
        if($context->cart != null) {
            $summary = $context->cart->getSummaryDetails(null, true);
        }else{
            $summary = new JObject();
            $summary->set('products', array());
            $summary->set('discounts', array());
        }
        $currency = (isset($context->currency) && $context->currency->currency_id == $context->cart->currency_id) ? $context->currency : new JeproshopCurrencyModelCurrency($context->cart->currency_id);
        if (count($summary->products) > 0){
            $productArray = array();
            foreach ($summary->products as &$product){
                $productData = array();
                //foreach ($product as $key => $value){ $productData[$key] = $value; }
                $productData['numeric_price'] = $product->price;
                $productData['numeric_total'] = $product->total;
                $productData['price'] = str_replace($currency->sign, '', JeproshopTools::displayPrice($product->price, $currency));
                $productData['total'] = str_replace($currency->sign, '', JeproshopTools::displayPrice($product->total, $currency));
                $productData['image_link'] = $this->getProductImageLink($product->link_rewrite, $product->image_id, 'small_default');
                if (!isset($product->attributes_small)) {
                    $productData['attributes_small'] = '';
                }
                $customizedData = JeproshopProductModelProduct::getAllCustomizedData((int)$context->cart->cart_id, null, true, null, (int)$product->customization_id);
                $customizedDataArray = array();
                foreach ($customizedData as $key => $value){ $customizedDataArray[$key] = $value; }
                $productData['customized_data'] = $customizedDataArray;

                $productArray[] = $productData;
            }
            $summaryArray['products'] = $productArray;
        }else{
            $summaryArray["products"] = array();
        }
        if (count($summary->discounts) > 0) {
            foreach ($summary->discounts as &$voucher) {
                $voucher['value_real'] = JeproshopTools::displayPrice($voucher->value_real, $currency);
            }
        }

        if (isset($summary->gift_products) && count($summary->gift_products)) {
            foreach ($summary->gift_products as &$product) {
                $product['image_link'] = $this->getProductImageLink($product->link_rewrite, $product->image_id, 'small_default');
                if (!isset($product->attributes_small)) {
                    $product['attributes_small'] = '';
                }
            }
        }
        return $summaryArray;
    }

    protected function getDeliveryOptionList($context = null){
        $formattedDeliveryOptionList = array();
        if($context == null){ $context = JeproshopContext::getContext(); }
        if(!isset($context->cart) || $context->cart == null) {
            $deliveryOptionList = array();
        }else{
            $deliveryOptionList = $context->cart->getDeliveryOptionList();
        }

        if (!count($deliveryOptionList)) {
            return array();
        }

        $defaultCarrierId = (int)JeproshopSettingModelSetting::getValue('default_carrier');
        foreach (current($deliveryOptionList) as $key => $deliveryOption) {
            $name = '';
            $first = true;
            $defaultCarrierDeliveryId = false;
            foreach ($deliveryOption->carrier_list as $carrier) {
                if (!$first) {
                    $name .= ', ';
                } else {
                    $first = false;
                }

                $name .= $carrier->instance->name;

                if ($deliveryOption->unique_carrier) {
                    $name .= ' - '.$carrier->instance->delay[$context->employee->lang_id];
                }

                if (!$defaultCarrierDeliveryId) {
                    $defaultCarrierDeliveryId = (int)$carrier->instance->carrier_id;
                }
                if ($carrier->instance->carrier_id == $defaultCarrierId) {
                    $defaultCarrierDeliveryId = $defaultCarrierId;
                }
                if (!$context->cart->carrier_id) {
                    $context->cart->setDeliveryOption(array($context->cart->delivery_address_id => (int)$carrier->instance->carrier_id.','));
                    $context->cart->save();
                }
            }
            $formattedDeliveryOptionList[] = array('name' => $name, 'key' => $key);
        }
        return $formattedDeliveryOptionList;
    }
}