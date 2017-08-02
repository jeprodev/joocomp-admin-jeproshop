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

class JeproshopController extends JControllerLegacy
{
    public $use_ajax = true;

    public $default_form_language;

    public $allow_employee_form_language;

    public $allow_link_rewrite = false;

    public $shop_link_type = "";

    public $multi_shop_context = -1;

    public $multi_shop_context_group = true;

    public $has_errors;

    /**
     * @var array List of loaded routes
     */
    protected $routes = array();

    /**
     * check if the controller is available for the current user/visitor
     */
    public function checkAccess(){ return true; }

    /**
     * Check if the current user/visitor has valid view permissions
     */
    public function viewAccess(){ return true; }

    /**
     * initialize jeproshop
     */
    public function initialize(){
        if(!defined('JEPROSHOP_BASE_URL')){ define('JEPROSHOP_BASE_URL', JeproshopTools::getShopDomain(true)); }
        if(!defined('JEPROSHOP_BASE_SSL_URL')){ define('JEPROSHOP_BASE_SSL_URL', JeproshopTools::getShopSslDomain(true)); }
    }

    public function initContent(){
        if(!$this->viewAccess()){
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_YOU_DO_NOT_HAVE_PERMISSION_TO_VIEW_THIS_PAGE_MESSAGE'));
        }

        $this->getLanguages();
        $app = JFactory::getApplication();

        $task = $app->input->get('task');
        $view = $app->input->get('view');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());

        if($task == 'edit' || $task == 'add'){
            if(!$viewClass->loadObject(true)){ return false; }
            $viewClass->setLayout('edit');
            $viewClass->renderEditForm(); 
        }elseif($task == 'added'){
            $viewClass->setLayout('edit');
            $viewClass->renderAddForm();
        }elseif($task == 'view'){
            if(!$viewClass->loadObject(true)){  return false; }
            $viewClass->setLayout('view');
            $viewClass->renderViewForm();
        }elseif($task == 'display' || $task  == ''){
            $viewClass->renderDetails();
        }elseif(!$this->use_ajax){

        }else{
            $this->execute($task);
        }
    }

    public function display($cache = FALSE, $urlParams = FALSE){
        //$this->initContent();
        $view = $this->input->get('view', 'dashboard');
        $layout = $this->input->get('layout', 'default');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->display();
    }

    public function catalog(){
        $app = JFactory::getApplication();
        $app->input->set('category_id', null);
        $app->input->set('parent_id', null);
        $app->redirect('index.php?option=com_jeproshop&view=product');
    }

    public function orders(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=order');
    }

    public function customers(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=customer');
    }

    public function price_rules(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=cart&task=rules');
    }

    public function shipping(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=carrier');
    }

    public function localization(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=country');
    }

    public function settings(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=setting');
    }

    public function administration(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=administration');
    }

    public function stats(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=stats');
    }

    public function getLanguages(){
        $cookie = JeproshopContext::getContext()->cookie;
        $this->allow_employee_form_language = (int)JeproshopSettingModelSetting::getValue('allow_employee_form_lang');
        if($this->allow_employee_form_language && !$cookie->employee_form_lang){
            $cookie->employee_form_lang = (int)JeproshopSettingModelSetting::getValue('default_lang');
        }

        $lang_exists = false;
        $languages = JeproshopLanguageModelLanguage::getLanguages(false);
        foreach($languages as $language){
            if(isset($cookie->employee_form_language) && $cookie->employee_form_language == $language->lang_id){
                $lang_exists = true;
            }
        }

        $this->default_form_language = $lang_exists ? (int)$cookie->employee_form_language : (int)JeproshopSettingModelSetting::getValue('default_lang');

        return $languages;
    }

    /**
     * Returns a link to a product image for display
     * Note: the new image filesystem stores product images in subdirectories of img/p/
     *
     * @param string $name rewrite link of the image
     * @param string $ids id part of the image filename - can be "productId_imageId" (legacy support, recommended) or "imageId" (new)
     * @param string $type
     * @return string
     */
    public function getProductImageLink($name, $ids, $type = null){
        $notDefault = false;
        if(is_array($name)){ $name = $name[JeproshopContext::getContext()->language->lang_id]; }

        // legacy mode or default image
        $theme = ((JeproshopShopModelShop::isFeaturePublished() && file_exists(COM_JEPROSHOP_PRODUCT_IMAGE_DIR . $ids . ($type ? '_'.$type : '').'_'. JeproshopContext::getContext()->shop->theme_name .'.jpg')) ? '_'.JeproshopContext::getContext()->shop->theme->name : '');

        if ((JeproshopSettingModelSetting::getValue('legacy_images') && (file_exists(COM_JEPROSHOP_PRODUCT_IMAGE_DIR . $ids . ($type ? '_'.$type : '').$theme.'.jpg'))) || ($notDefault = strpos($ids, 'default') !== false)) {
            if ($this->allow_link_rewrite == 1 && !$notDefault){
                $uriPath = JURI::base() . $ids.($type ? '_'.$type : '').$theme.'/'.$name.'.jpg';
            }else{
                $context = JeproshopContext::getContext();
                $theme = isset($context->shop->theme_directory) ? $context->shop->theme_directory : 'default';

                $ids = $context->language->iso_code;
                $uriPath = COM_JEPROSHOP_PRODUCT_IMAGE_DIR . $ids.($type ? '_' . $type . '_' : '').$theme.'.jpg';
            }
        } else {
            // if ids if of the form product_id-image_id, we want to extract the id_image part
            $splitIds = explode('_', $ids);
            $imageId = (isset($splitIds[1]) ? $splitIds[1] : $splitIds[0]);
            $productId = isset($splitIds[0]) ? $splitIds[0] : "" ;

            if(JeproshopShopModelShop::isFeaturePublished() && file_exists(COM_JEPROSHOP_PRODUCT_IMAGE_DIR . JeproshopImageModelImage::getStaticImageFolder($imageId). $imageId .($type ? '_'.$type : '').'_'.(int)JeproshopContext::getContext()->shop->theme->name . '.jpg')) {
                $theme = '_' . JeproshopContext::getContext()->shop->theme->name;
            }else{ $theme = '_default'; }

            if ($this->allow_link_rewrite == 1){
                $uriPath =  $imageId .($type ? '_'.$type : '').$theme.'/'.$name.'.jpg';
            }else if(isset($productId) && isset($imageId)) {
                $uriPath = COM_JEPROSHOP_PRODUCT_IMAGE_DIR . $productId . '/' . $imageId . ($type ? '_' . $type : '') . '.jpg';
                //$uriPath = COM_JEPROSHOP_PRODUCT_IMAGE_DIR . JeproshopImageModelImage::getStaticImageFolder($imageId) . $imageId . ($type ? '_' . $type : '') . '.jpg';
            }else{
                $langCode = JeproshopContext::getContext()->language->iso_code;
                $uriPath =  COM_JEPROSHOP_PRODUCT_IMAGE_DIR . $langCode .($type ? '_'.$type : '').$theme.'.jpg';
            }
        }
        //return JeproshopTools::getMediaServer($uri_path).$uri_path;
        return $uriPath;
    }

    /**
     * Create a link to a product
     *
     * @param mixed $product Product object (can be an ID product, but deprecated)
     * @param string $alias
     * @param string $category
     * @param string $ean13
     * @param null $langId
     * @param null $shopId
     * @param int $productAttributeId ID product attribute
     * @param bool $forceRoutes
     * @return string
     * @throws JException
     * @internal param int $langId
     * @internal param int $id_shop (since 1.5.0) ID shop need to be used when we generate a product link for a product in a cart
     */
    public function getProductLink($product, $alias = null, $category = null, $ean13 = null, $langId = null, $shopId = null, $productAttributeId = 0, $forceRoutes = false){
        if (!$langId) {
            $langId = JeproshopContext::getContext()->language->lang_id;
        }

        if (!is_object($product)) {
            if (is_array($product) && isset($product['product_id'])) {
                $product = new JeproshopProductModelProduct($product['product_id'], false, $langId, $shopId);
            } elseif ((int)$product) {
                $product = new JeproshopProductModelProduct((int)$product, false, $langId, $shopId);
            } else {
                throw new JException(JText::_('COM_JEPROSHOP_INVALID_PRODUCT_VARS_MESSAGE'));
            }
        }

        // Set available keywords
        $anchor = '&task=view&product_id=' . $product->product_id; // .  ((!$alias) ? '&rewrite=' . $product->getFieldByLang('link_rewrite') : $alias) . ((!$ean13) ? '&ean13=' . $product->ean13 : $ean13);
        //$anchor .= '&meta_keywords=' . JeproshopTools::str2url($product->getFieldByLang('meta_keywords')) . '&meta_title=' . JeproshopTools::str2url($product->getFieldByLang('meta_title'));

        if ($this->hasKeyword('product', $langId, 'manufacturer', $shopId)) {
            //$params['manufacturer'] = JeproshopTools::str2url($product->isFullyLoaded ? $product->manufacturer_name : JeproshopManufacturerModelManufacturer::getNameById($product->manufacturer_id));
        }
        if ($this->hasKeyword('product', $langId, 'supplier', $shopId)) {
            //$params['supplier'] = JeproshopTools::str2url($product->isFullyLoaded ? $product->supplier_name : JeproshopSupplierModelSupplier::getNameById($product->supplier_id));
        }
        if ($this->hasKeyword('product', $langId, 'price', $shopId)) {
            //$params['price'] = $product->isFullyLoaded ? $product->price : JeproshopProductModelProduct::getStaticPrice($product->product_id, false, null, 6, null, false, true, 1, false, null, null, null, $product->specific_price);
        }
        if ($this->hasKeyword('product', $langId, 'tags', $shopId)) {
            //$params['tags'] = JeproshopTools::str2url($product->getTags($lang_id));
        }
        if ($this->hasKeyword('product', $langId, 'category', $shopId)) {
            //$params['category'] = (!is_null($product->category) && !empty($product->category)) ? JeproshopTools::str2url($product->category) : JeproshopTools::str2url($category);
        }
        if ($this->hasKeyword('product', $langId, 'reference', $shopId)) {
            //$params['reference'] = JeproshopTools::str2url($product->reference);
        }

        if ($this->hasKeyword('product', $langId, 'categories', $shopId))
        {
            $params['category'] = (!$category) ? $product->category : $category;
            $cats = array();
            foreach ($product->getParentCategories() as $cat) {
                if (!in_array($cat->category_id, Link::$category_disable_rewrite)) {//remove root and home category from the URL
                    $cats[] = $cat->link_rewrite;
                }
            }
            $params['categories'] = implode('/', $cats);
        }
        $anchor .= $productAttributeId ? '&product_attribute_id='  . $productAttributeId : '';



        return JRoute::_('index.php?option=com_jeproshop&view=product' . $anchor);
    }

    /**
     * Check if a keyword is written in a route rule
     *
     * @param string $route_id
     * @param int $lang_id
     * @param string $keyword
     * @param int $shop_id
     * @return bool
     */
    public function hasKeyword($route_id, $lang_id, $keyword, $shop_id = null) {
        if ($shop_id === null){
            $shop_id = (int)JeproshopContext::getContext()->shop->shop_id;
        }
        /*if (!isset($this->routes[$shop_id])){
            $this->loadRoutes($shop_id);
        }*/
        if (!isset($this->routes[$shop_id]) || !isset($this->routes[$shop_id][$lang_id]) || !isset($this->routes[$shop_id][$lang_id][$route_id]))
            return false;

        return preg_match('#\{([^{}]*:)?'.preg_quote($keyword, '#').'(:[^{}]*)?\}#', $this->routes[$shop_id][$lang_id][$route_id]['rule']);
    }



}

