<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
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

class JeproshopCategoryModelCategory extends JeproshopModel {
    public $category_id;

    public $lang_id;

    public $shop_id;

    public $name;

    public $published = true;

    public $position;

    public $description;

    public $parent_id;

    public $depth_level;

    public $n_left;

    public $n_right;

    public $link_rewrite;

    public $meta_title;
    public $meta_keywords;
    public $meta_description;

    public $date_add;
    public $date_upd;

    public $shop_list_ids;

    public $is_root_category;

    public $default_shop_id;

    public $groupBox;

    public $image_id = 'default';
    public $image_dir = '';

    public $multiLang = true;
    public $multiLangShop = true;

    protected $deleted_category = FALSE;

    protected static $_links = array();

    public static function getCategories(JeproshopContext $context = NULL, $sqlSort = ''){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!$context){ $context = JeproshopContext::getContext(); }

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limitstart', 'limitstart', 0, 'int');
        $langId = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $published = $app->getUserStateFromRequest($option. $view. '.published', 'published', 0, 'string');

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_category') . " AS category " . JeproshopShopModelShop::addSqlAssociation('category');
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (category." . $db->quoteName('category_id');
        $query .= " = category_lang." . $db->quoteName('category_id'). JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . ") WHERE 1";
        $query .= ($langId ? " AND " . $db->quoteName('lang_id') . " = " . (int)$langId : "") . ($published ? " AND " . $db->quoteName('published') . "= 1" : "" );
        $query .= (!$langId ? " GROUP BY category.category_id " : "") . ($sqlSort ? $sqlSort : " ORDER BY category." . $db->quoteName('depth_level') . " ASC, category_shop." . $db->quoteName('position') . " ASC");


        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getRootCategory($lang_id = null, JeproshopShopModelShop $shop = NULL){
        $context = JeproshopContext::getContext();
        if(is_null($lang_id)){ $lang_id = $context->language->lang_id; }

        if(!$shop){
            if(JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_SHOP ){
                $shop = new JeproshopShopModelShop(JeproshopSettingModelSetting::getValue('default_shop'));
            }else{
                $shop = $context->shop;
            }
        }else{
            return new JeproshopCategoryModelCategory($shop->getCategoryId(), $lang_id);
        }

        $has_more_than_one_root_category = count(JeproshopCategoryModelCategory::getCategoriesWithoutParent()) > 1;
        if (JeproshopShopModelShop::isFeaturePublished() && $has_more_than_one_root_category && JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_SHOP){
            $category = JeproshopCategoryModelCategory::getTopCategory($lang_id);
        }else{
            $category = new JeproshopCategoryModelCategory($shop->getCategoryId(), $lang_id);
        }
        return $category;
    }

    public static function getRootCategories($lang_id = null, $published = true){
        if (!$lang_id){
            $lang_id = JeproshopContext::getContext()->language->lang_id;
        }

        $db = JFactory::getDBO();
        $query = "SELECT DISTINCT(category." . $db->quoteName('category_id') . "), category_lang.";
        $query .= $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_category') . "AS category";
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (";
        $query .= " category_lang." . $db->quoteName('category_id') . " = category." . $db->quoteName('category_id');
        $query .= " AND category_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") WHERE ";
        $query .= $db->quoteName('is_root_category') . " = 1 " .($published ? "AND " . $db->quoteName('published') . " = 1": "");

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getCategoriesWithoutParent(){
        $cache_id = 'jeproshop_category_get_Categories_Without_parent_'.(int)JeproshopContext::getContext()->language->lang_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();

            $query = "SELECT DISTINCT category.* FROM " . $db->quoteName('#__jeproshop_category') . " AS category";
            $query .= "	LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (category.";
            $query .= $db->quoteName('category_id') . " = category_lang." . $db->quoteName('category_id') . " AND category_lang.";
            $query .= $db->quoteName('lang_id') . " = " .(int)JeproshopContext::getContext()->language->lang_id;
            $query .= ") WHERE " . $db->quoteName('depth_level') . " = 1";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     *
     * @param Array $categoryIds
     * @param int $langId
     * @return Array
     */
    public static function getCategoryInformation($categoryIds, $langId = null){
        if ($langId === null){
            $langId = JeproshopContext::getContext()->language->lang_id;
        }

        if (!is_array($categoryIds) || !count($categoryIds)){ return; }

        $categories = array();

        $db = JFactory::getDBO();

        $query = "SELECT category." . $db->quoteName('category_id') . ", category_lang." . $db->quoteName('name');
        $query .= ", category_lang." . $db->quoteName('link_rewrite') . ", category_lang." . $db->quoteName('lang_id');
        $query .= " FROM " . $db->quoteName('#__jeproshop_category') . " AS category LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (category." ;
        $query .= $db->quoteName('category_id') . " = category_lang." . $db->quoteName('category_id');
        $query .= JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . ") " . JeproshopShopModelShop::addSqlAssociation('category');
        $query .= " WHERE category_lang." . $db->quoteName('lang_id') . " = " .(int)$langId . " AND category.";
        $query .= $db->quoteName('category_id') . " IN (" . implode(',', array_map('intval', $categoryIds)) . ")";

        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as $category){
            $categories[$category->category_id] = $category;
        }
        return $categories;
    }

    /**
     * Get Each parent category of this category until the root category
     *
     * @param integer $langId Language ID
     * @return array Corresponding categories
     */
    public function getParentsCategories($langId = null) {
        $context = JeproshopContext::getContext()->cloneContext();
        $context->shop = clone($context->shop);
        $category_id = JFactory::getApplication()->input->get('category_id');

        if (is_null($langId)){ $lang_id = $context->language->lang_id; }

        $categories = null;
        $current_id = $this->category_id;
        if (count(JeproshopCategoryModelCategory::getCategoriesWithoutParent()) > 1 && JeproshopSettingModelSetting::getValue('multishop_feature_active') && count(JeproshopShopModelShop::getShops(true, null, true)) != 1) {
            $context->shop->category_id = JeproshopCategoryModelCategory::getTopCategory()->category_id;
        }elseif (!$context->shop->shop_id) {
            $context->shop = new JeproshopShopModelShop(JeproshopSettingModelSetting::getValue('default_shop'));
        }

        $shop_id = $context->shop->shop_id;
        $db = JFactory::getDBO();
        while (true){
            $query = "SELECT category.*, category_lang.* FROM " . $db->quoteName('#__jeproshop_category') . " AS category LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (category.";
            $query .= $db->quoteName('category_id') . " = category_lang." . $db->quoteName('category_id') . " AND " . $db->quoteName('lang_id') . " = " . (int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . ")";
            if (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP) {
                $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_category_shop') . " AS category_shop ON (category." . $db->quoteName('category_id') . " = category_shop.";
                $query .= $db->quoteName('category_id') . " AND category_shop." . $db->quoteName('shop_id') . " = " . (int)$shop_id . ")";
            }
            $query .= " WHERE category." . $db->quoteName('category_id') . " = " .(int)$current_id;
            if (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP) {
                $query .= " AND category_shop." . $db->quoteName('shop_id') . " = "  . (int)$context->shop->shop_id;
            }
            $root_category = JeproshopCategoryModelCategory::getRootCategory();
            if (JeproshopShopModelShop::isFeaturePublished() && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && (!$category_id || (int)$category_id == (int)$root_category->category_id || (int)$root_category->category_id == (int)$context->shop->category_id)) {
                $query .= " AND category." . $db->quoteName('parent_id') . " != 0";
            }

            $db->setQuery($query);
            $result = $db->loadObject();

            if ($result)
                $categories[] = $result;
            elseif (!$categories)
                $categories = array();
            if (!$result || ($result->category_id == $context->shop->category_id))
                return $categories;
            $current_id = $result->parent_id;
        }
    }

    /**
     * @static
     * @param null $langId
     * @return JeproshopCategoryModelCategory
     */
    public static function getTopCategory($langId = null){
        if(is_null($langId)){
            $langId = (int)JeproshopContext::getContext()->language->lang_id;
        }
        $cacheKey = 'jeproshop_category::getTopCategory_'.(int)$langId;
        if (!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();
            $query = "SELECT " . $db->quoteName('category_id') . " FROM " . $db->quoteName('#__jeproshop_category');
            $query .= "	WHERE " . $db->quoteName('parent_id') . " = 0";
            $db->setQuery($query);
            $category_id = (int)$db->loadResult();
            JeproshopCache::store($cacheKey, new JeproshopCategoryModelCategory($category_id, $langId));
        }
        return JeproshopCache::retrieve($cacheKey);
    }

    public function getCategoriesList(){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        $context = JeproshopContext::getContext();

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $shop_id = $app->getUserStateFromRequest($option. $view. '.shop_id', 'shop_id', $context->shop->shop_id, 'int');
        $shop_group_id = $app->getUserStateFromRequest($option. $view. '.shop_group_id', 'shop_group_id', $context->shop->shop_group_id, 'int');
        $category_id = $app->getUserStateFromRequest($option. $view. '.category_id', 'category_id', 0, 'int');
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'date_add', 'string');
        $order_way = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');
        $published = $app->getUserStateFromRequest($option. $view. '.published', 'published', 0, 'string');

        $count_categories_without_parent = count(JeproshopCategoryModelCategory::getCategoriesWithoutParent());

        $top_category = JeproshopCategoryModelCategory::getTopCategory();
        $parent_id = 0;
        if($category_id){
            $category = new JeproshopCategoryModelCategory($category_id);
            $parent_id = $category->category_id;
        }elseif(!JeproshopShopModelShop::isFeaturePublished() && $count_categories_without_parent > 1){
            $parent_id = $top_category->category_id;
        }elseif(JeproshopShopModelShop::isFeaturePublished() && $count_categories_without_parent == 1){
            $parent_id = JeproshopSettingModelSetting::getValue('root_category');
        }elseif(JeproshopShopModelShop::isFeaturePublished() && $count_categories_without_parent > 1 && JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_SHOP){
            if(JeproshopSettingModelSetting::getValue('multishop_feature_active') && count(JeproshopShopModelShop::getShops(true, null, true)) == 1){
                $parent_id = $context->shop->category_id;
            }else{
                $parent_id = $top_category->category_id;
            }
        }

        $explicitSelect = true;


        /* Manage default params values */
        $use_limit = true;
        if ($limit === false)
            $use_limit = false;

        $join = " LEFT JOIN " . $db->quoteName('#__jeproshop_category_shop') . " AS category_shop ON (category." . $db->quoteName('category_id') . " = category_shop." . $db->quoteName('category_id') . " AND ";
        if (JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP){
            $join .= " category_shop.shop_id = " . (int)$context->shop->shop_id . ") ";
        }else{
            $join .= " category_shop.shop_id = category.default_shop_id)" ;
        }

        // we add restriction for shop
        if(JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && JeproshopShopModelShop::isFeaturePublished()){
            $where = " AND category_shop." . $db->quoteName('shop_id') . " = " . (int)JeproshopContext::getContext()->shop->shop_id;
        }
        /* Check params validity */
        if (!JeproshopTools::isOrderBy($order_by) || !JeproshopTools::isOrderWay($order_way)
            || !is_numeric($limitstart) || !is_numeric($limit) || !JeproshopTools::isUnsignedInt($lang_id)){
            echo JError::raiseError(500,('get list params is not valid'));
        }

        /* Cache */
        if (preg_match('/[.!]/', $order_by)){
            $order_by_split = preg_split('/[.!]/', $order_by);
            $order_by = bqSQL($order_by_split[0]).'.`'.bqSQL($order_by_split[1]).'`';
        }elseif ($order_by){
            $order_by = $db->quoteName($db->escape($order_by));
        }

        // Add SQL shop restriction
        $shopLinkType = "";
        $select_shop = $join_shop = $where_shop = '';
        /*if ($shopLinkType){
            $select_shop = ", shop.shop_name as shop_name ";
            $join_shop = " LEFT JOIN " ._DB_PREFIX_.$this->shopLinkType.' shop
                            ON a.id_'.$this->shopLinkType.' = shop.id_'.$this->shopLinkType;
            $where_shop = JeproshopShopModelShop::addSqlRestriction('1', 'category');
        }*/

        if ($context->controller->multishop_context && JeproshopShopModelShop::isTableAssociated('category')){
            if (JeproshopShopModelShop::getShopContext() != JeproshopShopModelShop::CONTEXT_ALL || !$context->employee->isSuperAdmin()){
                $test_join = !preg_match('/`?'.preg_quote('#__jeproshop_category_shop').'`? *category_shop/', $join);
                if (JeproshopShopModelShop::isFeaturePublished() && $test_join && JeproshopShopModelShop::isTableAssociated('category')){
                    $where .= " AND category.category_id IN ( SELECT category_shop.category_id FROM ";
                    $where .= $db->quoteName('#__jeproshop_category__shop') . " AS category_shop WHERE category_shop.";
                    $where .= "shop_id IN (" . implode(', ', JeproshopShopModelShop::getContextListShopIds()). ") )";
                }
            }
        }

        $select = ", category_shop.position AS position ";
        $tmpTableFilter = "";

        /* Query in order to get results with all fields */
        $lang_join = " LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (";
        $lang_join .= "category_lang." . $db->quoteName('category_id') . " = category." . $db->quoteName('category_id');
        $lang_join .= " AND category_lang." . $db->quoteName('lang_id') . " = " .(int)$lang_id;
        if ($context->shop->shop_id){
            if (!JeproshopShopModelShop::isFeaturePublished()){
                $lang_join .= " AND category_lang." . $db->quoteName('shop_id') . " = 1";
            }elseif (JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP){
                $lang_join .=  " AND category_lang." . $db->quoteName('shop_id') . " = " .(int)$context->shop->shop_id;
            }else{
                $lang_join .=  " AND category_lang." . $db->quoteName('shop_id') . " = category.default_shop_id";
            }
        }
        $lang_join .= ") ";


        $having_clause = '';
        if (isset($this->_filterHaving) || isset($this->_having)){
            $having_clause = ' HAVING ';
            if (isset($this->_filterHaving)){
                $having_clause .= ltrim($this->_filterHaving, ' AND ');
            }
            if(isset($this->_having)){
                $having_clause .= $this->_having.' ';
            }
        }

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS " .($tmpTableFilter ? " * FROM (SELECT " : "");
            if ($explicitSelect){
                $query .= "category." . $db->quoteName('category_id') . ", category_lang." . $db->quoteName('name') . ", category_lang." . $db->quoteName('description');
                $query .= " , category." . $db->quoteName('position') ." AS category_position, " . $db->quoteName('published');
            }else{
                $query .= ($lang_id ? " category_lang.*," : "") . " category.*";
            }
            $query .= (isset($select) ? rtrim($select, ", ") : "") . $select_shop . " FROM " . $db->quoteName('#__jeproshop_category') . " AS category " . $lang_join . (isset($join) ? $join . " " : "") ;
            $query .= $join_shop . " WHERE 1 ". (isset($where) ? $where . " " : "") . ($this->deleted_category ? " AND category." .$db->quoteName('deleted') . " = 0 " : "") .  "AND " . $db->quoteName('parent_id');
            $query .= "= " . (int)$parent_id . $where_shop .(isset($group) ? $group . " " : "") . $having_clause . " ORDER BY " . ((str_replace('`', '', $order_by) == 'category_id') ? "category." : ""). " category.";
            $query .= $order_by . " " . $db->escape($order_way) . ($tmpTableFilter ? ") tmpTable WHERE 1" . $tmpTableFilter : "");

            $db->setQuery($query);
            $total = count($db->loadObjectList());
            $query .= (($use_limit === true) ? " LIMIT " . (int)$limitstart.", ".(int)$limit : "" );

            $db->setQuery($query);
            $categories = $db->loadObjectList();

            if ($use_limit === true){
                $limitstart = (int)$limitstart - (int)$limit;
                if ($limitstart < 0){ break; }
            }else{ break; }
        } while (empty($categories));

        if(!empty($categories)){
            foreach($categories as $item){
                $category_tree = JeproshopCategoryModelCategory::getChildren((int)$item->category_id, $context->language->lang_id);
                $item->set_view = (count($category_tree) ? 1 : 0);
            }
        }

        $this->pagination = new JPagination($total, $limitstart, $limit);
        return $categories;
    }

}