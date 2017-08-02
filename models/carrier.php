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


class JeproshopCarrierModelCarrier extends JeproshopModel{
    /**
     * getCarriers method filter
     */
    const JEPROSHOP_CARRIERS_ONLY = 1;
    const JEPROSHOP_CARRIERS_MODULE = 2;
    const JEPROSHOP_CARRIERS_MODULE_NEED_RANGE = 3;
    const JEPROSHOP_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE = 4;
    const JEPROSHOP_ALL_CARRIERS = 5;

    const SORT_BY_PRICE = 0;
    const SORT_BY_POSITION = 1;

    const SORT_BY_ASC = 0;
    const SORT_BY_DESC = 1;

    const DEFAULT_SHIPPING_METHOD = 0;
    const WEIGHT_SHIPPING_METHOD = 1;
    const PRICE_SHIPPING_METHOD = 2;
    const FREE_SHIPPING_METHOD = 3;

    public $carrier_id;

    public $lang_id;

    public $shop_id;
    /** @var int common id for carrier historization */
    public $reference_id;

    /** @var string Name */
    public $name;

    /** @var string URL with a '@' for */
    public $url;

    /** @var string Delay needed to deliver customer */
    public $delay;

    /** @var boolean Carrier status */
    public $published = true;

    /** @var boolean True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;

    /** @var boolean Active or not the shipping handling */
    public $shipping_handling = true;

    /** @var int Behavior taken for unknown range */
    public $range_behavior;

    /** @var boolean Carrier module */
    public $is_module;

    /** @var boolean Free carrier */
    public $is_free = false;

    private $multiLang = true;

    /** @var int shipping behavior: by weight or by price */
    public $shipping_method = 0;

    /** @var boolean Shipping external */
    public $shipping_external = 0;

    /** @var string Shipping external */
    public $external_module_name = null;

    /** @var boolean Need Range */
    public $need_range = 0;

    /** @var int Position */
    public $position;

    /** @var int maximum package width managed by the transporter */
    public $max_width;

    /** @var int maximum package height managed by the transporter */
    public $max_height;

    /** @var int maximum package deep managed by the transporter */
    public $max_depth;

    /** @var int maximum package weight managed by the transporter */
    public $max_weight;

    /** @var int grade of the shipping delay (0 for longest, 9 for shortest) */
    public $grade;

    public function __construct($carrierId = null, $langId = null){
        $db = JFactory::getDBO();

        if($langId !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($langId) ? (int)$langId : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($carrierId){
            $cacheKey = 'jeproshop_carrier_model_' . $carrierId . '_' . $langId;
            if(!JeproshopCache::isStored($cacheKey)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_carrier') . " AS carrier ";
                $where = "";
                /** get language information **/
                if($langId){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_carrier_lang') . " AS carrier_lang ON (carrier.";
                    $query .= "carrier_id = carrier_lang.carrier_id AND carrier_lang.lang_id = " . (int)$langId . ") ";
                    /*if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND carrier_lang.shop_id = " . $this->shop_id;
                    }*/
                }

                /** Get shop information **/
                if(JeproshopShopModelShop::isTableAssociated('carrier')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_carrier_shop') . " AS carrier_shop ON (carrier.";
                    $query .= "carrier_id = carrier_shop.carrier_id AND carrier_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE carrier.carrier_id = " . (int)$carrierId . $where;

                $db->setQuery($query);
                $carrierData = $db->loadObject();

                if($carrierData){
                    if(!$langId && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_carrier_lang');
                        $query .= " WHERE carrier_id = " . (int)$carrierId;

                        $db->setQuery($query);
                        $carrierLangData = $db->loadObjectList();
                        if($carrierLangData){
                            foreach ($carrierLangData as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'carrier_id'){
                                        if(!isset($carrierData->{$key}) || !is_array($carrierData->{$key})){
                                            $carrierData->{$key} = array();
                                        }
                                        $carrierData->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cacheKey, $carrierData);
                }
            }else{
                $carrierData = JeproshopCache::retrieve($cacheKey);
            }

            if($carrierData) {
                $carrierData->carrier_id = $carrierId;
                foreach ($carrierData as $key => $value) {
                    if (array_key_exists($key, $this)) {
                        $this->{$key} = $value;
                    }
                }
            }
        }

        /**
         * keep retro-compatibility SHIPPING_METHOD_DEFAULT
         * @deprecated 1.5.5
         */
        if ($this->shipping_method == JeproshopCarrierModelCarrier::DEFAULT_SHIPPING_METHOD){
            $this->shipping_method = ((int)JeproshopSettingModelSetting::getValue('shipping_method') ? JeproshopCarrierModelCarrier::WEIGHT_SHIPPING_METHOD : JeproshopCarrierModelCarrier::PRICE_SHIPPING_METHOD);
        }
        /**
         * keep retro-compatibility id_tax_rules_group
         * @deprecated 1.5.0
         */
        if ($this->carrier_id){
            $this->tax_rules_group_id = $this->getTaxRulesGroupId(JeproshopContext::getContext());
        }
        if ($this->name == '0'){
            $this->name = JeproshopSettingModelSetting::getValue('shop_name');
        }
        $this->image_dir = COM_JEPROSHOP_CARRIER_IMAGE_DIR;
    }

    /**
     * Get all carriers in a given language
     *
     * @param integer $langId Language id
     * @param bool $published
     * @param bool $delete
     * @param bool $zoneId
     * @param null $groupIds
     * @param int $modulesFilters , possible values:
     *
     * PS_CARRIERS_ONLY
     * CARRIERS_MODULE
     * CARRIERS_MODULE_NEED_RANGE
     * PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
     * ALL_CARRIERS
     * @internal param bool $active Returns only active carriers when true
     * @return array Carriers
     */
    public static function getCarriers($langId, $published = false, $delete = false, $zoneId = false, $groupIds = null, $modulesFilters = self::JEPROSHOP_CARRIERS_ONLY){
        // Filter by groups and no groups => return empty array
        if ($groupIds && (!is_array($groupIds) || !count($groupIds))){ return array(); }

        $db = JFactory::getDBO();

        $query = "SELECT carrier.*, carrier_lang.delay FROM " . $db->quoteName('#__jeproshop_carrier') . " AS carrier LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_carrier_lang') . " AS carrier_lang ON (carrier." . $db->quoteName('carrier_id');
        $query .= " = carrier_lang." . $db->quoteName('carrier_id') . " AND carrier_lang." . $db->quoteName('lang_id') . " = ";
        $query .= (int)$langId . JeproshopShopModelShop::addSqlRestrictionOnLang('carrier_lang'). ") LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_carrier_zone') . " AS carrier_zone ON (carrier_zone." . $db->quoteName('carrier_id');
        $query .= " = carrier." . $db->quoteName('carrier_id') . ") " . ($zoneId ? "LEFT JOIN " . $db->quoteName('#__jeproshop_zone') . " AS zone ON (zone." . $db->quoteName('zone_id') . " = " .(int)$zoneId . ")" : "");
        $query .= JeproshopShopModelShop::addSqlAssociation('carrier') . " WHERE carrier." . $db->quoteName('deleted') . " = ";
        $query .= ($delete ? "1" : "0") . ($published ? " AND carrier." . $db->quoteName('published') . " = 1 " : "");
        if ($zoneId){
            $query .= " AND carrier_zone." . $db->quoteName('zone_id') . " = " . (int)$zoneId . " AND zone." . $db->quoteName('published') . " = 1 ";
        }
        if ($groupIds){
            $query .= ' AND c.id_carrier IN (SELECT id_carrier FROM '._DB_PREFIX_.'carrier_group WHERE id_group IN ('.implode(',', array_map('intval', $groupIds)).')) ';
        }
        switch ($modulesFilters){
            case 1 :
                $query .= " AND carrier.is_module = 0 ";
                break;
            case 2 :
                $query .= " AND carrier.is_module = 1 ";
                break;
            case 3 :
                $query .= " AND carrier.is_module = 1 AND carrier.need_range = 1 ";
                break;
            case 4 :
                $query .= " AND (carrier.is_module = 0 OR carrier.need_range = 1) ";
                break;
        }
        $query .= " GROUP BY carrier." . $db->quoteName('carrier_id') . " ORDER BY carrier." . $db->quoteName('position') . " ASC";


        $cache_id = 'Carrier::getCarriers_'.md5($query);
        if (!JeproshopCache::isStored($cache_id)){
            $db->setQuery($query);
            $carriers = $db->loadObjectList();
            JeproshopCache::store($cache_id, $carriers);
        }
        $carriers = JeproshopCache::retrieve($cache_id);
        foreach ($carriers as $key => $carrier){
            if ($carrier->name == '0'){
                $carriers[$key]->name = JeproshopSettingModelSetting::getValue('shop_name');
            }
        }
        return $carriers;
    }

    /**
     * Get all zones
     *
     * @return array Zones
     */
    public function getZones(){
        $db = JFactory::getDBO();
        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_carrier_zone') . " AS carrier_zone LEFT JOIN " . $db->quoteName('#__jeproshop_zone');
        $query .= " AS zone ON carrier_zone." . $db->quoteName('zone_id') . " = zone." . $db->quoteName('zone_id') . " WHERE carrier_zone.";
        $query .= $db->quoteName('carrier_id') . " = " .(int)$this->carrier_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getCarriersList(JeproshopContext $context = null){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!$context){ $context = JeproshopContext::getContext(); }

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limitstart', 'limitstart', 0, 'int');
        $lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $orderBy = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'position', 'string');
        $orderWay = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC', 'string');

        $useLimit = true;
        if ($limit === false)
            $useLimit = false;

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS carrier." . $db->quoteName('carrier_id') . ", carrier." . $db->quoteName('name') . ", carrier.";
            $query .= $db->quoteName('published') . ", carrier." . $db->quoteName('is_free') . ", carrier." . $db->quoteName('position');
            $query .= ", carrier_lang.* FROM " . $db->quoteName('#__jeproshop_carrier') . " AS carrier LEFT JOIN " . $db->quoteName('#__jeproshop_carrier_lang');
            $query .= " AS carrier_lang ON(carrier." . $db->quoteName('carrier_id') . " = carrier_lang." . $db->quoteName('carrier_id') ;
            $query .= JeproshopShopModelShop::addSqlRestrictionOnLang('carrier_lang') . " AND carrier_lang." . $db->quoteName('lang_id') . " = " ;
            $query .= (int)$lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_carrier_tax_rules_group_shop') . " AS carrier_tax_rules_group_shop ON (carrier.";
            $query .= $db->quoteName('carrier_id') . " = carrier_tax_rules_group_shop." . $db->quoteName('carrier_id'). " AND carrier_tax_rules_group_shop.";
            $query .= $db->quoteName('shop_id') . " = " . (int)$context->shop->shop_id . ") ORDER BY ";
            $query .= ((str_replace('`', '', $orderBy) == 'carrier_id') ? "carrier." : "") . $orderBy . " " . $orderWay ;
            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($useLimit === true) ? " LIMIT " .(int)$limitStart . ", " .(int)$limit : "");

            $db->setQuery($query);
            $carriers = $db->loadObjectList();

            if($useLimit == true){
                $limitStart = (int)$limitStart -(int)$limit;
                if($limitStart < 0){ break; }
            }else{ break; }
        }while(empty($carriers));

        foreach ($carriers as $key => $carrier) {
            if($carrier->name == '0'){
                $carrier->name = JeproshopContext::getContext()->shop->shop_name;
            }
        }

        $this->pagination = new JPagination($total, $limitStart, $limit);
        return $carriers;
    }

    public function getTaxRulesGroupId(JeproshopContext $context = null){
        return JeproshopCarrierModelCarrier::getTaxRulesGroupIdByCarrierId((int)$this->carrier_id, $context);
    }

    public static function getTaxRulesGroupIdByCarrierId($carrierId, JeproshopContext $context = null){
        if (!$context){ $context = JeproshopContext::getContext(); }
        $key = 'jeproshop_carrier_tax_rules_group_id'.(int)$carrierId . '_' . (int)$context->shop->shop_id;
        if (!JeproshopCache::isStored($key)){
            $db = JFactory::getDBO();
            $query = "SELECT " . $db->quoteName('tax_rules_group_id') . " FROM " . $db->quoteName('#__jeproshop_carrier_tax_rules_group_shop') . " WHERE "; ;
            $query .= $db->quoteName('carrier_id') . " = " .(int)$carrierId . " AND shop_id = " .(int)JeproshopContext::getContext()->shop->shop_id;

            $db->setQuery($query);
            JeproshopCache::store($key, $db->loadObject()->tax_rules_group_id);
        }
        return JeproshopCache::retrieve($key);
    }

}