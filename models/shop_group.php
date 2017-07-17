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
}