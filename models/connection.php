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

class JeproshopConnectionModelConnection extends JeproshopModel {
    /** @var integer */
    public $guest_id;

    /** @var integer */
    public $page_id;

    /** @var string */
    public $ip_address;

    /** @var string */
    public $http_referrer;

    /** @var int */
    public $shop_id;

    /** @var int */
    public $shop_group_id;

    /** @var string */
    public $date_add;

}

class JeproshopConnectionSourceModelConnectionSource extends JeproshopModel {
    public $connection_source_id;
    public $http_referrer;
    public $request_uri;
    public $keywords;
    public $date_add;
    public static $uri_max_size = 255;

    public static function getOrderSources($orderId){
        $db = JFactory::getDBO();

        $query = "SELECT connection_source.http_referrer, connection_source.request_uri, connection_source.keywords, ";
        $query .= "connection_source.date_add FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord INNER JOIN ";
        $query .= $db->quoteName('#__jeproshop_guest') . " AS guest ON guest.customer_id = ord.customer_id INNER JOIN ";
        $query .= $db->quoteName('#__jeproshop_connection') . " AS connection  ON connection.guest_id = guest.guest_id ";
        $query .= " INNER JOIN " . $db->quoteName('#__jeproshop_connection_source') . " AS connection_source ON connection_source.";
        $query .= $db->quoteName('connection_id') . " = connection.connection_id WHERE order_id = " . (int)($orderId) . " ORDER BY connection_source.date_add DESC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}