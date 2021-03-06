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

class JeproshopCategoryController extends JeproshopController{
    public $category = null;

    public function initialize(){
        $app = JFactory::getApplication();
        $context = JeproshopContext::getContext();

        parent::initialize();
        $categoryId = $app->input->get('category_id');
        $task = $app->input->get('task');
        if($categoryId && $task != 'delete'){
            $this->category = new JeproshopCategoryModelCategory($categoryId);
        }else{
            if(JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP){
                $this->category = new JeproshopCategoryModelCategory($context->shop->category_id);
            }elseif(count(JeproshopCategoryModelCategory::getCategoriesWithoutParent()) > 1 && JeproshopSettingModelSetting::getValue('multishop_feature_active') && count(JeproshopShopModelShop::getShops(true, null, true)) != 1){
                $this->category = JeproshopCategoryModelCategory::getTopCategory();
            }else{
                $this->category = new JeproshopCategoryModelCategory(JeproshopSettingModelSetting::getValue('root_category'));
            }
        }

        if(JeproshopTools::isLoadedObject($this->category, 'category_id') && !$this->category->isAssociatedToShop() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP){
            $app->redirect('index.php?option=com_jeproshop&view=category&task=edit&category_id=' . (int)$context->shop->getCategoryId() . '&' . JeproshopTools::getCategoryToken() . '=1');
        }
    }

    public function save(){
        if($this->viewAccess() && JeproshopTools::checkCategoryToken()){
            $categoryModel = new JeproshopCategoryModelCategory();
            $categoryModel->saveCategory();
        }
    }

    public function update(){
        if($this->viewAccess() && JeproshopTools::checkCategoryToken()){
            $app = JFactory::getApplication();
            $category_id = $app->input->get('category_id');

            if(isset($category_id) && $category_id > 0) {
                $categoryModel = new JeproshopCategoryModelCategory($category_id);
                $categoryModel->updateCategory();
            }
        }
    }
}