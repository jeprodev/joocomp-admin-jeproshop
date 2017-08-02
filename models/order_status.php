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

class JeproshopOrderStatusModelOrderStatus extends JeproshopModel {
    public $order_id;

    public $order_status_id;

    /** @var string Name */
    public $name;

    /** @var string Template name if there is any e-mail to send */
    public $template;

    /** @var boolean Send an e-mail to customer ? */
    public $send_email;

    public $module_name;

    /** @var boolean Allow customer to view and download invoice when order is at this state */
    public $invoice;

    /** @var string Display state in the specified color */
    public $color;

    public $unremovable;

    /** @var boolean Log authorization */
    public $logable;

    /** @var boolean Delivery */
    public $delivery;

    /** @var boolean Hidden */
    public $hidden;

    /** @var boolean Shipped */
    public $shipped;

    /** @var boolean Paid */
    public $paid;

    /** @var boolean True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;

    const FLAG_NO_HIDDEN	= 1;  /* 00001 */
    const FLAG_LOGABLE		= 2;  /* 00010 */
    const FLAG_DELIVERY		= 4;  /* 00100 */
    const FLAG_SHIPPED		= 8;  /* 01000 */
    const FLAG_PAID			= 16; /* 10000 */

    public function __construct($orderStatusId = null)
    {
    }

    /**
     * Get all available order statuses
     *
     * @param integer $lang_id Language id for status name
     * @return array Order statues
     */
    public static function getOrderStatus($lang_id){
        $cacheKey = 'jeproshop_order_status_get_order_status_'.(int)$lang_id;
        if (!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_status') . " AS order_status LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_order_status_lang') . " AS order_status_lang ON (order_status.";
            $query .= $db->quoteName('order_status_id') . " = order_status_lang." . $db->quoteName('order_status_id');
            $query .= " AND order_status_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") WHERE deleted = 0 ";
            $query .= " ORDER BY " . $db->quoteName('name') . " ASC";

            $db->setQuery($query);
            $result = $db->loadObjectList();

            JeproshopCache::store($cacheKey, $result);
        }
        return JeproshopCache::retrieve($cacheKey);
    }

}