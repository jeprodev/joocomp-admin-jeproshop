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

class JeproshopCategoryViewCategory extends JeproshopViewLegacy{
    public function renderDetails($tpl = null){
        $app = JFactory::getApplication();
        $category_id = $app->input->get('category_id');

        if(!isset($this->context) || empty($this->context)){ $this->context = JeproshopContext::getContext(); }
        if(!JeproshopShopModelShop::isFeaturePublished() && count(JeproshopCategoryModelCategory::getCategoriesWithoutParent()) > 1 && $category_id){
            $categories_tree = array(get_object_vars($this->context->controller->category->getTopCategory()));
        }else{
            $categories_tree = $this->context->controller->category->getParentsCategories();
            $end = end($categories_tree);
            if(isset($categories_tree) && !JeproshopShopModelShop::isFeaturePublished() && (isset($end) && $end->parent_id != 0)){
                $categories_tree = array_merge($categories_tree, array(get_object_vars($this->context->controller->category->getTopCategory())));
            }
        }

        $count_categories_without_parent = count(JeproshopCategoryModelCategory::getCategoriesWithoutParent());

        if(empty($categories_tree) && ($this->context->controller->category->category_id != 1 || $category_id ) && (JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && !JeproshopShopModelShop::isFeaturePublished() && $count_categories_without_parent > 1)){
            $categories_tree = array(array('name' => $this->context->controller->category->name[$this->context->language->lang_id]));
        }
        $categories_tree = array_reverse($categories_tree);

        $this->assignRef('categories_tree', $categories_tree);
        $this->assignRef('categories_tree_current_id', $this->context->controller->category->category_id);

        $categoryModel = new JeproshopCategoryModelCategory();
        $categories = $categoryModel->getCategoriesList();
        $pagination = $categoryModel->getPagination();
        $this->assignRef('pagination', $pagination);
        $this->assignRef('categories', $categories);
        $this->setLayout('default');

        $this->addToolBar();

        parent::display($tpl);
    }

    public function  renderView($tpl = null){
        $this->renderDetails($tpl);
    }

    private function addToolBar()
    {
        switch ($this->getLayout()) {
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_CATEGORY_TITLE'), 'jeproshop-category');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            case 'edit' :
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_CATEGORY_TITLE'), 'jeproshop-category');
                JToolBarHelper::apply('update', JText::_('COM_JEPROSHOP_UPDATE_LABEL'));
                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_CATEGORIES_LIST_TITLE'), 'jeproshop-category');
                JToolBarHelper::addNew('add');
                break;
        }
        $this->addSideBar('catalog');
    }
}