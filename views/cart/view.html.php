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

class JeproshopCartViewCart extends JeproshopViewLegacy {
    public $cart;

    public function renderDetails($tpl = null){
        $cartModel = new JeproshopCartModelCart();
        $carts = $cartModel->getCartList();
        $this->assignRef('carts', $carts);

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderViewForm($tpl = null){
        if(!isset($this->context)){ $this->context = JeproshopContext::getContext(); }
        $customer = new JeproshopCustomerModelCustomer($this->cart->customer_id);
        $currency = new JeproshopCurrencyModelCurrency($this->cart->currency_id);
        $this->context->cart = $this->cart;
        $this->context->currency = $currency;
        $this->context->customer = $customer;

        $products = $this->cart->getProducts();
        $customizedData = JeproshopProductModelProduct::getAllCustomizedData((int)$this->cart->cart_id);
        JeproshopProductModelProduct::addCustomizationPrice($products, $customizedData);
        $summary = $this->cart->getSummaryDetails();

        /* Display order information */
        $orderId = (int)JeproshopOrderModelOrder::getOrderIdByCartId($this->cart->cart_id);
        $order = new JeproshopOrderModelOrder($orderId);
        if (JeproshopTools::isLoadedObject($order, 'order_id')){
            $taxCalculationMethod = $order->getTaxCalculationMethod();
            $shopId = (int)$order->shop_id;
        }else{
            $shopId = (int)$this->cart->shop_id;
            $taxCalculationMethod = JeproshopGroupModelGroup::getPriceDisplayMethod(JeproshopGroupModelGroup::getCurrent()->group_id);
        }

        if ($taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED) {
            $totalProducts = $summary->total_products;
            $totalDiscounts = $summary->total_discounts_tax_exc;
            $totalWrapping = $summary->total_wrapping_tax_exc;
            $totalPrice = $summary->total_price_without_tax;
            $totalShipping = $summary->total_shipping_tax_exc;
        } else {
            $totalProducts = $summary->total_products_wt;
            $totalDiscounts = $summary->total_discounts;
            $totalWrapping = $summary->total_wrapping;
            $totalPrice = $summary->total_price;
            $totalShipping = $summary->total_shipping;
        }
        foreach ($products as $k => &$product){
            if ($taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED){
                $product->product_price = $product->price;
                $product->product_total = $product->total;
            } else{
                $product->product_price = $product->price_with_tax;
                $product->product_total = $product->total_with_tax;
            }
            $image = new JObject();
            
            if (isset($product->product_attribute_id) && (int)$product->product_attribute_id > 0) {
                $image = JeproshopProductModelProduct::getProductAttributeCoverImage((int)$product->product_attribute_id);
            }

            if (!isset($image->image_id)) {
                $image = JeproshopProductModelProduct::getCoverImage($product->product_id);
            }
            $productObj = new JeproshopProductModelProduct($product->product_id);
            $product->quantity_in_stock = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($product->product_id, isset($product->product_attribute_id) ? $product->product_attribute_id : null, (int)$shopId);

            $imageProduct = new JeproshopImageModelImage(isset($image) ? $image->image_id : 0);
            $product->image = (isset($image->image_id) ? JeproshopImageManager::thumbnail(COM_JEPROSHOP_IMAGE_DIR.'products/'. $imageProduct->getExistingImagePath().'.jpg', 'product_mini_'.(int)$product->product_id.(isset($product->product_attribute_id) ? '_'.(int)$product->product_attribute_id : '').'.jpg', 45, 'jpg') : '--');
        }

        $this->assignRef('products', $products);
        $discounts = $this->cart->getCartRules();
        $this->assignRef('discounts', $discounts);
        $this->assignRef('order', $order);
        $this->assignRef('currency', $currency);
        $this->assignRef('customer', $customer);
        $customerStats = $customer->getStats();
        $this->assignRef('customer_stats', $customerStats);
        $this->assignRef('total_products', $totalProducts);
        $this->assignRef('total_discounts', $totalDiscounts);
        $this->assignRef('total_wrapping', $totalWrapping);
        $this->assignRef('total_price', $totalPrice);
        $this->assignRef('total_shipping', $totalShipping);
        $this->assignRef('customized_data', $customizedData);

        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');

        $this->addSideBar('customers');
    }

    public function loadObject($option = false){
        $app = JFactory::getApplication();
        $cartId = $app->input->get('cart_id');
        if($cartId && JeproshopTools::isUnsignedInt($cartId)){
            if(!$this->cart){
                $this->cart = new JeproshopCartModelCart($cartId);
            }
            if(JeproshopTools::isLoadedObject($this->cart, 'cart_id')){ return true; }
            return false;
        }elseif($option){
            if(!$this->cart){
                $this->cart = new JeproshopCartModelCart();
            }
            return true;
        }else{
            return false;
        }
    }
}