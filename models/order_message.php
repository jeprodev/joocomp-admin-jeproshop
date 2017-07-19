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

class JeproshopOrderMessageModelOrderMessage extends JeproshopModel {
    public $order_message_id;
    
    /** @var string name name */
    public $name;

    /** @var string message content */
    public $message;

    /** @var string Object creation date */
    public $date_add;

    public static function getOrderMessages($lang_id){
        $db = JFactory::getDBO();

        $query = "SELECT order_message.order_message_id, order_message_lang.name, order_message_lang.message FROM ";
        $query .= $db->quoteName('#__jeproshop_order_message') . " AS order_message LEFT JOIN " . $db->quoteName('#__jeproshop_order_message_lang');
        $query .= " AS order_message_lang ON (order_message_lang.order_message_id = order_message.order_message_id )";
        $query .= " WHERE order_message_lang.lang_id = " . (int)$lang_id . " ORDER BY name ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}