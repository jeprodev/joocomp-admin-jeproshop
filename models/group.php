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

class JeproshopGroupModelGroup extends  JeproshopModel {
    public $group_id;

    public $shop_id;

    public $lang_id;

    public $name;

    public $reduction;

    public $show_prices = 1;

    public $price_display_method;

    public $date_add;
    public $date_upd;

    protected static $cache_reduction = array();
    protected static $group_price_display_method;

    public function __construct($groupId = null, $langId = null, $shopId = null){
        if($langId !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($langId) ? (int)$langId : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($shopId && $this->isMultiShop('group', false)){
            $this->shop_id = (int)$shopId;
            $this->getShopFromContext = FALSE;
        }

        if($this->isMultiShop('group', false) && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($groupId){
            $cacheKey = 'jeproshop_group_model_' . $groupId . '_' . $langId . '_' . $shopId;
            if(!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_group') . " AS j_group ";
                $where = "";
                /** get language information **/
                if($langId){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_group_lang') . " AS group_lang ";
                    $query .= "ON (j_group.group_id = group_lang.group_id AND group_lang.lang_id = " . (int)$langId . ") ";
                    if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND group_lang.shop_id = " . $this->shop_id;
                    }
                }

                /** Get shop information **/
                if(JeproshopShopModelShop::isTableAssociated('group')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_group_shop') . " AS group_shop ON (";
                    $query .= "j_group.group_id = group_shop.group_id AND group_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE j_group.group_id = " . (int)$groupId . $where;

                $db->setQuery($query);
                $groupData = $db->loadObject();

                if($groupData){
                    if(!$langId && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_group_lang');
                        $query .= " WHERE group_id = " . (int)$groupId;

                        $db->setQuery($query);
                        $groupLangData = $db->loadObjectList();
                        if($groupLangData){
                            foreach ($groupLangData as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'group_id'){
                                        if(!isset($groupData->{$key}) || !is_array($groupData->{$key})){
                                            $groupData->{$key} = array();
                                        }
                                        $groupData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cacheKey, $groupData);
                }
            }else{
                $groupData = JeproshopCache::retrieve($cacheKey);
            }

            if($groupData){
                $this->group_id = $groupId;
                foreach($groupData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        if($this->group_id && !isset(JeproshopGroupModelGroup::$group_price_display_method[$this->group_id])){
            self::$group_price_display_method[$this->group_id] = $this->price_display_method;
        }
    }

    /**
     * This method is allow to know if a feature is used or active
     *
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('group_feature_active');
    }

    public static function getStaticGroups($langId, $shopId = false){
        $db = JFactory::getDBO();
        $shopCriteria = '';
        if ($shopId){
            $shopCriteria = JeproshopShopModelShop::addSqlAssociation('group');
        }

        $query = "SELECT DISTINCT grp." . $db->quoteName('group_id') . ", grp." . $db->quoteName('reduction') . ", grp." . $db->quoteName('price_display_method');
        $query .= ", group_lang." . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_group') . " AS grp LEFT JOIN " . $db->quoteName('#__jeproshop_group_lang');
        $query .= " AS group_lang ON (grp." . $db->quoteName('group_id') . " = group_lang." . $db->quoteName('group_id') . " AND group_lang." . $db->quoteName('lang_id');
        $query .= " = " .(int)$langId . ") " . $shopCriteria . " ORDER BY grp." . $db->quoteName('group_id') . " ASC" ;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getGroups($shopId = FALSE){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        $context = JeproshopContext::getContext();
        $shop_criteria = '';
        if ($shopId){
            $shop_criteria = JeproshopShopModelShop::addSqlAssociation('group');
        }

        $langId = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        /*$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int'); */

        $query = "SELECT DISTINCT grp." . $db->quoteName('group_id') . ", grp." . $db->quoteName('reduction') . ", grp.";
        $query .= $db->quoteName('price_display_method') . ", group_lang." . $db->quoteName('name') . " FROM " ;
        $query .= $db->quoteName('#__jeproshop_group') . " AS grp LEFT JOIN " . $db->quoteName('#__jeproshop_group_lang');
        $query .= " AS group_lang ON (grp." . $db->quoteName('group_id') . " = group_lang." . $db->quoteName('group_id');
        $query .= " AND group_lang." . $db->quoteName('lang_id') . " = " .(int)$langId . ") " . $shop_criteria ;
        $query .= " ORDER BY grp." . $db->quoteName('group_id') . " ASC";

        $db->setQuery($query);
        $groups = $db->loadObjectList();
        return $groups;
    }


    public static function getDefaultPriceDisplayMethod(){
        return JeproshopGroupModelGroup::getPriceDisplayMethod((int)  JeproshopSettingModelSetting::getValue('customer_group'));
    }

    public static function getPriceDisplayMethod($group_id){
        if(!isset(JeproshopGroupModelGroup::$group_price_display_method[$group_id])){
            $db = JFactory::getDbO();

            $query = "SELECT " . $db->quoteName('price_display_method') . " FROM " . $db->quoteName('#__jeproshop_group');
            $query .= " WHERE " . $db->quoteName('group_id') . " = " . (int)$group_id;

            $db->setQuery($query);
            self::$group_price_display_method[$group_id] = $db->loadResult();
        }
        return self::$group_price_display_method[$group_id];
    }

    /**
     * Return current group object
     * Use context
     * @static
     * @return JeproshopGroupModelGroup Group object
     */
    public static function getCurrent()	{
        static $groups = array();

        $customer = JeproshopContext::getContext()->customer;
        if (JeproshopTools::isLoadedObject($customer, 'customer_id')){
            $group_id = (int)$customer->default_group_id;
        }else{
            $group_id = (int)  JeproshopSettingModelSetting::getValue('unidentified_group');
        }

        if (!isset($groups[$group_id])){
            $groups[$group_id] = new JeproshopGroupModelGroup($group_id);
        }

        if (!$groups[$group_id]->isAssociatedToShop(JeproshopContext::getContext()->shop->shop_id)){
            $group_id = (int)  JeproshopSettingModelSetting::getValue('customer_group');
            if (!isset($groups[$group_id])){
                $groups[$group_id] = new JeproshopGroupModelGroup($group_id);
            }
        }
        return $groups[$group_id];
    }

    public function isAssociatedToShop($shopId = NULL){
        if($shopId === NULL){
            $shopId = (int)JeproshopContext::getContext()->shop->shop_id;
        }

        $cacheKey = 'jeproshop_shop_model_group_' . (int)$this->group_id . '_' . (int)$shopId;
        if(!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();
            $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_group_shop') . " WHERE " . $db->quoteName('group_id') . " = " . (int)$this->group_id;
            $query .= " AND shop_id = " . (int)$shopId;

            $db->setQuery($query);
            $result = (bool)$db->loadResult();
            JeproshopCache::store($cacheKey, $result);
        }
        return JeproshopCache::retrieve($cacheKey);
    }


}


class JeproshopGroupReductionModelGroupReduction extends JeproshopModel {
    public	$group_id;
    public	$category_id;
    public	$reduction;

    protected static $reduction_cache = array();

    public static function getValueForProduct($productId, $groupId){
        if (!JeproshopGroupModelGroup::isFeaturePublished()){ return 0; }

        if (!isset(self::$reduction_cache[$productId . '_' . $groupId])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('reduction') . " FROM " . $db->quoteName('#__jeproshop_product_group_reduction_cache');
            $query .= " WHERE " . $db->quoteName('product_id') . " = " .(int)$productId . " AND " . $db->quoteName('group_id') . " = " .(int)$groupId;

            $db->setQuery($query);
            $reduction = $db->loadObject();
            self::$reduction_cache[$productId.'_'.$groupId] = ($reduction ? $reduction : 0);
        }
        // Should return string (decimal in database) and not a float
        return self::$reduction_cache[$productId. '_'. $groupId];
    }
}