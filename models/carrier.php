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
            $query .= " AND carrier_zone." . $db->quoteName('zone_id') . " = " . (int)$zone_id . " AND zone." . $db->quoteName('published') . " = 1 ";
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

}