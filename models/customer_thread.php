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

class JeproshopCustomerThreadModelCustomerThread extends JeproshopModel {
    public $customer_thread_id;
    public $shop_id;
    public $lang_id;
    public $contact_id;
    public $customer_id;
    public $order_id;
    public $product_id;
    public $status;
    public $email;
    public $token;
    public $date_add;
    public $date_upd;



    public static function getCustomerMessages($customer_id, $read = null){
        $db = JFactory::getDBO();
        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_customer_thread'). " AS customer_thread LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_customer_message') . " AS customer_message ON (customer_thread.customer_thread_id";
        $query .= " = customer_message.customer_thread_id ) WHERE customer_id = " . (int)$customer_id;

        if (!is_null($read)){
            $query .= " AND customer_message." . $db->quoteName('read') . " = " . (int)$read;
        }
        $db->setQuery($query);
        return $db->loadObjectList();
    }
}