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

class JeproshopShopGroupModelShopGroup extends JeproshopModel{
    public $name;
    public $shop_group_id;
    public $published = true;
    public $share_customer;
    public $share_stock;
    public $share_order;
    public $deleted;

    public function __construct($shopGroupId = NULL) {
        if($shopGroupId) {
            /** Load object from database if shop group id is present **/
            $cacheKey = 'jeproshop_shop_group_model_' . $shopGroupId;
            if (!JeproshopCache::isStored($cacheKey)) {
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_shop_group') . " AS shop_group ";
                $query .= " WHERE shop_group." . $db->quoteName('shop_group_id') . " = " . (int)$shopGroupId;

                $db->setQuery($query);
                $shopGroupData = $db->loadObject();
                if ($shopGroupData) {
                    JeproshopCache::store($cacheKey, $shopGroupData);
                }
            } else {
                $shopGroupData = JeproshopCache::retrieve($cacheKey);
            }

            if ($shopGroupData) {
                $shopGroupData->shop_group_id = $shopGroupId;
                foreach ($shopGroupData as $key => $value) {
                    if (array_key_exists($key, $this)) {
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    public static function getShopGroups($published = TRUE){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_shop_group') . " WHERE 1 ";
        if($published){
            $query .= " AND " . $db->quoteName('published') . " = " . $published;
        }

        $db->setQuery($query);
        $groups = $db->loadObjectList();
        return $groups;
    }

    public function getShopGroupList($published = false){
        $db = JFactory::getDBO();

        $query = "SELECT shop_group.* FROM " . $db->quoteName('#__jeproshop_shop_group') . " AS shop_group WHERE 1 ";
        if($published){
            $query .= " AND " . $db->quoteName('published') . " = " . $published;
        }

        $db->setQuery($query);
        $groups = $db->loadObjectList();
        return $groups;
    }

    public function add(){
        $data = JRequest::get('post');
        $shopData = $data['jform'];

        $db = JFactory::getDBO();
        $this->name = (isset($shopData['name']) ? $shopData['name'] : '');
        $this->share_customer = (int)(isset($shopData['share_customer']) ? ($shopData['share_customer'] == 1 ? 1 : 0) : 0);
        $this->share_stock = (int)(isset($shopData['share_stock']) ? ($shopData['share_stock'] == 1 ? 1 : 0) : '');
        $this->share_order = (int)(isset($shopData['share_orders']) ? ($shopData['share_orders'] == 1 ? 1 : 0 ): '');
        $this->published = (int)(isset($shopData['published']) ? ($shopData['published'] == 1 ? 1 : 0) : '');
        $this->deleted = (int)(isset($shopData['deleted']) ? ($shopData['deleted'] == 1 ? 1 : 0) : '');

        if($this->published == 1){ $this->deleted = 0; }

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_shop_group') . " (" . $db->quoteName('name') . ", " ;
        $query .= $db->quoteName('share_customer') . ", " . $db->quoteName('share_order'). ", " . $db->quoteName('share_stock');
        $query .= ", " . $db->quoteName('published') . ", " .  $db->quoteName('deleted') . ") VALUES (" . $db->quote($this->name);
        $query .= ", " . $this->share_customer . ", " . $this->share_order . ", " . $this->share_stock  . " = " . $this->published;
        $query .= ", " . $this->deleted ."  WHERE " . $db->quoteName('shop_group_id') . " = " . $this->shop_group_id;

        $db->setQuery($query);
        if($db->query()){
            JeproshopStockAvailableModelStockAvailable::resetProductFromStockAvailableByShopGroup($this);
            return true;
        }
        return false;
    }
    
    public function update(){
        $data = JRequest::get('post');
        $shopData = $data['jform'];

        $db = JFactory::getDBO();
        $this->name = (isset($shopData['name']) ? $shopData['name'] : '');
        $this->share_customer = (int)(isset($shopData['share_customer']) ? ($shopData['share_customer'] == 1 ? 1 : 0) : 0);
        $this->share_stock = (int)(isset($shopData['share_stock']) ? ($shopData['share_stock'] == 1 ? 1 : 0) : '');
        $this->share_order = (int)(isset($shopData['share_orders']) ? ($shopData['share_orders'] == 1 ? 1 : 0 ): '');
        $this->published = (int)(isset($shopData['published']) ? ($shopData['published'] == 1 ? 1 : 0) : '');
        $this->deleted = (int)(isset($shopData['deleted']) ? ($shopData['deleted'] == 1 ? 1 : 0) : '');

        if($this->published == 1){ $this->deleted = 0; }

        $query = "UPDATE " . $db->quoteName('#__jeproshop_shop_group') . " SET " . $db->quoteName('name') . " = ";
        $query .= $db->quote($this->name) . ", " .  $db->quoteName('share_customer') . " = " . $this->share_customer . ", ";
        $query .= $db->quoteName('share_order')  . " = " . $this->share_order . ", " . $db->quoteName('share_stock');
        $query .= " = " . $this->share_stock . ", " . $db->quoteName('published') . " = " . $this->published . ", " ;
        $query .= $db->quoteName('deleted') . " =  " .$this->deleted . " WHERE " . $db->quoteName('shop_group_id');
        $query .= " = " . $this->shop_group_id;

        $db->setQuery($query);
        if($db->query()){
            JeproshopStockAvailableModelStockAvailable::resetProductFromStockAvailableByShopGroup($this);
            return true;
        }
        return false;
    }

    /**
     * @param bool $published
     * @return int Total of shop groups
     */
    public static function getTotalShopGroup($published = true){
        return count(JeproshopShopGroupModelShopGroup::getShopGroups($published));
    }

    public function haveShops(){
        return (bool)$this->getTotalShops();
    }

    public function getTotalShops(){
        $db = JFactory::getDBO();
        $query = "SELECT COUNT(*) AS total	FROM " . $db->quoteName('#__jeproshop_shop') . " WHERE " ;
        $query .= $db->quoteName('shop_group_id') . " = " . (int)$this->shop_group_id;

        $db->setQuery($query);
        $data = $db->loadObject();
        return (int)(isset($data) ? $data->total : 0);
    }
}