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

class JeproshopAttributeModelAttribute extends JeproshopModel {
    /** @var integer Group id which attribute belongs */
    public $attribute_id;
    public $attribute_group_id;
    public $product_attribute_id;
    public $product_id;
    public $attribute_designation;

    public $shop_id;

    /** @var string Name */
    public $name;
    public $color;
    public $position;
    public $default;

    protected $multiLang = true;
    protected $multiLangShop = true;

    protected $shop_list_id;
    protected $image_dir = COM_JEPROSHOP_COLOR_IMAGE_DIR;

    /**
     * Get all attributes for a given language
     *
     * @param integer $langId Language id
     * @param boolean $notNull Get only not null fields if true
     * @return array Attributes
     */
    public static function getAttributes($langId, $notNull = false){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return array(); }

        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT attribute_group.*, attribute_group_lang.*, attribute." . $db->quoteName('attribute_id') . ", attribute_lang." . $db->quoteName('name') . ", attribute_group_lang." . $db->quoteName('name'). " AS ";
        $query .= $db->quoteName('attribute_group_name') . " FROM " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS ";
        $query .= "attribute_group_lang ON (attribute_group." . $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') . " AND attribute_group_lang." . $db->quoteName('lang_id') . " = ";
        $query .= (int)$langId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute') . " AS attribute ON (attribute." . $db->quoteName('attribute_group_id') . " = attribute_group." . $db->quoteName('attribute_group_id');
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id') . " AND ";
        $query .= "attribute_lang." . $db->quoteName('lang_id') . " = " . (int)$langId . ") " . JeproshopShopModelShop::addSqlAssociation('attribute_group') ;
        $notNullQuery =  ($notNull ? " WHERE attribute." . $db->quoteName('attribute_id') . " IS NOT NULL AND attribute_lang." . $db->quoteName('name') . " IS NOT NULL AND attribute_group_lang." . $db->quoteName('attribute_group_id') . " IS NOT NULL" : "");
        $query .= JeproshopShopModelShop::addSqlAssociation('attribute') . $notNullQuery  . " ORDER BY attribute_group_lang." . $db->quoteName('name') .  " ASC, attribute." . $db->quoteName('position') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

}


class JeproshopAttributeGroupModelAttributeGroup extends JeproshopModel{
    public $attribute_group_id;
    /** @var string Name */
    public $name;
    public $is_color_group;
    public $position;
    public $group_type;

    public $shop_id;
    public $lang_id;

    protected  $multiLang = true;
    protected  $multiLangShop = true;

    /** @var string Public Name */
    public $public_name;

    public function __construct($attribute_group_id = null, $lang_id = null, $shop_id = null){
        if($lang_id !== NULL){
            $this->lang_id = JeproshopLanguageModelLanguage::getLanguage($lang_id) !== FALSE ? (int)$lang_id : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($shop_id && $this->isMultiShop('attribute_group', $this->multiLangShop)){
            $this->shop_id = (int)$shop_id;
            $this->get_shop_from_context = false;
        }

        if($this->isMultiShop('attribute_group', $this->multiLangShop) && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($attribute_group_id){
            $cache_id = 'jeproshop_attribute_group_model_' . (int)$attribute_group_id;
            if(!JeproshopCache::isStored($cache_id)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ";
                $where = " WHERE attribute_group." . $db->quoteName('attribute_group_id') . " = " . (int)$attribute_group_id;

                //Get Language information
                if($lang_id){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON (attribute_group.";
                    $query .= $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') . " AND ";
                    $query .= "attribute_group_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ")";
                    if($this->shop_id && !empty($this->multiLangShop)){
                        $where .= " AND attribute_group_lang." . $db->quoteName('shop_id') . " = " . (int)$shop_id;
                    }
                }

                // Get Shop Information
                if(JeproshopShopModelShop::isTableAssociated('attribute_group')){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_shop') . " AS attribute_group_shop ON (attribute_group.";
                    $query .= $db->quoteName('attribute_group_id') . " = attribute_group_shop." . $db->quoteName('attribute_group_id') . " AND attribute_group_shop.";
                    $query .= $db->quoteName('shop_id') . " = " . (int)$this->shop_id . ")";
                }

                $db->setQuery($query . $where);
                $attributeGroupData = $db->loadObject();

                if($attributeGroupData){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_attribute_group_lang') . " WHERE " . $db->quoteName('attribute_group_id') . " = " ;
                        $query .= (int)$attribute_group_id; // . (($this->shop_id && $this->isMultiLangShop()) ? " AND " . $db->quoteName('shop_id') . " = " . (int)$this->shop_id : "");
                        $db->setQuery($query);
                        $attributeGroupDataLang = $db->loadObjectList();
                        if($attributeGroupDataLang){
                            foreach ($attributeGroupDataLang as $row) {
                                foreach ($row as $key => $value) {
                                    if(array_key_exists($key, $this) && $key != 'attribute_group_id'){
                                        if(!isset($attributeGroupData->{$key}) || !is_array($attributeGroupData->{$key})){
                                            $attributeGroupData->{$key} = array();
                                        }
                                        $attributeGroupData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $attributeGroupData);
                }
            }else{
                $attributeGroupData = JeproshopCache::retrieve($cache_id);
            }

            if($attributeGroupData){
                $this->attribute_group_id = (int)$attribute_group_id;
                foreach ($attributeGroupData as $key => $value) {
                    if (array_key_exists($key, $this)) {
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    /**
     * Get all attributes groups for a given language
     *
     * @param integer $langId Language id
     * @return array Attributes groups
     */
    public static function getAttributesGroups($langId){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){
            return array();
        }

        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT attribute_group_lang." . $db->quoteName('name') . ", attribute_group.*, ";
        $query .= "attribute_group_lang.* FROM " . $db->quoteName('#__jeproshop_attribute_group') . " AS ";
        $query .= "attribute_group " . JeproshopShopModelShop::addSqlAssociation('attribute_group'). " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON (attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id');
        $query .= " AND " . $db->quoteName('lang_id') . " = " .(int)$langId . ") ORDER BY " . $db->quoteName('name') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getAttributeGroupList($explicitSelect = TRUE){
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
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'position', 'string');
        $order_way = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');
        $published = $app->getUserStateFromRequest($option. $view. '.published', 'published', 0, 'string');


        /* Manage default params values */
        $use_limit = true;
        if ($limit === false)
            $use_limit = false;

        /* Query in order to get results with all fields */
        $lang_join = '';
        if ($lang_id){
            $lang_join = " LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON (";
            $lang_join .= "attribute_group_lang." . $db->quoteName('attribute_group_id') . " = attribute_group.";
            $lang_join .= $db->quoteName('attribute_group_id');
            $lang_join .= " AND attribute_group_lang." . $db->quoteName('lang_id') . " = " .(int)$lang_id .") ";

        }

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS ";
            if($explicitSelect){
                $query .= "attribute_group." . $db->quoteName('attribute_group_id') . ", attribute_group_lang.";
                $query .= $db->quoteName('name') . ", attribute_group." . $db->quoteName('position');
            }

            $query .= " FROM " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group " . $lang_join;

            $query .= " ORDER BY " . ((str_replace('`', '', $order_by) == 'attribute_group_id') ? "attribute_group." : ""). " attribute_group." ;
            $query .= $order_by ." " . $db->escape($order_way) . (($use_limit === true) ? " LIMIT " . (int)$limitstart.", ".(int)$limit : "" );

            $db->setQuery($query);
            $attribute_groups = $db->loadObjectList();

            if ($use_limit === true){
                $limitstart = (int)$limitstart - (int)$limit;
                if ($limitstart < 0){ break; }
            }else{ break; }
        }while(empty($attribute_groups));

        foreach($attribute_groups as $attribute_group){
            $query = "SELECT attribute.attribute_id as count_values FROM " . $db->quoteName('#__jeproshop_attribute') . " AS attribute ";
            $query .= JeproshopShopModelShop::addSqlAssociation('attribute') . " WHERE attribute.attribute_group_id = ";
            $query .= (int)$attribute_group->attribute_group_id . " GROUP BY attribute_shop.shop_id ORDER BY count_values DESC";

            $db->setQuery($query);
            $attribute_group->count_values = (int)count($db->loadObjectList());
        }

        $total = count($attribute_groups);

        $this->pagination = new JPagination($total, $limitstart, $limit);
        return $attribute_groups;
    }
}