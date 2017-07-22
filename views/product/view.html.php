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

class JeproshopProductViewProduct extends JeproshopViewLegacy{
    protected $products;

    protected $product;

    protected $currency = NULL;

    protected $helper;

    protected $images = null;

    protected $languages = null;

    protected $multi_shop_check = false;

    protected $product_exists_in_shop = false;

    public function renderDetails($tpl = null){
        $productModel = new JeproshopProductModelProduct();
        $this->products = $productModel->getProductList();

        $this->pagination = $productModel->getPagination();

        if($this->getLayout() != 'modal'){
            $this->addToolBar();
        }
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $check_product_association_ajax = false;
        if (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_ALL){
            $check_product_association_ajax = true;
        }

        $this->assignRef('check_product_association_ajax', $check_product_association_ajax);
        $this->helper = new JeproshopHelper();
        $this->product = new JeproshopProductModelProduct();
        if($this->getLayout() != 'modal'){
            $this->addToolBar();
        } 
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        $app = JFactory::getApplication();

        $bulletCommonField = false;

        $this->addToolBar();

        if(JeproshopShopModelShop::isFeaturePublished()){
            if(JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_SHOP){
                $displayMultiShopCheckboxes = true;
                $multiShopCheck = $app->input->get('multishop_check');
            }

            if(JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_ALL){
                $bulletCommonField = '<i class="icon-circle text-orange"></i>';
                $displayCommonField = true;
            }
        }

        $this->languages = $this->context->controller->getLanguages();
        $defaultLanguageId = JeproshopSettingModelSetting::getValue('default_lang');
        $displayMultiShopCheckboxes = (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_SHOP);
        $this->getCombinationImagesJs();
        $this->assignRef('bullet_common_field', $bulletCommonField);

        if(JeproshopTools::isLoadedObject($this->product, 'product_id')){
            $productId = (int)$this->product->product_id;
        }else{
            $productId = (int)$app->input->get('product_id');
        }

        $uploadMaxFileSize = JeproshopTools::getOctets(ini_get('upload_max_filesize'));
        $uploadMaxFileSize = ($uploadMaxFileSize/1024)/1024;

        $countryDisplayTaxLabel = $this->context->country->display_tax_label;
        $hasCombinations = $this->product->hasAttributes();
        $this->product_exists_in_shop = true;

        if(JeproshopTools::isLoadedObject($this->product, 'product_id') && JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && !$this->product->isAssociatedToShop($this->context->shop->shop_id)){
            $this->product_exists_in_shop = false;

            $defaultProduct = new JeproshopProductModelProduct();
        }

        if($this->context->controller->default_form_language){
            $this->languages = $this->context->controller->getLanguages();
        }

        if($app->input->get('submit_form_ajax')){
            $this->context->controller->use_ajax = true;
        }

        $this->helper = new JeproshopHelper();

        /** prepare fields data **/
        $this->initInformationForm();
        $this->initPriceForm();
        $this->initAssociationsForm();
        $this->initAttributesForm();
        $this->initQuantitiesForm();
        $this->initImagesForm();
        $this->initCustomizationsForm();
        $this->initFeaturesForm();
        $this->initSuppliersForm();
        $this->initShippingForm();
        $this->initAttachmentForm();
        $this->assign('current_shop_url', $this->context->shop->getBaseURL()); 

        parent::display($tpl);
    }

    private function initInformationForm(){
        if (!$this->context->controller->default_form_language){
            $this->languages = $this->context->controller->getLanguages();
        }
        $app = JFactory::getApplication();
        $product_name_redirected = JeproshopProductModelProduct::getProductName((int)$this->product->product_redirected_id, null, (int)$this->context->language->lang_id);
        $this->assignRef('product_name_redirected', $product_name_redirected);

        /*
         * Form for adding a virtual product like software, mp3, etc...
         */
        $product_download = new JeproshopProductDownloadModelProductDownload();
        $product_download_id = $product_download->getProductDownloadIdFromProductId($this->product->product_id);
        if ($product_download_id){
            $product_download = new JeproshopProductDownloadModelProductDownload($product_download_id);
        }
        $this->product->productDownload = $product_download;

        $cache_default_attribute = (int)$this->product->cache_default_attribute;

        $product_props = array();
        // global information
        array_push($product_props, 'reference', 'ean13', 'upc',	'available_for_order', 'show_price', 'online_only',	'manufacturer_id');

        // specific / detailed information
        array_push($product_props,
            // physical product
            'width', 'height', 'weight', 'published',
            // virtual product
            'is_virtual', 'cache_default_attribute',
            // customization
            'uploadable_files', 'text_fields'
        );
        // prices
        array_push($product_props,
            'price', 'wholesale_price', 'tax_rules_group_id', 'unit_price_ratio', 'on_sale', 'unity',
            'minimal_quantity', 'additional_shipping_cost', 'available_now', 'available_later', 'available_date'
        );

        if(JeproshopSettingModelSetting::getValue('use_eco_tax')){
            array_push($product_props, 'ecotax');
        }

        $this->product->name['class'] = 'updateCurrentText';
        if (!$this->product->product_id || JeproshopSettingModelSetting::getValue('force_friendly_product')){
            $this->product->name['class'] .= ' copy2friendlyUrl';
        }

        $images = JeproshopImageModelImage::getImages($this->context->language->lang_id, $this->product->product_id);

        if (is_array($images)){
            foreach ($images as $k => $image){
                //$images[$k]->src = $this->context->controller->getImageLink($this->product->link_rewrite[$this->context->language->lang_id], $this->product->product_id.'-'.$image->image_id, 'small_default'); echo $images[$k]->src;
            }
            $this->assignRef('product_images', $images);
        }
        $imagesTypes = JeproshopImageTypeModelImageType::getImagesTypes('products');
        $this->assignRef('imagesTypes', $imagesTypes);

        $this->product->tags = JeproshopTagModelTag::getProductTags($this->product->product_id);

        $product_type = (int)$app->input->get('product_type', $this->product->getType());
        $this->assignRef('product_type', $product_type);
        $is_in_pack = (int)JeproshopProductPack::isPacked($this->product->product_id);
        $this->assignRef('is_in_pack', $is_in_pack);

        $check_product_association_ajax = false;
        if (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_ALL){
            $check_product_association_ajax = true;
        }

        $iso_tiny_mce = $this->context->language->iso_code;
        $iso_tiny_mce = (file_exists(JURI::base() . '/components/com_jeproshop/assets/javascript/tiny_mce/langs/'.$iso_tiny_mce.'.js') ? $iso_tiny_mce : 'en');
        $this->assignRef('iso_tiny_mce', $iso_tiny_mce);
        $this->assignRef('check_product_association_ajax', $check_product_association_ajax);
    }

    private function initPriceForm(){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        if($this->product->product_id){
            $shops = JeproshopShopModelShop::getShops();
            $countries = JeproshopCountryModelCountry::getStaticCountries($this->context->language->lang_id);
            $groups = JeproshopGroupModelGroup::getStaticGroups($this->context->language->lang_id);
            $currencies = JeproshopCurrencyModelCurrency::getStaticCurrencies();
            $attributes = $this->product->getAttributesGroups((int)$this->context->language->lang_id);
            $combinations = array();
            if(count($attributes)){
                foreach($attributes as $attribute){
                    $combinations[$attribute->product_attribute_id] = new JObject();
                    $combinations[$attribute->product_attribute_id]->product_attribute_id = $attribute->product_attribute_id;
                    if(!isset($combinations[$attribute->product_attribute_id]->attributes)){
                        $combinations[$attribute->product_attribute_id]->attributes = '';
                    }
                    if(isset($combinations[$attribute->product_attribute_id])){
                        $combinations[$attribute->product_attribute_id]->attributes .= $attribute->attribute_name . ' - ';

                        $combinations[$attribute->product_attribute_id]->price = JeproshopTools::displayPrice(
                            JeproshopTools::convertPrice(
                                JeproshopProductModelProduct::getStaticPrice((int)$this->product->product_id, false, $attribute->product_attribute_id),
                                $this->context->currency
                            ), $this->context->currency	);
                    }
                }

                foreach($combinations as $combination){
                    if(isset($combination->attributes )){
                        $combination->attributes = rtrim($combination->attributes, ' - ');
                    }
                }
            }
            $this->displaySpecificPriceModificationForm($this->context->currency, $shops, $currencies, $countries, $groups);

            $this->assignRef('ecotax_tax_excluded', $this->product->ecotax);
            //$this->applyTaxToEcotax();

            $this->assignRef('shops', $shops);
            /*$admin_one_shop = count($this->context->employee->getAssociatedShops()) == 1;
            $this->assignRef('admin_one_shop', $admin_one_shop); */
            $this->assignRef('currencies', $currencies);
            $this->assignRef('currency', $this->context->currency);
            $this->assignRef('countries', $countries);
            $this->assignRef('groups', $groups);
            $this->assignRef('combinations', $combinations);
            $multiShop = JeproshopShopModelShop::isFeaturePublished();
            $this->assignRef('multi_shop', $multiShop);
        }else{
            JError::raiseWarnig(JText::_('COM_JEPROSHOP_YOU_MUST_SAVE_THIS_PRODUCT_BEFORE_ADDING_SPECIFIC_PRICING_MESSAGE'));
            $this->product->tax_rules_group_id = JeproshopProductModelProduct::getTaxRulesMostUsedGroupId();
            $this->assignRef('ecotax_tax_excluded', 0);
        }
        $use_tax = JeproshopSettingModelSetting::getValue('use_tax');
        $this->assignRef('use_tax', $use_tax);
        $use_ecotax = JeproshopSettingModelSetting::getValue('use_eco_tax');
        $this->assignRef('use_ecotax', $use_ecotax);
        $tax_rules_groups = JeproshopTaxRulesGroupModelTaxRulesGroup::getTaxRulesGroups(true);
        $this->assignRef('tax_rules_groups', $tax_rules_groups);
        $taxesRatesByGroup = JeproshopTaxRulesGroupModelTaxRulesGroup::getAssociatedTaxRatesByCountryId($this->context->country->country_id);
        $this->assignRef('taxesRatesByGroup', $taxesRatesByGroup);
        $ecotaxTaxRate = JeproshopTaxModelTax::getProductEcotaxRate();
        $this->assignRef('ecotaxTaxRate', $ecotaxTaxRate);
        $tax_exclude_tax_option = JeproshopTaxModelTax::taxExcludedOption();
        $this->assignRef('tax_exclude_tax_option', $tax_exclude_tax_option);

        $this->product->price = JeproshopTools::convertPrice($this->product->price, $this->context->currency, true, $this->context);
        if($this->product->unit_price_ratio != 0){
            $unit_price = JeproshopTools::roundPrice($this->product->price / $this->product->unit_price_ratio, 2);
        }else{
            $unit_price = 0;
        }
        $this->assignRef('unit_price', $unit_price);
    }

    public function getCombinationImagesJs(){
        if (!$this->loadObject(true)) {
            $content = 'var combination_images = [];';
            $allCombinationImages = $this->product->getCombinationImages($this->context->language->lang_id);
            if (!$allCombinationImages) {
                return $content;
            }

            foreach ($allCombinationImages as $product_attribute_id => $combination_images) {
                $i = 0;
                $content .= 'combination_images[' . (int)$product_attribute_id . '] = [];';
                foreach ($combination_images as $combination_image) {
                    $content .= 'combination_images[' . (int)$product_attribute_id . '][' . $i++ . '] = ' . (int)$combination_image->image_id . ';';
                }
            }
            $this->assignRef('combination_images_js', $content);
        }
    }

    private function displaySpecificPriceModificationForm($defaultCurrency, $shops, $currencies, $countries, $groups){
        if(!$this->product){ return null; }

        $specificPrices = JeproshopSpecificPriceModelSpecificPrice::getSpecificPricesByProductId((int)$this->product->product_id);
        $specificPricePriorities = JeproshopSpecificPriceModelSpecificPrice::getPriority((int)$this->product->product_id);
        $app = JFactory::getApplication();
        $taxRate = $this->product->getTaxesRate(JeproshopAddressModelAddress::initialize());
      
        $this->assignRef('default_currency', $defaultCurrency);
        $this->assignRef('specific_prices', $specificPrices);
        $this->assignRef('specific_price_priorities', $specificPricePriorities);

        $tmp = array();
        foreach($shops as $shop){
            $tmp[$shop->shop_id] = $shop;
        }
        $shops = $tmp;
        $this->assignRef('shops', $shops);

        $tmp = array();
        foreach($currencies as $currency){
            $tmp[$currency->currency_id] = $currency;
        }
        $currencies = $tmp;
        $this->assignRef('currencies', $currencies);

        $tmp = array();
        foreach($countries as $country){
            $tmp[$country->country_id] = $country;
        }
        $countries = $tmp;
        $this->assignRef('countries', $countries);

        $tmp = array();
        foreach($groups as $group){
            $tmp[$group->group_id] = $group;
        }
        $groups = $tmp;
        $this->assignRef('groups', $groups);

        $this->assign('specific_price_modification_form', 1);
    }

    protected function productMultiShopCheckFields($product_tab){
        $scriptReturned = '';
        if(isset($this->display_multishop_checkboxes) && $this->display_multishop_checkboxes){
            $scriptReturned .= '<input style="float: none;" /><input type="checkbox" style="vertical-align:text-bottom" ';
            $scriptReturned .=' onclick="$(\'#jform_product_tab_content_' . $product_tab . ' input[name^=\'multi_shop_check[\']\').';
            $scriptReturned .= 'attr(\'checked\', this.checked); ProductMultiShop.checkAll' . $product_tab . '(); " />'; //]
            $scriptReturned .= JText::_('COM_JEPROSHOP_PRODUCT_PAGE_EDITING_MESSAGE') . '</label>';
        }
        return $scriptReturned;
    }

    protected function productMultiShopCheckbox($field, $type){
        $scriptReturned = '';
        if(isset($this->display_multishop_checkboxes) && $this->display_multishop_checkboxes){
            if(isset($this->multilang) && $this->multilang){
                if(isset($this->checkbox_only)){
                    foreach($this->languages as $language){
                        $scriptReturned .= '<input type="checkbox" name="multi_shop_check[' . $field . '][' . $language->lang_id . ']"';
                        $scriptReturned .= 'value="1" onclick="ProductMultiShop.checkField(this.checked, \'' . $field . '_' . $language->lang_id;
                        $scriptReturned .= '\', \'' . $type . '\' )" ';
                        if(!empty($this->multiShopCheck[$field][$language->lang_id])){
                            $scriptReturned .= 'checked="checked" ';
                        }
                        $scriptReturned .= ' />';
                    }
                }else{
                    $scriptReturned .= '<div class="multi_shop_product_checkbox" >';
                    foreach($this->languages as $language){
                        $scriptReturned .= '<div class="multi_shop_lang_' . $language->lang_id . '" ';
                        if(!$language->is_default){
                            $scriptReturned .= 'style="display:none; "';
                        }
                        $scriptReturned .= ' ><input type="checkbox" name="jform[multi_shop_check[' . $field . '][' . $language->lang_id . ']]';
                        $scriptReturned .= ' value="1" onclick="ProductMultiShop.checkField(this.checked, \'' . $field .'_' . $language->lang_id;
                        $scriptReturned .= '\', \'' . $type . '\' ); "';
                        if(!empty($this->multi_shop_check[$field][$language->lang_id])){
                            $scriptReturned .= ' checked="checked" ';
                        }
                        $scriptReturned .= '/></div>';
                    }
                    $scriptReturned .= '</div>';
                }
            }else{
                if(isset($this->checkbox_only)){
                    $scriptReturned .= '<input type="checkbox" name="jform[multi_shop_check[' . $field . ']" value="1" ';
                    $scriptReturned .= ' onclick="ProductMultiShop.checkField(this.checked, \'' . $field . '\', \'' . $type .'\' ); "';
                    if(!empty($this->multi_shop_check[$field])){
                        $scriptReturned .= ' checked="checked" ';
                    }
                    $scriptReturned .= '/>';
                }else{
                    $scriptReturned .= '<div class="multi_shop_product_checkbox"><input type="checkbox" name="jform[multi_shop_check[';
                    $scriptReturned .= $field . ']" value="1" onclick="ProductMultiShop.checkField(this.checked, \'' . $field . '\', \'' . $type .'\' ); "';
                    if(!empty($this->multi_shop_check[$field])){
                        $scriptReturned .= ' checked="checked" ';
                    }
                    $scriptReturned .= ' /></div>';
                }
            }
        }
        return $scriptReturned;
    }


    private function initAssociationsForm(){
        $app = JFactory::getApplication();
        /** prepare category tree **/
        $root = JeproshopCategoryModelCategory::getRootCategory();

        $default_category_id = $this->context->cookie->products_filter_category_id ? $this->context->cookie->products_filter_category_id : JeproshopContext::getContext()->shop->category_id;
        $categoryBox = $app->input->get('category_box', array($default_category_id));
        if(!$this->product->product_id || !$this->product->isAssociatedToShop()){
            $selected_category = JeproshopCategoryModelCategory::getCategoryInformation($categoryBox, $this->context->controller->default_form_language);
        }else{
            if($categoryBox){
                $selected_category = JeproshopCategoryModelCategory::getCategoryInformation($categoryBox);
            }else{
                $selected_category = JeproshopProductModelProduct::getProductCategoriesFull($this->product->product_id, $this->context->controller->default_form_language);
            }
        }

        // Multishop block
        $feature_shop_active = JeproshopShopModelShop::isFeaturePublished();
        $this->assignRef('feature_shop_published', $feature_shop_active);

        /** Accessories **/
        $accessories = JeproshopProductModelProduct::getAccessoriesLight($this->context->language->lang_id, $this->product->product_id);
        $postAccessories = $app->input->get('input_accessories');
        if($postAccessories){
            $postAccessoriesTab = explode('-', $postAccessories);
            foreach($postAccessoriesTab as $accessory_id){
                $accessory = JeproshopProductModelProduct::getAccessoryById($accessory_id);
                if(!$this->hasThisAccessory($accessory_id, $accessories) && $accessory){
                    $accessories[] = $accessory;
                }
            }
        }
        $this->assignRef('accessories', $accessories);
        $this->product->manufacturer_name = JeproshopManufacturerModelManufacturer::getNameById($this->product->manufacturer_id);

        $categories = array();
        foreach($selected_category as $key => $category){
            $categories[] = $key;
        }
        $manufacturers = JeproshopManufacturerModelManufacturer::getManufacturers($this->context->language->lang_id);
        $categories_tree = new JeproshopCategoriesTree('associated_categories_tree', JText::_('COM_JEPROSHOP_ASSOCIATED_CATEGORIES_LABEL'));
        $categories_tree->setTreeLayout('associated_categories')->setRootCategory((int)$root->category_id)->setUseCheckBox(true)->setSelectedCategories($categories);

        $this->assignRef('manufacturers', $manufacturers);
        $selected_category_ids = implode(',', array_keys($selected_category));
        $this->assignRef('selected_category_ids', $selected_category_ids);
        $this->assignRef('selected_category', $selected_category);
        $categoryId = $this->product->getDefaultCategoryId();
        $this->assignRef('default_category_id', $categoryId);
        $category_tree = $categories_tree->render();
        $this->assignRef('category_tree', $category_tree);
        $is_shop_context = JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP;
        $this->assignRef('is_shop_context', $is_shop_context);
    }


    private function initQuantitiesForm(){
        if(!$this->context->controller->default_form_language){
            $this->languages = $this->context->controller->getLanguages();
        }

        if($this->product->product_id){
            if($this->product_exists_in_shop){
                //Get all product_attribute_id
                $attributes = $this->product->getAttributesResume($this->context->language->lang_id);
                if(empty($attributes)){
                    $attributes[] = new JObject();
                    $attributes[0]->set('product_attribute_id', 0);
                    $attributes[0]->set('attribute_designation', '');
                }

                /** get available quantities **/
                $available_quantity = array();
                $product_designation = array();

                foreach($attributes as $attribute){
                    $product_attribute_id = is_object($attribute) ? $attribute->product_attribute_id : $attribute['product_attribute_id'];
                    $attribute_designation = is_object($attribute) ? $attribute->attribute_designation : $attribute['attribute_designation'];
                    // Get available quantity for the current product attribute in the current shop
                    $available_quantity[$product_attribute_id] = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct((int)$this->product->product_id,
                        $product_attribute_id);
                    // Get all product designation
                    $product_designation[$product_attribute_id] = rtrim(
                        $this->product->name[$this->context->language->lang_id].' - '.$attribute_designation, ' - '
                    );
                }

                $show_quantities = true;
                $shop_context = JeproshopShopModelShop::getShopContext();
                $shop_group = new JeproshopShopGroupModelShopGroup((int)JeproshopShopModelShop::getContextShopGroupID());

                // if we are in all shops context, it's not possible to manage quantities at this level
                if (JeproshopShopModelShop::isFeaturePublished() && $shop_context == JeproshopShopModelShop::CONTEXT_ALL){
                    $show_quantities = false;
                    // if we are in group shop context
                }elseif (JeproshopShopModelShop::isFeaturePublished() && $shop_context == JeproshopShopModelShop::CONTEXT_GROUP){
                    // if quantities are not shared between shops of the group, it's not possible to manage them at group level
                    if (!$shop_group->share_stock){ $show_quantities = false; }
                }else{
                    // if we are in shop context
                    // if quantities are shared between shops of the group, it's not possible to manage them for a given shop
                    if ($shop_group->share_stock){ $show_quantities = false; }
                }

                $stock_management = JeproshopSettingModelSetting::getValue('stock_management');
                $this->assignRef('stock_management', $stock_management);
                $has_attribute = $this->product->hasAttributes();
                $this->assignRef('has_attribute', $has_attribute);
                // Check if product has combination, to display the available date only for the product or for each combination
                $db = JFactory::getDBO();
                if(JeproshopCombinationModelCombination::isFeaturePublished()){
                    $query = "SELECT COUNT(product_id) FROM " . $db->quoteName('#__jeproshop_product_attribute') . " WHERE ";
                    $query .= " product_id = " . (int)$this->product->product_id;
                    $db->setQuery($query);
                    $countAttributes = (int)$db->loadResult();
                }else{
                    $countAttributes = false;
                }
                $this->assignRef('count_attributes', $countAttributes);
                // if advanced stock management is active, checks associations
                $advanced_stock_management_warning = false;
                if (JeproshopSettingModelSetting::getValue('advanced_stock_management') && $this->product->advanced_stock_management){
                    $product_attributes = JeproshopProductModelProduct::getProductAttributesIds($this->product->product_id);
                    $warehouses = array();

                    if (!$product_attributes){
                        $warehouses[] = JeproshopWarehouseModelWarehouse::getProductWarehouseList($this->product->product_id, 0);
                    }

                    foreach ($product_attributes as $product_attribute){
                        $ws = JeproshopWarehouseModelWarehouse::getProductWarehouseList($this->product->product_id, $product_attribute->product_attribute_id);
                        if ($ws){
                            $warehouses[] = $ws;
                        }
                    }
                    $warehouses = JeproshopTools::arrayUnique($warehouses);

                    if (empty($warehouses)){
                        $advanced_stock_management_warning = true;
                    }
                }

                if ($advanced_stock_management_warning){
                    JError::raiseWarning(500, JText::_('If you wish to use the advanced stock management, you must:'));
                    JError::raiseWarning(500, '- ' . JText::_('associate your products with warehouses.'));
                    JError::raiseWarning(500, '- ' . JText::_('associate your warehouses with carriers.'));
                    JError::raiseWarning(500, '- ' . JText::_('associate your warehouses with the appropriate shops.'));
                }

                $pack_quantity = null;

                // if product is a pack
                if (JeproshopProductPack::isPack($this->product->product_id)){
                    $items = JeproshopProductPack::getItems((int)$this->product->product_id, JeproshopSettingModelSetting::getValue('default_lang'));

                    // gets an array of quantities (quantity for the product / quantity in pack)
                    $pack_quantities = array();
                    foreach ($items as $item){
                        if (!$item->isAvailableWhenOutOfStock((int)$item->out_of_stock)){
                            $pack_id_product_attribute = JeproshopProductModelProduct::getDefaultAttribute($item->product_id, 1);
                            $pack_quantities[] = JeproshopProductModelProduct::getQuantity($item->id, $pack_id_product_attribute) / ($item->pack_quantity !== 0 ? $item->pack_quantity : 1);
                        }
                    }

                    // gets the minimum
                    if (count($pack_quantities)){
                        $pack_quantity = $pack_quantities[0];
                        foreach ($pack_quantities as $value){
                            if ($pack_quantity > $value){
                                $pack_quantity = $value;
                            }
                        }
                    }

                    if (!JeproshopWarehouseModelWarehouse::getPackWarehouses((int)$this->product->product_id))
                        JeproshopTools::displayWarning(JText::_('You must have a common warehouse between this pack and its product.'));
                }

                $this->assignRef('attributes', $attributes);
                $this->assignRef('available_quantity', $available_quantity);
                $this->assignRef('pack_quantity', $pack_quantity);
                $stock_management_active = JeproshopSettingModelSetting::getValue('advanced_stock_management');
                $this->assignRef('stock_management_active', $stock_management_active);
                $this->assignRef('product_designation', $product_designation);
                $this->assignRef('show_quantities', $show_quantities);
                $order_out_of_stock = JeproshopSettingModelSetting::getValue('allow_out_of_stock_ordering');
                $this->assignRef('order_out_of_stock', $order_out_of_stock);
                /*'token_preferences' => Tools::getAdminTokenLite('AdminPPreferences'),
                'token' => $this->token,
                'languages' => $this->_languages,
                'id_lang' => $this->context->language->id
        ));*/
            }else{
                JError::raiseWarning(500, JText::_('You must save the product in this shop before managing quantities.'));
            }
        }else{
            JError::raiseWarning(500, JText::_('You must save this product before managing quantities.'));
        }
    }

    private function initShippingForm(){
        $dimension_unit = JeproshopSettingModelSetting::getValue('dimension_unit');
        $this->assignRef('dimension_unit', $dimension_unit);
        $weight_unit = JeproshopSettingModelSetting::getValue('weight_unit');
        $this->assignRef('weight_unit', $weight_unit);
        $carrier_list = $this->getCarrierList();
        $this->assignRef('carrier_list', $carrier_list);
    }

    protected function getCarrierList(){
        $carrier_list = JeproshopCarrierModelCarrier::getCarriers($this->context->language->lang_id, false, false, false, null, JeproshopCarrierModelCarrier::JEPROSHOP_ALL_CARRIERS);
        if ($this->product){
            $carrier_selected_list = $this->product->getCarriers();
            foreach ($carrier_list as &$carrier){
                foreach ($carrier_selected_list as $carrier_selected){
                    if ($carrier_selected->reference_id == $carrier->reference_id){
                        $carrier->selected = true;
                        continue;
                    }
                }
            }
        }
        return $carrier_list;
    }

    private function initAttributesForm(){
        if(!JeproshopCombinationModelCombination::isFeaturePublished()){
            $settingPanelLink = '<a href="#" >' . JText::_('COM_JEPROSHOP_PERFORMANCE_LABEL') . '</a>';
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_FEATURE_HAS_BEEN_DISABLED_MESSAGE') . $settingPanelLink);
        }elseif(JeproshopTools::isLoadedObject($this->product, 'product_id')){
            if($this->product_exists_in_shop){
                if($this->product->is_virtual){
                    JError:raiseWarning(500, JText::_('COM_JEPROSHOP_VIRTUAL_PRODUCT_CANNOT_HAVE_COMBINATIONS'));
                }else{
                    $attribute_js = array();
                    $attributes = JeproshopAttributeModelAttribute::getAttributes($this->context->language->lang_id, true);
                    if($attributes){
                        foreach($attributes as $key => $attribute){
                            $attribute_js[$attribute->attribute_group_id][$attribute->attribute_id] = $attribute->name;
                        }
                    }
                    $this->assignRef('attributeJs', $attribute_js);
                    $attributes_groups =  JeproshopAttributeGroupModelAttributeGroup::getAttributesGroups($this->context->language->lang_id);
                    $this->assignRef('attributes_groups',$attributes_groups);

                    $images = JeproshopImageModelImage::getImages($this->context->language->lang_id, $this->product->product_id);
                    $weight_unit = JeproshopSettingModelSetting::getValue('weight_unit');
                    $this->assignRef('weight_unit', $weight_unit);
                    $reasons = JeproshopStockMovementReasonModelStockMovementReason::getStockMovementReasons();
                    $this->assignRef('reasons', $reasons);
                    //$this->assignRef('minimal_quantity', );
                    $this->assignRef('available_date', $available_date);
                    $stock_mvt_default_reason = JeproshopSettingModelSetting::getValue('default_stock_mvt_reason');
                    $this->assignRef('default_stock_mvt_reason', $stock_mvt_default_reason);

                    $i = 0;
                    /*$type = JeproshopImageTypeModelImageType::getByNameNType('%', 'products', 'height');
                    if (isset($type->name)){
                        $data->assign('imageType', $type['name']);
                    }else
                        $data->assign('imageType', 'small_default'); */
                    //$this->assignRef('imageWidth', (isset($image_type->width) ? (int)($image_type->width) : 64) + 25);
                    foreach ($images as $k => $image){
                        $images[$k]->obj = new JeproshopImageModelImage($image->image_id);
                        ++$i;
                    }
                    $this->assignRef('attribute_images', $images);
                    $attributeList = $this->renderAttributesList($this->product, $this->currency);
                    $this->assignRef('attribute_list', $attributeList);
                    $combination_exists = (JeproshopShopModelShop::isFeaturePublished() && (JeproshopShopModelShop::getContextShopGroup()->share_stock) && count(JeproshopAttributeGroupModelAttributeGroup::getAttributesGroups($this->context->language->lang_id)) > 0 && $this->product->hasAttributes());
                    $this->assignRef('combination_exists', $combination_exists);
                }
            }
        }
    }


    private function initSuppliersForm(){
        if ($this->product->product_id){
            if ($this->product_exists_in_shop){
                // Get all id_product_attribute
                $attributes = $this->product->getAttributesResume($this->context->language->lang_id);
                if (empty($attributes)){
                    $attribute = new JeproshopAttributeModelAttribute();
                    $attribute->product_id = $this->product->product_id;
                    $attribute->product_attribute_id = 0;
                    $attribute->attribute_designation = '';
                    $attributes[] = $attribute;
                }
                $product_designation = array();

                foreach ($attributes as $attribute){
                    $product_designation[$attribute->product_attribute_id] = rtrim(
                        $this->product->name[$this->context->language->lang_id] . ' - '. $attribute->attribute_designation, ' - '
                    );
                }

                // Get all available suppliers
                $suppliers = JeproshopSupplierModelSupplier::getSuppliers();

                // Get already associated suppliers
                $associated_suppliers = JeproshopProductSupplierModelProductSupplier::getSuppliers($this->product->product_id);

                // Get already associated suppliers and force to retrieve product declinations
                $product_supplier_collection = JeproshopProductSupplierModelProductSupplier::getSuppliers($this->product->product_id, false);

                $default_supplier = 0;
                if(count($suppliers) > 0){
                    foreach ($suppliers as &$supplier){
                        $supplier->is_selected = false;
                        $supplier->is_default = false;

                        foreach ($associated_suppliers as $associated_supplier){
                            if ($associated_supplier->supplier_id == $supplier->supplier_id){
                                $associated_supplier->name = $supplier->name;
                                $supplier->is_selected = true;

                                if ($this->product->supplier_id == $supplier->supplier_id){
                                    $supplier->is_default = true;
                                    $default_supplier = $supplier->supplier_id;
                                }
                            }
                        }
                    }
                }

                $this->assignRef('attributes', $attributes);
                $this->assignRef('suppliers', $suppliers);
                $this->assignRef('default_supplier', $default_supplier);
                $this->assignRef('associated_suppliers', $associated_suppliers);
                $this->assignRef('associated_suppliers_collection', $product_supplier_collection);
                $this->assignRef('product_designation', $product_designation);
                /*$this->assignRef(			'currencies' => Currency::getCurrencies(),

                            'link' => $this->context->link,
                            'token' => $this->token,));*/
                $default_currency_id = JeproshopSettingModelSetting::getValue('default_currency');
                $this->assignRef('default_currency_id', $default_currency_id);

            }
            else
                JeproshopTools::displayWarning(JText::_('You must save the product in this shop before managing suppliers.'));
        }
        else
            JeproshopTools::displayWarning(JText::_('You must save this product before managing suppliers.'));

        //$this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    private function initAttachmentForm(){
        if (!$this->context->controller->default_form_language){
            $this->languages = $this->context->controller->getLanguages();
        }

        if ((bool)$this->product->product_id){
            if ($this->product_exists_in_shop){
                $attachment_name = array();
                $attachment_description = array();
                foreach ($this->languages as $language){
                    $attachment_name[$language->lang_id] = '';
                    $attachment_description[$language->lang_id] = '';
                }

                $iso_tiny_mce = (file_exists(COM_JEPROSHOP_JS_DIR . DIRECTORY_SEPARATOR .'tiny_mce/langs/'. $this->context->language->iso_code .'.js') ? $this->context->language->iso_code : 'en');

                $attachment_link = JRoute::_('index.php?option=com_jeproshop&view=product&ajax=1&task=add_attachment&product_id=' . (int)$this->product->product_id);
                $attachment_uploader = new JeproshopFileUploader('attachment_file');
                $attachment_uploader->setMultiple(false)->setUseAjax(true)->setUrl($attachment_link)
                    ->setPostMaxSize((JeproshopSettingModelSetting::getValue('attachment_maximum_size') * 1024 * 1024));
                //->setTemplate('attachment_ajax.tpl');
                /*
                        $data->assign(array(
                                'obj' => $obj,
                                'table' => $this->table,
                                'ad' => __PS_BASE_URI__.basename(_PS_ADMIN_DIR_),
                                'iso_tiny_mce' => $iso_tiny_mce,
                                'languages' => $this->_languages,
                                'id_lang' => $this->context->language->id,; */
                $attachments_1 = JeproshopAttachmentModelAttachment::getAttachments($this->context->language->lang_id, $this->product->product_id, true);
                $this->assignRef('attachments_1', $attachments_1);
                $attachments_2 = JeproshopAttachmentModelAttachment::getAttachments($this->context->language->lang_id, $this->product->product_id, false);
                $this->assignRef('attachments_2', $attachments_2);
                $this->assignRef('attachment_name', $attachment_name);
                $this->assignRef('attachment_description', $attachment_description);
                $attachment_maximum_size = JeproshopSettingModelSetting::getValue('attachment_maximum_size');
                $this->assignRef('attachment_maximum_size', $attachment_maximum_size);
                $attachment_uploader = $attachment_uploader->render();
                $this->assignRef('attachment_uploader', $attachment_uploader);
            }else
                JeproshopTools::displayWarning(JText::_('You must save the product in this shop before adding attachements.'));
        }
        else
            JeproshopTools::displayWarning(JText::_('You must save this product before adding attachements.'));
    }

    private function initCustomizationsForm(){
        if ((bool)$this->product->product_id){
            if ($this->product_exists_in_shop){
                $labels = $this->product->getCustomizationFields();

                $has_file_labels = (int)$this->product->uploadable_files;
                $has_text_labels = (int)$this->product->text_fields;

                $this->assignRef('has_file_labels', $has_file_labels);
                $displayFileLabels = $this->displayLabelFields($obj, $labels, JeproshopSettingModelSetting::getValue('default_lang'), JeproshopProductModelProduct::CUSTOMIZE_FILE);
                $this->assignRef('display_file_labels', $displayFileLabels);
                $this->assignRef('has_text_labels', $has_text_labels);
                $displayTextLabels = $this->displayLabelFields($obj, $labels, JeproshopSettingModelSetting::getValue('default_lang'), JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD);
                $this->assignRef('display_text_labels', $displayTextLabels);
                $uploadable_files = (int)($this->product->uploadable_files ? (int)$this->product->uploadable_files : '0');
                $this->assignRef('uploadable_files', $uploadable_files);
                $text_fields = (int)($this->product->text_fields ? (int)$this->product->text_fields : '0');
                $this->assignRef('text_fields', $text_fields);

            }
            else
                JeproshopTools::displayWarning(JText::_('You must save the product in this shop before adding customization.'));
        }
        else
            JeproshopTools::displayWarning(JText::_('You must save this product before adding customization.'));

    }


    private function initImagesForm(){
        if ((bool)$this->product->product_id){
            if ($this->product_exists_in_shop){
                $shops = false;
                if (JeproshopShopModelShop::isFeaturePublished()){
                    $shops = JeproshopShopModelShop::getShops();
                }
                if ($shops){
                    foreach ($shops as $key => $shop){
                        if (!$this->product->isAssociatedToShop($shop->shop_id)){
                            unset($shops[$key]);
                        }
                    }
                }
                $this->assignRef('shops', $shops);
                $db = JFactory::getDBO();
                $app = JFactory::getApplication();

                $query = "SELECT COUNT(product_id) FROM " . $db->quoteName('#__jeproshop_image');
                $query .= " WHERE product_id = " .(int)$this->product->product_id;
                $db->setQuery($query);
                $count_images = $db->loadResult();

                $images = JeproshopImageModelImage::getImages($this->context->language->lang_id, $this->product->product_id);
                foreach ($images as $k => $image){
                    $images[$k] = new JeproshopImageModelImage($image->image_id);
                }

                if ($this->context->shop->getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP){
                    $current_shop_id = (int)$this->context->shop->shop_id;
                }else{
                    $current_shop_id = 0;
                }

                $languages = JeproshopLanguageModelLanguage::getLanguages(true);
                $image_uploader = new JeproshopImageUploader('file');
                $image_link = JRoute::_('index.php?option=com_jeproshop&view=product&ajax=1&product_id=' . (int)$this->product->product_id .'&task=add_product_image');
                $image_uploader->setMultiple(!(JeproshopTools::getUserBrowser() == 'Apple Safari' && JeproshopTools::getUserPlatform() == 'Windows'))
                    ->setUseAjax(true)->setUrl($image_link);


                $this->assignRef('countImages', $count_images);
                /*$this->assignRef(
                        'id_product' => (int)Tools::getValue('id_product'),
                        'id_category_default' => (int)$this->_category->id, */
                $this->assignRef('images', $images);
                /*'iso_lang' => $languages[0]['iso_code'],
                'token' =>  $this->token,
                'table' => $this->table,*/
                $image_size = ((int)JeproshopSettingModelSetting::getValue('product_picture_max_size') / 1024 / 1024);
                $this->assignRef('max_image_size', $image_size);
                $virtualProductFilenameAttribute = (string)$app->input->get('virtual_product_filename_attribute');
                $this->assignRef('up_filename', $virtualProductFilenameAttribute);
                //'currency' => $this->context->currency,
                $this->assignRef('current_shop_id', $current_shop_id);
                //		'languages' => $this->_languages,
                //		'default_language' => (int)Configuration::get('PS_LANG_DEFAULT'),
                $imageUploader = $image_uploader->render();
                $this->assignRef('image_uploader', $imageUploader);
                //));

                $type = JeproshopImageTypeModelImageType::getByNameNType('%', 'products', 'height');
                if (isset($type->name)){
                    $imageType = $type->name;
                }else{
                    $imageType = 'small_default';
                }
                $this->assignRef('image_type', $imageType);
            }
            else
                JeproshopTools::displayWarning(JText::_('You must save the product in this shop before adding images.'));
        }
    }

    private function initFeaturesForm(){
        if (!$this->context->controller->default_form_language){ $this->context->controller->getLanguages(); }

        /*$data = $this->createTemplate($this->tpl_form);
        $data->assign('default_form_language', $this->default_form_language); */

        if (!JeproshopFeatureModelFeature::isFeaturePublished()){
            //JeproshopTools::displayWarning(JText::_('This feature has been disabled. ').' <a href="index.php?tab=AdminPerformance&token='.Tools::getAdminTokenLite('AdminPerformance').'#featuresDetachables">'.$this->l('Performances').'</a>');
        }else{
            if ($this->product->product_id){
                if ($this->product_exists_in_shop){
                    $features = JeproshopFeatureModelFeature::getFeatures($this->context->language->lang_id, (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP));

                    foreach ($features as $k => $feature){
                        $features[$k]->current_item = false;
                        $features[$k]->val = array();

                        $custom = true;
                        foreach ($this->product->getFeatures() as $products){
                            if ($products->feature_id == $features->feature_id){
                                $features[$k]->current_item = $products->feature_value_id;
                            }
                        }
                        $features[$k]->featureValues = JeproshopFeatureValueModelFeatureValue::getFeatureValuesWithLang($this->context->language->lang_id, (int)$feature->feature_id);
                        if (count($features[$k]->featureValues)){
                            foreach ($features[$k]->featureValues as $value){
                                if ($features[$k]->current_item == $value->feature_value_id){
                                    $custom = false;
                                }
                            }
                        }
                        if ($custom){
                            $features[$k]->val = JeproshopFeatureValueModelFeatureValue::getFeatureValueLang($features[$k]->current_item);
                        }
                    }

                    $this->assignRef('available_features', $features);

                    /*$data->assign('product', $obj);
                    $data->assign('link', $this->context->link);
                    $data->assign('languages', $this->_languages);
                    $data->assign('default_form_language', $this->default_form_language); */
                }
                else
                    JeproshopTools::displayWarning(JText::_('You must save the product in this shop before adding features.'));
            }
            else
                JeproshopTools::displayWarning(JText::_('You must save this product before adding features.'));
        }

    }

    protected function displayLabelFields(&$obj, &$labels, $default_language, $type){
        $content = '';
        $type = (int)($type);
        $labelGenerated = array(JeproshopProductModelProduct::CUSTOMIZE_FILE => (isset($labels[JeproshopProductModelProduct::CUSTOMIZE_FILE]) ? count($labels[JeproshopProductModelProduct::CUSTOMIZE_FILE]) : 0), JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD => (isset($labels[JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD]) ? count($labels[JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD]) : 0));

        $fieldIds = $this->product->getCustomizationFieldIds($labels, $labelGenerated, $obj);
        if (isset($labels[$type]))
            foreach ($labels[$type] as $id_customization_field => $label)
                $content .= $this->displayLabelField($label, $default_language, $type, $fieldIds, (int)($id_customization_field));
        return $content;
    }

    /**
     * @param JeproshopProductModelProduct $product
     * @param JeproshopCurrencyModelCurrency|array|int $currency
     * @return string
     */
    public function renderAttributesList($product, $currency){
        /*$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
        $this->addRowAction('edit');
        $this->addRowAction('default');
        $this->addRowAction('delete');

        $default_class = 'highlighted';

        $this->fields_list = array(
            'attributes' => array('title' => $this->l('Attribute - value pair'), 'align' => 'left'),
            'price' => array('title' => $this->l('Impact on price'), 'type' => 'price', 'align' => 'left'),
            'weight' => array('title' => $this->l('Impact on weight'), 'align' => 'left'),
            'reference' => array('title' => $this->trans('Reference', array(), 'Admin.Global'), 'align' => 'left'),
            'ean13' => array('title' => $this->l('EAN-13'), 'align' => 'left'),
            'isbn' => array('title' => $this->l('ISBN'), 'align' => 'left'),
            'upc' => array('title' => $this->l('UPC'), 'align' => 'left')
        );

        if ($product->product_id){
            /* Build attributes combinations * /
            $combinations = $product->getAttributeCombinations($this->context->language->lang_id);
            $groups = array();
            $comb_array = array();
            if (is_array($combinations)) {
                $combination_images = $product->getCombinationImages($this->context->language->lang_id);
                foreach ($combinations as $k => $combination) {
                    $price_to_convert = Tools::convertPrice($combination['price'], $currency);
                    $price = Tools::displayPrice($price_to_convert, $currency);

                    $comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = array($combination['group_name'], $combination['attribute_name'], $combination['id_attribute']);
                    $comb_array[$combination['id_product_attribute']]['wholesale_price'] = $combination['wholesale_price'];
                    $comb_array[$combination['id_product_attribute']]['price'] = $price;
                    $comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'].Configuration::get('PS_WEIGHT_UNIT');
                    $comb_array[$combination['id_product_attribute']]['unit_impact'] = $combination['unit_price_impact'];
                    $comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
                    $comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
                    $comb_array[$combination['id_product_attribute']]['isbn'] = $combination['isbn'];
                    $comb_array[$combination['id_product_attribute']]['upc'] = $combination['upc'];
                    $comb_array[$combination['id_product_attribute']]['id_image'] = isset($combination_images[$combination['id_product_attribute']][0]['id_image']) ? $combination_images[$combination['id_product_attribute']][0]['id_image'] : 0;
                    $comb_array[$combination['id_product_attribute']]['available_date'] = strftime($combination['available_date']);
                    $comb_array[$combination['id_product_attribute']]['default_on'] = $combination['default_on'];
                    if ($combination['is_color_group']) {
                        $groups[$combination['id_attribute_group']] = $combination['group_name'];
                    }
                }
            }

            if (isset($comb_array)) {
                foreach ($comb_array as $id_product_attribute => $product_attribute) {
                    $list = '';

                    /* In order to keep the same attributes order * /
                    asort($product_attribute['attributes']);

                    foreach ($product_attribute['attributes'] as $attribute) {
                        $list .= $attribute[0].' - '.$attribute[1].', ';
                    }

                    $list = rtrim($list, ', ');
                    $comb_array[$id_product_attribute]['image'] = $product_attribute['id_image'] ? new Image($product_attribute['id_image']) : false;
                    $comb_array[$id_product_attribute]['available_date'] = $product_attribute['available_date'] != 0 ? date('Y-m-d', strtotime($product_attribute['available_date'])) : '0000-00-00';
                    $comb_array[$id_product_attribute]['attributes'] = $list;
                    $comb_array[$id_product_attribute]['name'] = $list;

                    if ($product_attribute['default_on']) {
                        $comb_array[$id_product_attribute]['class'] = $default_class;
                    }
                }
            }
        }

        foreach ($this->actions_available as $action) {
            if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action) {
                $this->actions[] = $action;
            }
        }

        $helper = new HelperList();
        $helper->identifier = 'id_product_attribute';
        $helper->table_id = 'combinations-list';
        $helper->token = $this->token;
        $helper->currentIndex = self::$currentIndex;
        $helper->no_link = true;
        $helper->simple_header = true;
        $helper->show_toolbar = false;
        $helper->shopLinkType = $this->shopLinkType;
        $helper->actions = $this->actions;
        $helper->list_skip_actions = $this->list_skip_actions;
        $helper->colorOnBackground = true;
        $helper->override_folder = $this->tpl_folder.'combination/'; */

        return array(); //$helper->generateList($comb_array, $this->fields_list);
    }





    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch($task){
            case 'edit':
                JToolbarHelper::title(JText::_('COM_JEPROSHOP_PRODUCT_EDIT_PRODUCT_TITLE'), 'product-jeproshop');
                JToolbarHelper::apply('update', JText::_('COM_JEPROSHOP_UPDATE_LABEL'));
                JToolbarHelper::custom('sales', 'sales.png', 'sales.png', JText::_('COM_JEPROSHOP_SALES_LABEL'), true);
                JToolbarHelper::deleteList('delete');
                JToolbarHelper::cancel('cancel');
                break;
            case 'add':
                JToolbarHelper::title(JText::_('COM_JEPROSHOP_PRODUCT_ADD_PRODUCT_TITLE'), 'jeproshop-product');
                JToolbarHelper::apply('save');
                JToolbarHelper::save('save_close');
                JToolbarHelper::save2new('add2new');
                JToolbarHelper::cancel('cancel');
                break;
            default:
                JToolbarHelper::title(JText::_('COM_JEPROSHOP_PRODUCTS_LIST_TITLE'), 'product-jeproshop');
                JToolbarHelper::addNew('add');
                JToolbarHelper::editList('edit');
                JToolbarHelper::publish('publish');
                JToolbarHelper::unpublish('unpublish');
                JToolbarHelper::trash('delete');
                JToolbarHelper::preferences('com_jeproshop');
                JToolbarHelper::help('COM_JEPROSHOP_PRODUCT_MANAGER');

                $filter_product_type_options = '<option value="1" >' . JText::_('COM_JEPROSHOP_PRODUCT_SIMPLE_PRODUCT_LABEL'). '</option>';
                $filter_product_type_options .= '<option value="2" >' . JText::_('COM_JEPROSHOP_PRODUCT_PACKAGE_LABEL'). '</option>';
                $filter_product_type_options .= '<option value="3" >' . JText::_('COM_JEPROSHOP_PRODUCT_VIRTUAL_LABEL'). '</option>';
                JHtmlSidebar::addFilter(JText::_('COM_JEPROSHOP_SELECT_PRODUCT_TYPE_LABEL'), 'jform[filter_product_type]', $filter_product_type_options, FALSE);
                $filter_state_options = '<option value="1" >' . JText::_('COM_JEPROSHOP_FILTER_LABEL'). '</option>';
                JHtmlSidebar::addFilter(JText::_('COM_JEPROSHOP_SELECT_STATUS_LABEL'), 'jform[filter_state]', $filter_state_options, FALSE);
                $categories = JeproshopCategoryModelCategory::getCategories();
                $filter_category_options = '';
                foreach ($categories as $category){
                    $filter_category_options .= '<option value="'. $category->category_id . '" >' . ucfirst($category->name). '</option>';
                }
                JHtmlSidebar::addFilter(JText::_('COM_JEPROSHOP_SELECT_CATEGORY_LABEL'), 'jform[filter_category]', $filter_category_options, FALSE);
                $manufacturers = JeproshopManufacturerModelManufacturer::getManufacturers();
                $filter_manufacturers_options = '';
                foreach ($manufacturers as $manufacturer){
                    $filter_manufacturers_options .= '<option value="'. (int)$manufacturer->manufacturer_id . '" >' . ucfirst($manufacturer->name). '</option>';
                }
                JHtmlSidebar::addFilter(JText::_('COM_JEPROSHOP_SELECT_MANUFACTURER_LABEL'), 'jform[filter_manufacturer]', $filter_manufacturers_options, FALSE);
                $suppliers = JeproshopSupplierModelSupplier::getSuppliers();
                $filter_suppliers_options = '';
                foreach($suppliers as $supplier){
                    $filter_suppliers_options .= '<option value="' . (int)$supplier->supplier_id . '" >' . ucfirst($supplier->name) . '</option>';
                }
                JHtmlSidebar::addFilter(JText::_('COM_JEPROSHOP_SELECT_SUPPLIER_LABEL'), 'jform[filter_supplier]', $filter_suppliers_options, FALSE);
                break;
        }
        $this->addSideBar('catalog');
    }
    

    public function loadObject($option = false){
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


}