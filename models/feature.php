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

class JeproshopFeatureModelFeature extends JeproshopModel{
    public $feature_id;

    public $shop_id;

    public $lang_id;

    /** @var string Name */
    public $name;
    public $position;

    public $multiLang = true;
    public $multiLangShop = false;

    public function __construct($featureId = null, $langId = null, $shopId = null){
        if ($langId !== null) {
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($langId) !== false) ? $langId : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if ($shopId && $this->isMultiShop("feature", $this->multiLangShop)){
            $this->shop_id = (int)$shopId;
            $this->get_shop_from_context = false;
        }

        if ($this->isMultiShop('feature', $this->multiLangShop) && !$this->shop_id)
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;

        if ($featureId){
            // Load object from database if object id is present
            $cacheKey = 'jeproshop_feature_model_' . (int)$featureId . '_' . (int)$this->shop_id . '_' . (int)$langId;
            if (!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_feature') . " AS feature ";

                $where = " WHERE feature." . $db->quoteName('feature_id') . " = " . (int)$featureId;

                // Get lang information
                if ($langId){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_feature_lang') . " AS feature_lang ON (feature." . $db->quoteName('feature_id') . " = ";
                    $query .= $db->quoteName('feature_id') . " AND feature_lang." .$db->quoteName('lang_id') . " = " . (int)$langId . ") ";
                    if ($this->shop_id && !empty($this->multiLangShop)) {
                        $where .= " AND feature_lang." . $db->quoteName('shop_id') . " = " . $this->shop_id ;
                    }
                }

                // Get shop information
                if (JeproshopShopModelShop::isTableAssociated('feature')) {
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_feature_shop') . " AS feature_shop ON (feature." . $db->quoteName('feature_id') . " = feature_shop.";
                    $query .= $db->quoteName('feature_id') . " AND feature_shop." .$db->quoteName('shop_id') . " = " . (int)$this->shop_id . ") ";
                }

                $db->setQuery($query . $where);
                $featureData = $db->loadObject();
                if ($featureData) {
                    if (!$langId && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_feature_lang') . " WHERE " . $db->quoteName('feature_id') . " = " .(int)$featureId;
                        $query .= (($this->shop_id && $this->isLangMultiShop()) ? " AND " . $db->quoteName('shop_id') . " = " . $this->shop_id : "");

                        $db->setQuery($query);
                        $featureLangData = $db->loadObjectList();
                        if ($featureLangData) {
                            foreach ($featureLangData as $row) {
                                foreach ($row as $key => $value) {
                                    if (array_key_exists($key, $this) && $key != 'feature_id') {
                                        if (!isset($featureData->{$key}) || !is_array($featureData->{$key})) {
                                            $featureData->{$key} = array();
                                        }
                                        $featureData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cacheKey, $featureData);
                }
            } else {
                $featureData = JeproshopCache::retrieve($cacheKey);
            }

            if ($featureData){
                //$this->id = (int)$id;
                foreach ($featureData as $key => $value)
                    if (array_key_exists($key, $this))
                        $this->{$key} = $value;
            }
        }
    }

    public function getFeatureList($explicitSelect = TRUE){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        $context = JeproshopContext::getContext();

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $shopId = $app->getUserStateFromRequest($option. $view. '.shop_id', 'shop_id', $context->shop->shop_id, 'int');
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
            $lang_join = " LEFT JOIN " . $db->quoteName('#__jeproshop_feature_lang') . " AS feature_lang ON (";
            $lang_join .= "feature_lang." . $db->quoteName('feature_id') . " = feature." . $db->quoteName('feature_id');
            $lang_join .= " AND feature_lang." . $db->quoteName('lang_id') . " = " .(int)$lang_id .") ";
        }

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS ";
            if($explicitSelect){
                $query .= "feature." . $db->quoteName('feature_id') . ", feature_lang.";
                $query .= $db->quoteName('name') . ", feature." . $db->quoteName('position');
            }

            $query .= " FROM " . $db->quoteName('#__jeproshop_feature') . " AS feature " . $lang_join;

            $query .= " ORDER BY " . ((str_replace('`', '', $order_by) == 'feature_id') ? "feature." : ""). " feature." ;
            $query .= $order_by ." " . $db->escape($order_way) . (($use_limit === true) ? " LIMIT " . (int)$limitstart.", ".(int)$limit : "" );


            $db->setQuery($query);
            $features = $db->loadObjectList();

            if ($use_limit === true){
                $limitstart = (int)$limitstart - (int)$limit;
                if ($limitstart < 0){ break; }
            }else{ break; }
        }while(empty($features));

        foreach($features as $feature){
            $query = "SELECT feature_value.feature_value_id AS count_values FROM " . $db->quoteName('#__jeproshop_feature_value');
            $query .= " AS feature_value WHERE feature_value.feature_id = " . $feature->feature_id . " AND (feature_value.custom";
            $query .= " = 0 OR feature_value.custom IS NULL)";

            $db->setQuery($query);
            $feature->count_values = count($db->loadObjectList());
        }

        $total = count($features);

        $this->pagination = new JPagination($total, $limitstart, $limit);
        return $features;
    }

    /**
     * This method is allow to know if a feature is used or active
     * @since 1.5.0.1
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('feature_feature_active');
    }

    /**
     * Get all features for a given language
     *
     * @param integer $langId Language id
     * @param bool $withShop
     * @return array Multiple arrays with feature's data
     * @static
     */
    public static function getFeatures($langId, $withShop = true){
        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT feature.feature_id, feature.*, feature_lang.* FROM " . $db->quoteName('#__jeproshop_feature');
        $query .= " AS feature " .($withShop ? JeproshopShopModelShop::addSqlAssociation('feature') : "") . " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_feature_lang') . " AS feature_lang ON (feature." . $db->quoteName('feature_id');
        $query .= " = feature_lang." . $db->quoteName('feature_id') . " AND feature_lang." . $db->quoteName('lang_id') . " = ";
        $query .= (int)$langId . ") ORDER BY feature." . $db->quoteName('position') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function isLangMultiShop() {
        return !empty($this->multiLang) && !empty($this->multiLangShop);
    }

    public function getFeatureValues($langId = null){
        return JeproshopFeatureValueModelFeatureValue::getFeatureValues($this->feature_id, $langId);
    }

}


class JeproshopFeatureValueModelFeatureValue extends JeproshopModel
{
    /** @var integer Group id which attribute belongs */
    public $feature_id;

    public $feature_value_id;

    public $lang_id;

    /** @var string Name */
    public $value;

    /** @var boolean Custom */
    public $custom = 0;

    public function __construct($featureValueId = null, $langId = null){
        if($langId != null){
            $this->lang_id = JeproshopLanguageModelLanguage::getLanguage($langId) ? $langId : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($featureValueId){
            $cacheKey = 'jeproshop_feature_value_' . $featureValueId . '_' . $langId;

            if(JeproshopCache::isStored($cacheKey)){
                $featureValueData = JeproshopCache::retrieve($cacheKey);
            }else{
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_feature_value') . " AS feature_value ";
                if($langId != null){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_feature_value_lang') . " AS feature_value_lang ON (feature_value_lang.";
                    $query .= $db->quoteName('feature_value_id') . " = feature_value." . $db->quoteName('feature_value_id') . " AND feature_value_lang.";
                    $query .= $db->quoteName('lang_id') . " = " . $langId . ") ";
                }
                $query .= " WHERE feature_value." . $db->quoteName('feature_value_id') . " = " . (int)$featureValueId;

                $db->setQuery($query);
                $featureValueData = $db->loadObject();

                if($featureValueData){
                    if(!$langId){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_feature_value_lang') . " WHERE " . $db->quoteName('feature_value_id');
                        $query .= " = " . $featureValueId ;

                        $db->setQuery($query);

                        $featureValueLangData = $db->loadObjectList();
                        if($featureValueLangData){
                            foreach ($featureValueLangData as $row){
                                foreach ($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'feature_value_id'){
                                        if(!isset($featureValueData->{$key}) || !is_array($featureValueData->{$key})){
                                            $featureValueData->{$key} = array();
                                        }
                                        $featureValueData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                }
                JeproshopCache::store($cacheKey, $featureValueData);
            }

            if($featureValueData){
                $this->feature_value_id = $featureValueId;
                foreach($featureValueData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    /**
     * Get all values for a given feature and language
     *
     * @param integer $langId Language id
     * @param boolean $featureId Feature id
     * @param bool $custom
     * @return array Array with feature's values
     * @static
     */
    public static function getFeatureValuesWithLang($langId, $featureId, $custom = false)
    {
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_feature_value') . " AS feature_value LEFT JOIN " . $db->quoteName('#__jeproshop_feature_value_lang');
        $query .= " AS feature_value_lang ON (feature_value." . $db->quoteName('feature_value_id') . " = feature_value_lang." . $db->quoteName('feature_value_id');
        $query .= " AND feature_value_lang." . $db->quoteName('lang_id') . " = " . (int)$langId . ") WHERE feature_value." . $db->quoteName('feature_id') . " = ";
        $query .= (int)$featureId . (!$custom ? " AND (feature_value." . $db->quoteName('custom') . " IS NULL OR feature_value." . $db->quoteName('custom') . " = 0)" : "");
        $query .= "	ORDER BY feature_value_lang." . $db->quoteName('value') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get all language for a given value
     *
     * @param boolean $featureValueId Feature value id
     * @return array Array with value's languages
     * @static
     */
    public static function getFeatureValueLang($featureValueId)
    {
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_feature_value_lang') . " WHERE " . $db->quoteName('feature_value_id') . " = ";
        $query .= (int)$featureValueId . " ORDER BY " . $db->quoteName('lang_id');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getFeatureValues($featureId, $langId = null){
        $db = JFactory::getDBO();

        $query = "SELECT feature_value.*, feature_value_lang.* FROM " . $db->quoteName('#__jeproshop_feature_value') . " AS feature_value ";
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_feature_value_lang') . " AS feature_value_lang ON (feature_value_lang." ;
        $query .= $db->quoteName('feature_value_id') . " = feature_value." . $db->quoteName('feature_value_id') ;
        $query .= ($langId ? " AND feature_value_lang." . $db->quoteName('lang_id') . " = " . $langId : " ") . ") WHERE feature_value.";
        $query .= $db->quoteName('feature_id') . " = " . (int)$featureId;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

}