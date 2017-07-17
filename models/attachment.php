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

class JeproshopAttachmentModelAttachment extends JeproshopModel {
    public $attachment_id;
    public $file;
    public $file_name;
    public $file_size;
    public $name;
    public $mime;
    public $description;

    /** @var integer position */
    public $position;

    public static function getAttachments($lang_id, $product_id, $include = true){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_attachment') . " AS attachment LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attachment_lang') . " AS attachment_lang ON (attachment.";
        $query .= "attachment_id = attachment_lang.attachment_id AND attachment_lang.lang_id = " . (int)$lang_id;
        $query .= ") WHERE attachment.attachment_id " . ($include ? "IN" : "NOT IN") . " ( SELECT product_attachment.";
        $query .= "attachment_id FROM " . $db->quoteName('#__jeproshop_product_attachment') . " AS product_attachment";
        $query .= " WHERE product_id = " .(int)$product_id . ")";

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}