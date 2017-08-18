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

class JeproshopCustomizationModelCustomization extends JeproshopModel
{
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('customization_feature_active');
    }

    /**
     * This method is allow to know if a Customization entity is currently used
     *
     * @return bool
     */
    public static function isCurrentlyUsed(){
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('customization_field_id') . " FROM " . $db->quoteName('#__jeproshop_customization_field');

        $db->setQuery($query);
        $data = $db->loadObject();
        return (isset($data) ? (bool)$data->customization_field_id : false);
    }

}