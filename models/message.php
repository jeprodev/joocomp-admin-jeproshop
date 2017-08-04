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

class JeproshopMessageModelMessage extends JeproshopModel
{
    public $message_id;

    /** @var string message content */
    public $message;

    /** @var integer Cart ID (if applicable) */
    public $cart_id;

    /** @var integer Order ID (if applicable) */
    public $order_id;

    /** @var integer Customer ID (if applicable) */
    public $customer_id;

    /** @var integer Employee ID (if applicable) */
    public $employee_id;

    /** @var boolean Message is not displayed to the customer */
    public $private;

    /** @var string Object creation date */
    public $date_add;

    /**
     * Return messages from Order ID
     *
     * @param integer $order_id Order ID
     * @param boolean $private return WITH private messages
     * @param JeproshopContext $context
     * @return array Messages
     */
    public static function getMessagesByOrderId($order_id, $private = false, JeproshopContext $context = null){
        if (!JeproshopTools::isBool($private))
            die(JError::raiseError());

        if (!$context){ $context = JeproshopContext::getContext(); }

        $db = JFactory::getDBO();

        $query = "SELECT message.*, customer." . $db->quoteName('firstname') . " AS customer_firstname, customer.";
        $query .= $db->quoteName('lastname') . " AS customer_lastname, employee." . $db->quoteName('name') . " AS employee_name, ";
        $query .= "employee." . $db->quoteName('username') . " AS employee_user_name, (COUNT(message_readed.message_id) = 0 AND ";
        $query .= "message.customer_id != 0) AS is_new_for_me FROM " . $db->quoteName('#__jeproshop_message') . " AS message LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_customer') . " AS customer ON message." . $db->quoteName('customer_id') . " = ";
        $query .= "customer." . $db->quoteName('customer_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_message_readed');
        $query .= " AS message_readed ON message_readed." . $db->quoteName('message_id') . " = message." . $db->quoteName('message_id');
        $query .= " AND message_readed." . $db->quoteName('employee_id') . " = " .(isset($context->employee) ? (int)$context->employee->employee_id : "");
        $query .= " LEFT OUTER JOIN " . $db->quoteName('#__users') . " AS employee ON employee." . $db->quoteName('id');
        $query .= " = message." . $db->quoteName('employee_id') . "	WHERE order_id = " . (int)$order_id;
        $query .= (!$private ? " AND message." . $db->quoteName('private') . " = 0" : "") . " GROUP BY message.message_id";
        $query .= " ORDER BY message.date_add DESC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Return the last message from cart
     *
     * @param int $cartId Cart ID
     * @return array Message
     */
    public static function getMessageByCartId($cartId){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_message') . " WHERE " . $db->quoteName('cart_id')  . " = " .(int)$cartId;

        $db->setQuery($query);
        return $db->loadObject();
    }

    /**
     * Return messages from Cart ID
     *
     * @param int $cartId ID
     * @param bool $private return WITH private messages
     * @param JeproshopContext $context
     * @return array Messages
     */
    public static function getMessagesByCartId($cartId, $private = false, JeproshopContext $context = null)
    {
        if (!JeproshopTools::isBool($private)) {
            JeproshopTools::displayError(500, "");
            die();
        }

        if (!$context) {
            $context = JeproshopContext::getContext();
        }

        $db = JFactory::getDBO();

        $query = "SELECT message.*, customer." . $db->quoteName('firstname') . " AS first_name, customer." . $db->quoteName('lastname');
        $query .= " AS last_name, employee." . $db->quoteName('username') . " AS firstname, employee." . $db->quoteName('name');
        $query .= " AS lastname, (COUNT(read_message." . $db->quoteName('message_id') . ") = 0 AND message."  . $db->quoteName('customer_id');
        $query .= " != 0) AS is_new_for_me FROM " . $db->quoteName('#__jeproshop_message') . " AS message LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_customer') . " AS customer ON message." . $db->quoteName('customer_id') . " = customer.";
        $query .= $db->quoteName('customer_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_message_readed') . " AS read_message ON (";
        $query .= " read_message." . $db->quoteName('message_id') . " = message." . $db->quoteName('message_id') . " AND read_message." ;
        $query .= $db->quoteName('employee_id') . " = " . (int)$context->employee->employee_id . ") LEFT OUTER JOIN  ";
        $query .= $db->quoteName('#__users') . " AS employee ON (employee." . $db->quoteName('id') . " = message.";
        $query .= $db->quoteName('employee_id') . ") WHERE " . $db->quoteName('cart_id') . " = " . (int)$cartId ;
        $query .= (!$private ? " AND message." . $db->quoteName('private') . " = 0 "  : "") . "	GROUP BY message." . $db->quoteName('message_id');
        $query .= "	ORDER BY message." . $db->quoteName('date_add') . " DESC ";
//echo $query; exit();
        $db->setQuery($query);

        return $db->loadObjectList();
    }
}