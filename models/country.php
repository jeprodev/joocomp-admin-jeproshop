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

class JeproshopCountryModelCountry extends JeproshopModel
{
    public $country_id;

    public $lang_id;

    public $shop_id;

    public $zone_id;

    public $currency_id;

    public $states = array();

    public $name = array();

    public $iso_code;

    public $call_prefix;

    public $published;

    public $contains_states;

    public $need_identification_number;

    public $need_zip_code;

    public $zip_code_format;

    public $display_tax_label;

    public $country_display_tax_label;
    public $get_shop_from_context = false;

    public $multiLangShop = true;
    public $multiLang = true;

    protected static $_zone_ids = array();
    protected static $cache_iso_by_id = array();

    
    public function __construct($countryId = null, $langId = null, $shopId = NULL){
        $db = JFactory::getDBO();

        if($langId !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($langId) ? (int)$langId : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($shopId  && $this->isMultiShop('country', $this->multiLangShop)){
            $this->shop_id = (int)$shopId;
            $this->get_shop_from_context = FALSE;
        }

        if($this->isMultiShop('country', $this->multiLangShop) && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($countryId){
            $cacheKey = 'jeproshop_country_model_' . (int)$countryId . '_' . (int)$langId . '_' . (int)$shopId;
            if(!JeproshopCache::isStored($cacheKey)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_country') . " AS country ";

                //Get language data
                if($langId){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country_lang."
                        . $db->quoteName('country_id') . " = country." . $db->quoteName('country_id') . " AND country_lang." 
                        . $db->quoteName('lang_id') . " = " . (int)$langId . ")";
                }

                if(JeproshopShopModelShop::isTableAssociated('country')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_country_shop') . " AS country_shop ON (country_shop." 
                        . $db->quoteName('country_id') . " = country." . $db->quoteName('country_id') . " AND country_shop." 
                        . $db->quoteName('shop_id') . " = " . (int)$shopId . ") ";
                }

                $db->setQuery($query);
                $countryData = $db->loadObject();

                if($countryData){
                    if(!$langId){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_country_lang') . " WHERE " . $db->quoteName('country_id') . " = " . (int)$countryId;

                        $db->setQuery($query);
                        $countryLangData = $db->loadObjectList();
                        if($countryLangData){
                            foreach($countryLangData as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'country_id'){
                                        if(!isset($countryData->{$key}) || !is_array($countryData->{$key})){
                                            $countryData->{$key} = array();
                                        }
                                        $countryData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cacheKey, $countryData);
                }
            }else{
                $countryData = JeproshopCache::retrieve($cacheKey);
            }

            if($countryData){
                $countryData->country_id = (int)$countryId;
                foreach($countryData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    /**
     * @brief Return available countries
     *
     * @param integer $lang_id Language ID
     * @param boolean $published return only active countries
     * @param boolean $contain_states return only country with states
     * @param boolean $list_states Include the states list with the returned list
     *
     * @return Array Countries and corresponding zones
     */
    public static function getStaticCountries($lang_id, $published = false, $contain_states = false, $list_states = true) {
        $countries = array();
        $db = JFactory::getDBO();

        $query = "SELECT country_lang.*, country.*, country_lang." . $db->quoteName('name') . " AS country_name, zone." . $db->quoteName('name');
        $query .= " AS zone_name FROM " . $db->quoteName('#__jeproshop_country') . " AS country " . JeproshopShopModelShop::addSqlAssociation('country');
        $query .= "	LEFT JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country." . $db->quoteName('country_id') ;
        $query .= " = country_lang." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id') . " = " .(int)$lang_id;
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_zone') . " AS zone ON (zone." . $db->quoteName('zone_id') . " = country.";
        $query .= $db->quoteName('zone_id') . ") WHERE 1 " .($published ? " AND country.published = 1" : "") ;
        $query .= ($contain_states ? " AND country." . $db->quoteName('contains_states') . " = " .(int)$contain_states : "")." ORDER BY country_lang.name ASC";

        $db->setQuery($query);
        $result = $db->loadObjectList();
        foreach ($result as $row){ $countries[$row->country_id] = $row; }

        if ($list_states){
            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_state') . " ORDER BY " . $db->quoteName('name') . " ASC";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            foreach ($result as $row)
                if (isset($countries[$row->country_id]) && $row->published == 1) /* Does not keep the state if its country has been disabled and not selected */
                    $countries[$row->country_id]->states[] = $row;
        }
        return $countries;
    }

    /**
     * This method is allow to know if a entity is currently used
     * @since 1.5.0.1
     * @param string $table name of table linked to entity
     * @param bool $hasActiveColumn true if the table has an active column
     * @return bool
     */
    public static function isCurrentlyUsed($table = null, $hasActiveColumn = false) {
        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('country_id') . " FROM " . $db->quoteName('#__jeproshop_country') ;
        $query .= " WHERE " . $db->quoteName('published') . " = 1";
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Get a country name with its ID
     *
     * @param $langId
     * @param $countryId
     * @return string Country name
     */
    public static function getCountryNameByCountryId($langId, $countryId){
        $key = 'jeproshop_country_get_name_by_id_' . $countryId .'_' . $langId;
        if (!JeproshopCache::isStored($key)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_country_lang') . " WHERE ";
            $query .= $db->quoteName('lang_id') . " = " . (int)$langId . " AND " . $db->quoteName('country_id') . " = ".(int)$countryId;

            $db->setQuery($query);

            JeproshopCache::store($key, $db->loadResult());
        }
        return JeproshopCache::retrieve($key);
    }

    public function getCountries($lang_id, $published = FALSE, $contain_states = FALSE, $states_list = TRUE){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');

        $query = "SELECT SQL_CALC_FOUND_ROWS country." . $db->quoteName('country_id') . ", country_lang." . $db->quoteName('name') . " AS name,";
        $query .= $db->quoteName('iso_code') . ", " . $db->quoteName('call_prefix') . ",zone." . $db->quoteName('zone_id'). " AS zone,";
        $query .= "country.published AS published, zone." . $db->quoteName('name') . " AS zone_name FROM " . $db->quoteName('#__jeproshop_country');
        $query .= " AS country LEFT JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country_lang.";
        $query .= $db->quoteName('country_id') . " = country." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id');
        $query .= " = " . $lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_zone') . " AS zone ON (zone." . $db->quoteName('zone_id');
        $query .= " = country." . $db->quoteName('zone_id') .") WHERE 1 ORDER BY country." . $db->quoteName('country_id');
        $query .= " ASC LIMIT " .$limitstart . ", " . $limit;

        $db->setQuery($query);
        $countries = $db->loadObjectList();

        $total = count($countries);

        $this->pagination = new JPagination($total, $limitstart, $limit);
        return $countries;
    }

    public function getCountryList(JeproshopContext $context = NULL){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!$context){ $context = JeproshopContext::getContext(); }

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $langId = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $shop_id = $app->getUserStateFromRequest($option. $view. '.shop_id', 'shop_id', $context->shop->shop_id, 'int');
        $shop_group_id = $app->getUserStateFromRequest($option. $view. '.shop_group_id', 'shop_group_id', $context->shop->shop_group_id, 'int');
        $category_id = $app->getUserStateFromRequest($option. $view. '.cat_id', 'cat_id', 0, 'int');
        $order_by = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'date_add', 'string');
        $orderWay = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');
        $published = $app->getUserStateFromRequest($option. $view. '.published', 'published', 0, 'string');
        $productAttribute_id = $app->getUserStateFromRequest($option. $view. '.product_attribute_id', 'product_attribute_id', 0, 'int');

        $useLimit = true;
        if ($limit === false)
            $useLimit = false;

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS country." . $db->quoteName('country_id') . ", country_lang." . $db->quoteName('name');
            $query .= " AS name, country." . $db->quoteName('iso_code') . ", country." . $db->quoteName('call_prefix') . ", country.";
            $query .= $db->quoteName('published') . ",zone." . $db->quoteName('zone_id'). " AS zone, zone." . $db->quoteName('name');
            $query .= " AS zone_name FROM " . $db->quoteName('#__jeproshop_country') . " AS country LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country_lang." . $db->quoteName('country_id');
            $query .= " = country." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id') . " = " . $langId;
            $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_zone') . " AS zone ON (zone." . $db->quoteName('zone_id') . " = country.";
            $query .= $db->quoteName('zone_id') .") WHERE 1 ORDER BY country." . $db->quoteName('country_id');

            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($useLimit === true) ? " LIMIT " .(int)$limitStart . ", " .(int)$limit : "");

            $db->setQuery($query);
            $countries = $db->loadObjectList();

            if($useLimit == true){
                $limitStart = (int)$limitStart -(int)$limit;
                if($limitStart < 0){ break; }
            }else{ break; }
        }while(empty($countries));

        $this->pagination = new JPagination($total, $limitStart, $limit);
        return $countries;
    }

    public static function getCountriesByZoneId($zoneId, $langId = null, $withStates = false){
        if (empty($zoneId)) {
            die(JError::raiseError("an error occured jeff"));
        }

        if($langId == null){
            $langId = JeproshopContext::getContext()->language->lang_id;
        }
        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT country.*, country_lang.* FROM " . $db->quoteName('#__jeproshop_country') . " AS country ";
        //$query .= JeproshopShopModelShop::addSqlAssociation('country', false) ;
        if($withStates) {
            $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_state') . " AS state ON (state." . $db->quoteName('country_id') . " = country.";
            $query .= $db->quoteName('country_id') . " AND state." . $db->quoteName('lang_id') . " = " . (int)$langId . ") ";
        }
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country." . $db->quoteName('country_id');
        $query .= " = country_lang." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id') ;
        $query .= ") WHERE country." . $db->quoteName('zone_id') . " = " . (int)$zoneId;
        /*if($withStates) {
            $query .= " OR state." . $db->quoteName('zone_id') . " = " . (int)$zoneId;
        }
        $query .= " "; // . $db->quoteName('lang_id') . " = " . (int)$langId; */

        $db->setQuery($query);
        return $db->loadObjectList();
    }

}