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

class JeproshopOrderReturnModelOrderReturn extends JeproshopModel {
    /** @var integer */
    public $order_return_id;

    /** @var integer */
    public $customer_id;

    /** @var integer */
    public $order_id;

    /** @var integer */
    public $state_id;

    /** @var string message content */
    public $question;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;


    public static function getOrdersReturn($customer_id, $order_id = false, $no_denied = false, JeproshopContext $context = null){
        if (!$context){	$context = JeproshopContext::getContext(); }

        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_return') . " WHERE " . $db->quoteName('customer_id');
        $query .= " = " . (int)($customer_id) . ($order_id ? " AND " .$db->quoteName('order_id') . " = ".(int)($order_id) : "");
        $query .= ($no_denied ? " AND " . $db->quoteName('state') . " != 4" : ""). " ORDER BY " . $db->quoteName('date_add') . " DESC ";

        $db->setQuery($query);
        $data = $db->loadObjectList();

        foreach ($data as $k => $or){
            $state = new JeproshopOrderReturnStateModelOrderReturnState($or->state);
            $data[$k]->state_name = $state->name[$context->language->lang_id];
            $data[$k]->type = 'Return';
            $data[$k]->tracking_number = $or->order_return_id;
            $data[$k]->can_edit = false;
            $data[$k]->reference = JeproshopOrderModelOrder::getUniqReferenceOf($or->order_id);
        }
        return $data;
    }
}


class JeproshopOrderReturnStateModelOrderReturnState extends JeproshopModel{

}