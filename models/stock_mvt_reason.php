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


class JeproshopStockMovementReasonModelStockMovementReason extends JeproshopModel{
    /**
     * Gets Stock Mvt Reasons
     *
     * @param int $lang_id
     * @param int $sign Optional
     * @return array
     */
    public static function getStockMovementReasons($lang_id = null, $sign = null){
        if($lang_id == null){ $lang_id = JeproshopContext::getContext()->language->lang_id; }
        $db = JFactory::getDBO();
        $query = "SELECT stock_mvt_reason_lang.name, stock_mvt_reason.stock_mvt_reason_id, stock_mvt_reason.sign FROM " ;
        $query .= $db->quoteName('#__jeproshop_stock_mvt_reason') . " AS stock_mvt_reason LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_stock_mvt_reason_lang') . " AS stock_mvt_reason_lang ON(stock_mvt_reason.";
        $query .= "stock_mvt_reason_id = stock_mvt_reason_lang.stock_mvt_reason_id AND stock_mvt_reason_lang.lang_id = ";
        $query .= (int)$lang_id . ") WHERE stock_mvt_reason.deleted = 0 ";

        if ($sign != null){
            $query .= " AND stock_mvt_reason.sign = " .(int)$sign;
        }
        $db->setquery($query);
        return $db->loadObjectList();
    }
}