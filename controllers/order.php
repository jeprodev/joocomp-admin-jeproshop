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

class JeproshopOrderController extends JeproshopController{
    public function search(){
        $app = JFactory::getApplication();
        $useAjax = $app->input->get('use_ajax');
        $tab = $app->input->getWord('tab');
        $jsonData = array("success" =>false, "found" => false);
        $customerId = $app->input->get('customer_id');
        $currencyId = $app->input->get('currency_id');

        if(isset($tab) && $tab != '') {
            $context = JeproshopContext::getContext();
            switch ($tab) {
                case 'products' :
                    $context->customer = new JeproshopCustomerModelCustomer((int)$customerId);
                    $currency = new JeproshopCurrencyModelCurrency((int)$currencyId);
                    $productSearch = $app->input->get('product_search');
                    if ($products = JeproshopProductModelProduct::searchProductsByName((int)$context->language->lang_id, $productSearch)){
                        $productArray = array();
                        $addressId = $app->input->getInt('address_id', 0);
                        foreach ($products as &$product) {
                            $productData = array();
                            // Formatted price
                            $productData['formatted_price'] = JeproshopTools::displayPrice(JeproshopTools::convertPrice($product->price_tax_incl, $currency), $currency);
                            // Concrete price
                            $productData['price_tax_incl'] = JeproshopTools::roundPrice(JeproshopTools::convertPrice($product->price_tax_incl, $currency), 2);
                            $productData['price_tax_excl'] = JeproshopTools::roundPrice(JeproshopTools::convertPrice($product->price_tax_excl, $currency), 2);
                            $productObj = new JeproshopProductModelProduct((int)$product->product_id, false, (int)$context->language->lang_id);
                            $combinations = array();
                            $attributes = $productObj->getAttributesGroups((int)$context->language->lang_id);

                            // Tax rate for this customer
                            if ($addressId > 0) {
                                $productData['tax_rate'] = $productObj->getTaxesRate(new JeproshopAddressModelAddress($addressId));
                            }

                            $productData['warehouse_list'] = array();

                            foreach ($attributes as $attribute) {
                                if (!isset($combinations[$attribute->product_attribute_id]['attributes'])) {
                                    $combinations[$attribute->product_attribute_id]['attributes'] = '';
                                }
                                $combinations[$attribute->product_attribute_id]['attributes'] .= $attribute->attribute_name . ' - ';
                                $combinations[$attribute->product_attribute_id]['product_attribute_id'] = $attribute->product_attribute_id;
                                $combinations[$attribute->product_attribute_id]['default_on'] = $attribute->default_on;
                                if (!isset($combinations[$attribute->product_attribute_id]['price'])) {
                                    $priceTaxIncluded = JeproshopProductModelProduct::getStaticPrice((int)$product->product_id, true, $attribute->product_attribute_id);
                                    $priceTaxExcluded = JeproshopProductModelProduct::getStaticPrice((int)$product->product_id, false, $attribute->product_attribute_id);
                                    $combinations[$attribute->product_attribute_id]['price_tax_incl'] = JeproshopTools::roundPrice(JeproshopTools::convertPrice($priceTaxIncluded, $currency), 2);
                                    $combinations[$attribute->product_attribute_id]['price_tax_excl'] = JeproshopTools::roundPrice(JeproshopTools::convertPrice($priceTaxExcluded, $currency), 2);
                                    $combinations[$attribute->product_attribute_id]['formatted_price'] = JeproshopTools::displayPrice(JeproshopTools::convertPrice($priceTaxExcluded, $currency), $currency);
                                }
                                if (!isset($combinations[$attribute->product_attribute_id]['quantity_in_stock'])) {
                                    $combinations[$attribute->product_attribute_id]['quantity_in_stock'] = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct((int)$product->product_id, $attribute->product_attribute_id, (int)$context->shop->shop_id);
                                }

                                if(JeproshopSettingModelSetting::getValue('advanced_stock_management') && (int)$product->advanced_stock_management == 1) {
                                    $productData['warehouse_list'][$attribute->product_attribute_id] = JeproshopWarehouseModelWarehouse::getProductWarehouseList($product->product_id, $attribute->product_attribute_id);
                                } else {
                                    $productData['warehouse_list'][$attribute->product_attribute_id] = array();
                                }

                                $productData['stock'][$attribute->product_attribute_id] = JeproshopProductModelProduct::getRealQuantity($product->product_id, $attribute->product_attribute_id);
                            }

                            if (JeproshopSettingModelSetting::getValue('advanced_stock_management') && (int)$product->advanced_stock_management == 1) {
                                $productData['warehouse_list'][0] = JeproshopWarehouseModelWarehouse::getProductWarehouseList($product->product_id);
                            } else {
                                $productData['warehouse_list'][0] = array();
                            }

                            $productData['stock'][0] = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct((int)$product->product_id, 0, (int)$context->shop->shop_id);

                            foreach ($combinations as &$combination) {
                                $combination['attributes'] = rtrim($combination['attributes'], ' - ');
                            }
                            $productData['combinations'] = $combinations;

                            if ($product->customizable) {
                                $productInstance = new JeproshopProductModelProduct((int)$product->product_id);
                                $productData['customization_fields'] = $productInstance->getCustomizationFields($context->language->lang_id);
                            }else{
                                $productData['customization_fields'] = array();
                            }
                            $productArray[] = $productData;
                        }

                        $jsonData = array("success" => true, "found" => true, 'products' => $productArray);
                    }
                    break;
            }
        }

        if($useAjax){
            $document = JFactory::getDocument();
            $document->setMimeEncoding('application/json');
            echo json_encode($jsonData);
            $app->close();
        }
    }
}