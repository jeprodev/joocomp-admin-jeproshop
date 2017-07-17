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

    public $name;

    public $reduction;

    public $show_prices = 1;

    public $price_display_method;

    public $date_add;
    public $date_upd;

    protected static $cache_reduction = array();
    protected static $group_price_display_method;

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
}
