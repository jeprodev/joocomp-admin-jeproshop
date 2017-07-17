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

class JeproshopTagModelTag extends JeproshopModel {
    public $tag_id;
    /** @var integer Language id */
    public $lang_id;

    /** @var string Name */
    public $name;

    public static function getProductTags($product_id){
        $db = JFactory::getDBO();

        $query = "SELECT tag." . $db->quoteName('lang_id') . ", tag." . $db->quoteName('name') . " FROM ";
        $query .= $db->quoteName('#__jeproshop_tag') . " AS tag LEFT JOIN " . $db->quoteName('#__jeproshop_product_tag');
        $query .= " AS product_tag ON (product_tag.tag_id = tag.tag_id ) WHERE product_tag.";
        $query .= $db->quoteName('product_id') . " = " . (int)$product_id;

        $db->setQuery($query);
        $tags = $db->loadObjectList();
        if(!$tags)
            return false;

        return $tags;
    }
}