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

class JeproshopShopUrlModelShopUrl extends JeproshopModel {
    public $shop_url_id;

    public $shop_id;

    public $domain;

    public $ssl_domain;

    public $main;

    public $published;

    protected static $main_domain = array();
    protected static $main_ssl_domain = array();

    public static function getMainShopDomain($shopId = null)
    {
        JeproshopShopUrlModelShopUrl::cacheMainDomainForShop($shopId);
        return self::$main_domain[(int)$shopId];
    }

    public static function getMainShopSslDomain($shopId = null){
        JeproshopShopUrlModelShopUrl::cacheMainDomainForShop($shopId);
        return self::$main_ssl_domain[(int)$shopId];
    }

    public static function cacheMainDomainForShop($shopId){
        if (!isset(self::$main_ssl_domain[(int)$shopId]) || !isset(self::$main_domain[(int)$shopId])) {
            $db = JFactory::getDBO();
            $query ="SELECT " . $db->quoteName('domain') . ", " . $db->quoteName('ssl_domain') . " FROM " . $db->quoteName('#__jeproshop_shop_url');
            $query .= " WHERE "  . $db->quoteName('main') . " = 1 AND " . $db->quoteName('shop_id') . " = " ;
            $query .= ($shopId !== null ? (int)$shopId : (int)JeproshopContext::getContext()->shop->shop_id);

            $db->setQuery($query);
            $row = $db->loadObject();

            self::$main_domain[(int)$shopId] = (isset($row) ? $row->domain : null);
            self::$main_ssl_domain[(int)$shopId] = (isset($row) ? $row->ssl_domain : null);
        }
    }
}