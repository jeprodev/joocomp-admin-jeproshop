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

require_once  'tree.php';

class JeproshopCategoriesTree extends JeproshopTree {
    const DEFAULT_TEMPLATE             = 'tree_categories';
    const DEFAULT_NODE_FOLDER_TEMPLATE = 'tree_node_folder_radio';
    const DEFAULT_NODE_ITEM_TEMPLATE   = 'tree_node_item_radio';

    private $disabled_categories;
    private $input_name;
    private $lang;
    private $root_category;
    private $selected_categories;
    private $shop;
    private $use_checkbox;
    private $use_search;
    private $use_shop_restriction;

    public function __construct($treeId, $title = null, $rootCategory = null, $lang = null, $useShopRestriction = true){
        parent::__construct($treeId);

        if (isset($title)){ $this->setTreeTitle($title); }

        if (isset($rootCategory)){ $this->setRootCategory($rootCategory); }

        $this->setLang($lang);
        $this->setUseShopRestriction($useShopRestriction);
    }

    public function setLang($value){
        $this->lang = $value;
        return $this;
    }

    public function setRootCategory($value){
        if (!JeproshopTools::isInt($value)){
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_ROOT_CATEGORY_MUST_BE_AN_INTEGER_VALUE_MESSAGE'));
        }
        $this->root_category = $value;
        return $this;
    }

    public function setUseShopRestriction($value){
        $this->use_shop_restriction = (bool)$value;
        return $this;
    }

    public function setUseCheckBox($value){
        $this->use_checkbox = (bool)$value;
        return $this;
    }

    public function setUseSearch($value){
        $this->use_search = (bool)$value;
        return $this;
    }

    public function setSelectedCategories($value){
        if (!is_array($value))
            throw new JException('Selected categories value must be an array');

        $this->selected_categories = $value;
        return $this;
    }

    public function setInputName($value) {
        $this->input_name = $value;
        return $this;
    }

    public function setDisabledCategories($value){
        $this->disabled_categories = $value;
        return $this;
    }

    public function getRootCategory(){
        return $this->root_category;
    }

    public function useShopRestriction(){
        return (isset($this->use_shop_restriction) && $this->use_shop_restriction);
    }

    public function getData(){
        if(!isset($this->data)){
            $this->setTreeData(JeproshopCategoryModelCategory::getNestedCategories(
                $this->getRootCategory(), $this->getLang(), false, null, $this->useShopRestriction()));
        }
        return $this->data;
    }

    public function getLang(){
        if (!isset($this->lang)){
            $this->setLang($this->getContext()->employee->lang_id);
        }
        return $this->lang;
    }

    private function getSelectedChildNumbers(&$categories, $selected, &$parent = null){
        $selected_children = 0;

        foreach ($categories as $key => &$category)	{
            if (isset($parent) && in_array($category->category_id, $selected)){	$selected_children++; }

            if (isset($category->children) && !empty($category->hildren))
                $selected_children += $this->getSelectedChildNumbers($category->children, $selected, $category);
        }

        if(!isset($parent)){ $parent = new  JeproshopCategoryModelCategory(); }
        if (!isset($parent->selected_childs))
            $parent->selected_childs = 0;

        $parent->selected_childs = $selected_children;
        return $selected_children;
    }

    public function getSelectedCategories(){
        if (!isset($this->selected_categories))
            $this->selected_categories = array();

        return $this->selected_categories;
    }

    public function getDisabledCategories(){
        return $this->disabled_categories;
    }

    public function useSearch(){
        return (isset($this->use_search) && $this->use_search);
    }

    public function getTreeTemplate(){
        if (!isset($this->template))
            $this->setTreeTemplate(self::DEFAULT_TEMPLATE);

        return $this->template;
    }

    public function useCheckBox(){
        return (isset($this->use_checkbox) && $this->use_checkbox);
    }

    private function disableCategories(&$categories, $disabled_categories = null){
        foreach ($categories as &$category){
            if (!isset($disabled_categories) || in_array($category->category_id, $disabled_categories)){
                $category->disabled = true;
                if (array_key_exists('children', $category) && is_array($category->children))
                    self::disableCategories($category->children);
            }
            else if (array_key_exists('children', $category) && is_array($category->children))
                self::disableCategories($category->children, $disabled_categories);
        }
    }

    public function getNodeFolderTemplate(){
        if (!isset($this->node_folder_template))
            $this->setNodeFolderTemplate(self::DEFAULT_NODE_FOLDER_TEMPLATE);

        return $this->node_folder_template;
    }

    public function getInputName(){
        if (!isset($this->input_name))
            $this->setInputName('category_box');

        return $this->input_name;
    }

    public function render(){

    }
}