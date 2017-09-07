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

class JeproshopSettingViewSetting extends JeproshopViewLegacy {
    public $helper;

    public function renderDetails($tpl = null){
        $improve_front_safety = JeproshopSettingModelSetting::getValue('improve_front_safety');
        $allow_iframes_in_html_field = JeproshopSettingModelSetting::getValue('allow_iframes_in_html_fields');
        $use_purifier_library = JeproshopSettingModelSetting::getValue('use_purifier_library');
        $round_mode_type = JeproshopSettingModelSetting::getValue('round_mode_type');
        $price_round_mode = JeproshopSettingModelSetting::getValue('price_round_mode');
        $number_of_decimals = JeproshopSettingModelSetting::getValue('number_of_decimals');
        $display_supplier_and_manufacturer = JeproshopSettingModelSetting::getValue('display_suppliers_and_manufacturers');
        $display_best_sells = JeproshopSettingModelSetting::getValue('display_best_sells');
        $activateMultiShop = JeproshopSettingModelSetting::getValue('activate_multishop');
        $shop_activity = JeproshopSettingModelSetting::getValue('shop_activity');

        $this->assignRef('improve_front_safety', $improve_front_safety);
        $this->assignRef('number_of_decimals', $number_of_decimals);
        $this->assignRef('allow_iframes_in_html_field', $allow_iframes_in_html_field);
        $this->assignRef('use_purifier_library', $use_purifier_library);
        $this->assignRef('round_mode_type', $round_mode_type);
        $this->assignRef('price_round_mode', $price_round_mode);
        $this->assignRef('display_supplier_manufacturer', $display_supplier_and_manufacturer);
        $this->assignRef('display_best_sells', $display_best_sells);
        $this->assignRef('activate_multishop', $activateMultiShop);
        $this->assignRef('shop_activity', $shop_activity);


        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderOrderSettingForm($tpl = null){
        $currency = new JeproshopCurrencyModelCurrency(JeproshopContext::getContext()->currency->currency_id);
        $this->assignRef('currency', $currency);
        $offer_gift_wrapping = JeproshopSettingModelSetting::getValue('offer_gift_wrapping');
        $this->assignRef('offer_gift_wrapping', $offer_gift_wrapping);
        $gift_wrapping_price = JeproshopSettingModelSetting::getValue('gift_wrapping_price');
        $this->assignRef('gift_wrapping_price', $gift_wrapping_price);
        $gift_wrapping_tax = JeproshopSettingModelSetting::getValue('gift_wrapping_tax');
        $this->assignRef('gift_wrapping_tax', $gift_wrapping_tax);
        $offer_recycled_wrapping = JeproshopSettingModelSetting::getValue('offer_recycled_wrapping');
        $this->assignRef('offer_recycled_wrapping', $offer_recycled_wrapping);
        $order_process_type = JeproshopSettingModelSetting::getValue('order_process_type');
        $this->assignRef('order_process_type', $order_process_type);
        $allow_order_with_unregistered_customer = JeproshopSettingModelSetting::getValue('allow_order_with_unregistered_customer');
        $this->assignRef('allow_order_with_unregistered_customer', $allow_order_with_unregistered_customer);
        $deactivate_reordering_option = JeproshopSettingModelSetting::getValue('deactivate_reordering_option');
        $this->assignRef('deactivate_reordering_option', $deactivate_reordering_option);

        //$this->assignRef('warehouses', JeproshopWarehouseModelWarehouse::getWarehouses());
        $delay_shipping = JeproshopSettingModelSetting::getValue('delay_shipping');
        $this->assignRef('delay_shipping', $delay_shipping);
        $general_selling_condition = JeproshopSettingModelSetting::getValue('general_selling_condition');
        $this->assignRef('general_selling_condition', $general_selling_condition);
        $minimum_amount_required_for_order = JeproshopSettingModelSetting::getValue('minimum_amount_required_for_order');
        $this->assignRef('minimum_amount_required_for_order', $minimum_amount_required_for_order);
        $display_product_image_on_invoice = JeproshopSettingModelSetting::getValue('display_product_image_on_invoice');
        $this->assignRef('display_product_image_on_invoice', $display_product_image_on_invoice);
        $display_product_image_on_delivery = JeproshopSettingModelSetting::getValue('display_product_image_on_delivery_file');
        $this->assignRef('display_product_image_on_delivery_file', $display_product_image_on_delivery);
        $this->addToolBar();
        parent::display($tpl);
    }
    
    public function renderProductSettingsForm($tpl = null){
        $catalog_mode = JeproshopSettingModelSetting::getValue('catalog_mode');
        $this->assignRef('catalog_mode', $catalog_mode);
        $comparator_max_item = JeproshopSettingModelSetting::getValue('comparator_max_item');
        $this->assignRef('comparator_max_item', $comparator_max_item);
        $number_days_new_product = JeproshopSettingModelSetting::getValue('number_days_new_product');
        $this->assignRef('number_days_new_product', $number_days_new_product);
        $redirect_after_adding_product_to_cart = JeproshopSettingModelSetting::getValue('redirect_after_adding_product_to_cart');
        $this->assignRef('redirect_after_adding_product_to_cart', $redirect_after_adding_product_to_cart);
        $product_short_desc_limit = JeproshopSettingModelSetting::getValue('product_short_desc_limit');
        $this->assignRef('product_short_desc_limit', $product_short_desc_limit);
        $quantity_discount_based_on = JeproshopSettingModelSetting::getValue('quantity_discount_based_on');
        $this->assignRef('quantity_discount_based_on', $quantity_discount_based_on);
        $product_short_desc_limit = JeproshopSettingModelSetting::getValue('product_short_desc_limit');
        $this->assignRef('product_short_desc_limit', $product_short_desc_limit);
        $quantity_discount_based_on = JeproshopSettingModelSetting::getValue('quantity_discount_based_on');
        $this->assignRef('quantity_discount_based_on', $quantity_discount_based_on);
        $force_update_of_friendly_url = JeproshopSettingModelSetting::getValue('force_update_of_friendly_url');
        $this->assignRef('force_update_of_friendly_url', $force_update_of_friendly_url);
        $attribute_anchor_separator = JeproshopSettingModelSetting::getValue('attribute_anchor_separator');
        $this->assignRef('attribute_anchor_separator', $attribute_anchor_separator);
        $default_sort_way = JeproshopSettingModelSetting::getValue('default_sort_way');
        $this->assignRef('default_sort_way', $default_sort_way);
        $default_order_way = JeproshopSettingModelSetting::getValue('default_order_way');
        $this->assignRef('default_order_way', $default_order_way);
        $products_per_page = JeproshopSettingModelSetting::getValue('products_per_page');
        $this->assignRef('products_per_page', $products_per_page);
        $display_available_quantity = JeproshopSettingModelSetting::getValue('display_available_quantity');
        $this->assignRef('display_available_quantity', $display_available_quantity);
        $last_quantities = JeproshopSettingModelSetting::getValue('last_quantities');
        $this->assignRef('last_quantities', $last_quantities);
        $display_unavailable_attributes = JeproshopSettingModelSetting::getValue('display_unavailable_attributes');
        $this->assignRef('display_unavailable_attributes', $display_unavailable_attributes);
        $display_add_to_cart_on_product_with_attributes = JeproshopSettingModelSetting::getValue('display_add_to_cart_on_product_with_attributes');
        $this->assignRef('display_add_to_cart_on_product_with_attributes', $display_add_to_cart_on_product_with_attributes);
        $display_discount_price = JeproshopSettingModelSetting::getValue('display_discount_price');
        $this->assignRef('display_discount_price', $display_discount_price);
        $allow_out_of_stock_ordering = JeproshopSettingModelSetting::getValue('allow_out_of_stock_ordering');
        $this->assignRef('allow_out_of_stock_ordering', $allow_out_of_stock_ordering);
        $stock_management = JeproshopSettingModelSetting::getValue('stock_management');
        $this->assignRef('stock_management', $stock_management);
        $advanced_stock_management = JeproshopSettingModelSetting::getValue('advanced_stock_management');
        $this->assignRef('advanced_stock_management', $advanced_stock_management);
        $use_advanced_stock_management_on_new_product = JeproshopSettingModelSetting::getValue('use_advanced_stock_management_on_new_product');
        $this->assignRef('use_advanced_stock_management_on_new_product', $use_advanced_stock_management_on_new_product);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderCustomerSettingsForm($tpl = null){
        $currency = new JeproshopCurrencyModelCurrency(JeproshopContext::getContext()->currency->currency_id);
        $this->assignRef('currency', $currency);
        $require_phone_number = JeproshopSettingModelSetting::getValue('require_phone_number');
        $this->assignRef('require_phone_number', $require_phone_number);
        $refresh_cart_after_identification = JeproshopSettingModelSetting::getValue('refresh_cart_after_identification');
        $this->assignRef('refresh_cart_after_identification', $refresh_cart_after_identification);
        $email_on_registration = JeproshopSettingModelSetting::getValue('email_on_registration');
        $this->assignRef('email_on_registration', $email_on_registration);
        $password_regeneration_delay = JeproshopSettingModelSetting::getValue('password_regeneration_delay');
        $this->assignRef('password_regeneration_delay', $password_regeneration_delay);
        $enable_b2b_mode = JeproshopSettingModelSetting::getValue('enable_b2b_mode');
        $this->assignRef('enable_b2b_mode', $enable_b2b_mode);
        $activate_newsletter_subscription = JeproshopSettingModelSetting::getValue('activate_newsletter_subscription');
        $this->assignRef('activate_newsletter_subscription', $activate_newsletter_subscription);
        $registration_process_type = JeproshopSettingModelSetting::getValue('registration_process_type');
        $this->assignRef('registration_process_type', $registration_process_type);
        $activate_opt_in = JeproshopSettingModelSetting::getValue('activate_opt_in');
        $this->assignRef('activate_opt_in', $activate_opt_in);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderGeolocationSettingsForm($tpl = null){
        $enableGeolocation  = JeproshopSettingModelSetting::getValue('enable_geolocation');

        //$this->assignRef('geolocation_un_allowed_behavior', );
        //$this->helper = new JeproshopHelper();
        $this->addToolBar();
        parent::display($tpl);
    }

    public function render($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $this->helper = new JeproshopHelper();
        $this->addSideBar('settings');
    }
}