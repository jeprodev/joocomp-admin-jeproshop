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

class JeproshopZoneModelZone extends JeproshopModel {
    public $zone_id;

    /** @var string Name */
    public $name;

    public $allow_delivery;

    public function __construct($zoneId = null){
        $db = JFactory::getDBO();

        if($zoneId){
            $cacheKey =  'jeproshop_zone_model_' . (int)$zoneId;
            if(!JeproshopCache::isStored($cacheKey)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_zone') . " AS zone WHERE " . $db->quoteName('zone_id') . " = " . (int)$zoneId;
                $db->setQuery($query);
                $zoneData = $db->loadObject();
                JeproshopCache::store($cacheKey, $zoneData);
            }else{
                $zoneData = JeproshopCache::retrieve($cacheKey);
            }

            if($zoneData){
                $zoneData->zone_id = (int)$zoneId;
                foreach($zoneData as $key => $value){
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * Get all available geographical zones
     *
     * @param bool|type $allowDelivery
     * @return type
     */
    public static function getZones($allowDelivery = FALSE){
        $cacheKey = 'jeproshop_zone_model_get_zones_' . (bool)$allowDelivery;
        if(!JeproshopCache::isStored($cacheKey)) {
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_zone') . ($allowDelivery ? " WHERE allow_delivery = 1 " : "");
            $query .= " ORDER BY " . $db->quoteName('name') . " ASC ";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cacheKey, $result);
        }
        return JeproshopCache::retrieve($cacheKey);
    }

    public function getZoneList(JeproshopContext $context = NULL){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!$context){ $context = JeproshopContext::getContext(); }

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $lang_id = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $shop_id = $app->getUserStateFromRequest($option. $view. '.shop_id', 'shop_id', $context->shop->shop_id, 'int');
        $shop_group_id = $app->getUserStateFromRequest($option. $view. '.shop_group_id', 'shop_group_id', $context->shop->shop_group_id, 'int');
        $allow_delivery = $app->getUserStateFromRequest($option. $view. '.allow_delivery', 'allow_delivery', 0, 'int');

        $useLimit = true;
        if ($limit === false)
            $useLimit = false;

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS zone." .  $db->quoteName('zone_id') . ", zone." .  $db->quoteName('name');
            $query .= " AS zone_name, zone." .  $db->quoteName('allow_delivery') . " FROM " . $db->quoteName('#__jeproshop_zone');
            $query .= ($allow_delivery ? " WHERE zone.allow_delivery = 1 " : "");
            $query .= " AS zone ORDER BY " . $db->quoteName('name') . " ASC ";

            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($useLimit === true) ? " LIMIT " .(int)$limitStart . ", " .(int)$limit : "");

            $db->setQuery($query);
            $zones = $db->loadObjectList();
            if($useLimit == true){

                $limitStart = (int)$limitStart -(int)$limit;
                if($limitStart < 0){ break; }
            }else{ break; }
        }while(empty($zones));

        $this->pagination = new JPagination($total, $limitStart, $limit);
        return $zones;
    }

}