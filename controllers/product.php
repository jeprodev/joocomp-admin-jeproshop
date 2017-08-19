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

class JeproshopProductController extends JeproshopController{
    public $current_category_id;
    
    public function initContent($token = null){
        $app = JFactory::getApplication();
        $context = JeproshopContext::getContext();
        $task = $app->input->get('task');
        $view = $app->input->get('view');

        if($task == 'add' || $task == 'edit'){
            /*$viewClass = $this->getView($view, JFactory::getDocument()->getType());
            if(!$viewClass->loadObject(true)){ return false; }
            $viewClass->setLayout('edit');
            $viewClass->renderEditForm();*/
        }else{
            if($categoryId = (int)$this->current_category_id){
                self::$_current_index .= '&category_id=' . (int)$this->current_category_id;
            }

            if(!$categoryId){
                $this->_defaultOrderBy = 'product';
                if(isset($context->cookie->product_order_by) && $context->cookie->product_order_by == 'position'){
                    unset($context->cookie->product_order_by);
                    unset($context->cookie->product_order_way);
                }
                //$category_id = 1;
            }
        }
        parent::initContent();
    }

    public function save(){
        $app = JFactory::getApplication();
        $useAjax = $app->input->get('use_ajax', 0);
        $tab = $app->input->getWord('tab');
        $jsonData = array("success" =>false, "found" => false);

        switch ($tab){
            case 'price' : $this->savePrice(); break;
            case 'specific' :
                $productId = $app->input->get('product_id');
                $product = new JeproshopProductModelProduct($productId);
                if (JeproshopTools::isLoadedObject($product, 'product_id')) {
                    $specificPrice = $product->addSpecificPrice();
                    if($specificPrice) {
                        $jsonData = array("success" => true, "found" => true, "messages" => JText::_('COM_JEPROSHOP_PRODUCT_PRICE_HAS_BEEN_SUCCESSFULLY_UPDATED_MESSAGE'));
                        foreach ($specificPrice as $key => $value){
                            $jsonData[$key] = $value;
                        }
                        if($specificPrice->specific_price_rule_id > 0){
                            $jsonData['rule_name'] = JeproshopSpecificPriceRuleModelSpecificPriceRule::getSpecifiPriceRuleNameBySpecifiPriceRuleId($specificPrice->specific_price_rule_id);
                        }else{
                            $jsonData['rule_name'] = '---';
                        }

                        if($specificPrice->product_attribute_id > 0){
                            $combination = new JeproshopCombinationModelCombination($specificPrice->product_attribute_id);
                            $attributes = $combination->getAttributesName(JeproshopContext::getContext()->language->lang_id);
                            $attributeName = '';
                            foreach ($attributes as $attribute){ $attributeName .= $attribute->name . ' - '; }
                            $attributeName = rtrim($attributeName, ' - ');
                        }else{
                            $attributeName = JText::_('COM_JEPROSHOP_ALL_COMBINATIONS_LABEL');
                        }
                        $jsonData['attributes_name'] = $attributeName;

                        if($specificPrice->customer_id > 0){
                            $customer = new JeproshopCustomerModelCustomer($specificPrice->customer_id);
                            if(JeproshopTools::isLoadedObject($customer, 'customer_id')){
                                $customerFullName = $customer->firstname . ' ' . $customer->lastname;
                            }else{
                                $customerFullName = ' ';
                            }
                        }else{
                            $customerFullName = ' ';
                        }
                        $jsonData['customer_name'] = $customerFullName;

                        $canDeleteSpecificPrices = true;
                        if(JeproshopShopModelShop::isFeaturePublished()){
                            $specificPriceShopId = $specificPrice->shop_id;
                            $canDeleteSpecificPrices = (count(JeproshopContext::getContext()->employee->getAssociatedShops()) > 1 && $specificPriceShopId) || $specificPriceShopId;
                        }
                        $jsonData['can_delete_specific_prices'] = ($canDeleteSpecificPrices ? 1 : 0);
                        $jsonData['shop_feature'] = (JeproshopShopModelShop::isFeaturePublished() ? 1 : 0);
                    }else{
                        $jsonData = array("success" => false, "found" => false, "messages" => JText::_('COM_JEPROSHOP_SOMETHING_WENT_WRONG_DURING_PRODUCT_SPECIFIC_PRICE_UPDATE_MESSAGE'));
                    }
                } else {
                    $jsonData = array("success" => false, "found" => false, "messages" => JText::_('COM_JEPROSHOP_SOMETHING_WENT_WRONG_DURING_PRODUCT_SPECIFIC_PRICE_UPDATE_MESSAGE'));
                }
                break;
            default : $this->saveProduct(); break;
        }

        if($useAjax){
            $document = JFactory::getDocument();
            $document->setMimeEncoding('application/json');
            echo json_encode($jsonData);
            $app->close();
        }
    }

    public function saveProduct(){
        $app = JFactory::getApplication();
        if($this->viewAccess() && $this->checkAccess() && $this->canEdit('product')) {
            $this->checkProduct();
            $product = new JeproshopProductModelProduct();
            if($product->add()){
                $product->setCarriers();
                $product->updateAccessories();
                $product->updatePackItems();
                $product->updateDownloadProduct();

                if(JeproshopSettingModelSetting::getValue('force_advanced_stock_management') && JeproshopSettingModelSetting::getValue('advanced_stock_management')){
                    $product->advanced_stock_management = 1;
                    JeproshopStockAvailableModelStockAvailable::setProductDependsOnStock($product->product_id, true, (int)JeproshopContext::getContext()->shop->shop_id);
                    if($product->save()){
                        $request = JRequest::get('post');

                        $languages = JeproshopLanguageModelLanguage::getLanguages(false);
                        if (isset($request['association']['category_box']) && !$product->updateCategories($request['association']['category_box'])) {
                            JError::raiseError(500, 'An error occurred while linking the object.'); //.' <b>'.$this->table.'</b> '.Tools::displayError('To categories');
                        }elseif (!$product->updateTags($languages)) {
                            JError::raiseError(500, 'An error occurred while adding tags.');
                        }else {
                            //Hook::exec('actionProductAdd', array('product' => $this->object));
                            if (in_array($product->visibility, array('both', 'search')) && JeproshopSettingModelSetting::getValue('search_indexation')) {
                                //Search::indexation(false, $product->product_id);
                            }
                        }

                        if (JeproshopSettingModelSetting::getValue('default_warehouse_new_product') != 0 && JeproshopSettingModelSetting::getValue('advanced_stock_management')) {
                            $warehouseLocationEntity = new JeproshopWarehouseProductLocationModelWarehouseProductLocation();
                            $warehouseLocationEntity->product_id = $product->product_id;
                            $warehouseLocationEntity->product_attribute_id = 0;
                            $warehouseLocationEntity->warehouse_id = JeproshopSettingModelSetting::getValue('default_warehouse_new_product');
                            $warehouseLocationEntity->location = ('');
                            $warehouseLocationEntity->save();
                        }

                        // Save and stay on same form
                        if ($app->input->get('layout', 'edit') == 'edit') {
                            $redirectUrl = 'index.php?option=com_jeproshop&view=product&task=edit&product_id=' . $product->product_id
                                . ($app->input->get('category_id', 0) ? '&category_id=' . $app->input->get('category_id', 0)  : '') .
                                '&' . JeproshopTools::getProductToken() . '=1';
                        }else {
                            // Default behavior (save and back)
                            $redirectUrl = 'index.php?option=com_jeproshop&view=product&'. JeproshopTools::getProductToken() . '=1';
                        }
                        $app->rediret($redirectUrl);
                    }else{
                        $product->delete();
                    }
                }
            }
        }
    }
    
    public function update(){
        $app = JFactory::getApplication();
        $useAjax = $app->input->get('use_ajax', 0);
        $tab = $app->input->getWord('tab');
        $jsonData = array("success" =>false, "found" => false);
        $productId = $app->input->get('product_id');
        if($this->viewAccess() && $this->checkAccess() && $this->canEdit('product')) {
            switch ($tab) {
                case 'price' :
                    $product = new JeproshopProductModelProduct($productId);
                    if ($product->updatePrice()) {
                        $jsonData = array("success" => true, "found" => true, "messages" => JText::_('COM_JEPROSHOP_PRODUCT_PRICE_HAS_BEEN_SUCCESSFULLY_UPDATED_MESSAGE'));
                    } else {
                        $jsonData = array("success" => false, "found" => false, "messages" => JText::_('COM_JEPROSHOP_SOMETHING_WENT_WRONG_DURING_PRODUCT_PRICE_UPDATE_MESSAGE'));
                    }
                    break;
                
                default :
                    $this->updateProduct();
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

    public function updatePrice(){
        $jsonData = array("success" =>false, "found" => false);
        $app = JFactory::getApplication();
        
        return $jsonData;
    }

    public function updateSpecificPrice(){
        $jsonData = array("success" =>false, "found" => false);
        return $jsonData;
    }

    public function updateProduct(){
        $app = JFactory::getApplication();
        $productId = $app->input->get('product_id', 0);
        $layout = 'edit';

        if($productId && $this->checkAccess() && $this->canEdit('product')){
            $data = JRequest::get('post');
            $product = new JeproshopProductModelProduct($productId);
            $informationData = $data['information'];
            $productType = $informationData['product_type'];
            if(isset($informationData) && $product->is_virtual && $productType != JeproshopProductModelProduct::VIRTUAL_PRODUCT){
                if($productDownloadId = (int)JeproshopProductDownloadModelProductDownload::getProductDownloadIdFromProductId($productId)) {
                    $productDownload = new JeproshopProductDownloadModelProductDownload($productDownloadId);
                    if($productDownload->deleteFFile()){
                        JError::raiseError(500, JText::_('COM_JEPROSHOP_CANNOT_DELETE_FILE_MESSAGE'));
                        return false;
                    }
                }
            }

            if($app->input->get('delete_virtual')){
                if($this->canDelete('product')){

                }else{
                    JError::raiseError(500, JText::_('COM_JEPROSHOP_YOU_DO_NOT_HAVE_PERMISSION_TO_DELETE_THIS_PRODUCT_MESSAGE'));
                    return false;
                }
            }else if($app->input->get('preview')){
                $layout = 'edit';
                $task = 'save';
            }else if(isset($data['attachments'])){
                $layout = 'edit';
            }

            $this->checkProduct();

            if($this->has_errors){
                $layout = 'edit';
                return false;
            }

            if(JeproshopTools::isLoadedObject($product, 'product_id')){
                if($product->update()){
                    $redirectUrl = '';
                    if (in_array($product->visibility, array('both', 'search')) && JeproshopSettingModelSetting::getValue('search_indexation')) {
                        //Search::indexation(false, $product->product_id);
                    }
                    $confirmations = '';

                    // Save and preview
                    if ($app->input->get('preview', 0)) {
                            $redirectUrl = $this->getPreviewUrl($product);
                    } else {
                        //$page = (int)Tools::getValue('page');
                        // Save and stay on same form
                        if ($layout == 'edit') {
                            $confirmations = ('Update successful');
                            $redirectUrl = 'index.php?option=com_jeproshop&view=product&product_id=' . (int)$product->product_id;
                            $redirectUrl .= ($app->input->get('category_id', 0) ? '&category_id=' . (int)$app->input->get('category_id', 0) : '');
                            $redirectUrl .= '&task=edit&' . JeproshopTools::getProductToken() . '=1';
                        } else {
                            // Default behavior (save and back)
                            $redirectUrl = 'index.php?option=com_jeproshop&view=product&' . JeproshopTools::getProductToken() . '=1';
                        }
                    }
                    $app->redirect($redirectUrl, $confirmations);
                }
                
            }
        }
    }

    public function getPreviewUrl(JeproshopProductModelProduct $product)
    {
        $id_lang = Configuration::get('PS_LANG_DEFAULT', null, null, Context::getContext()->shop->id);

        if (!ShopUrl::getMainShopDomain()) {
            return false;
        }

        $is_rewrite_active = (bool)Configuration::get('PS_REWRITING_SETTINGS');
        $preview_url = $this->context->link->getProductLink(
            $product,
            $this->getFieldValue($product, 'link_rewrite', $this->context->language->id),
            Category::getLinkRewrite($this->getFieldValue($product, 'id_category_default'), $this->context->language->id),
            null,
            $id_lang,
            (int)Context::getContext()->shop->id,
            0,
            $is_rewrite_active
        );

        if (!$product->active) {
            $admin_dir = dirname($_SERVER['PHP_SELF']);
            $admin_dir = substr($admin_dir, strrpos($admin_dir, '/') + 1);
            $preview_url .= ((strpos($preview_url, '?') === false) ? '?' : '&').'adtoken='.$this->token.'&ad='.$admin_dir.'&id_employee='.(int)$this->context->employee->id;
        }

        return $preview_url;
    }

    public function checkProduct(){ }


}