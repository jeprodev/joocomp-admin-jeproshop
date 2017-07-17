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

class JeproshopAttributeModelAttribute extends JeproshopModel {
    /** @var integer Group id which attribute belongs */
    public $attribute_id;
    public $attribute_group_id;
    public $product_attribute_id;
    public $product_id;
    public $attribute_designation;

    public $shop_id;

    /** @var string Name */
    public $name;
    public $color;
    public $position;
    public $default;

    protected $multiLang = true;
    protected $multiLangShop = true;

    protected $shop_list_id;
    protected $image_dir = COM_JEPROSHOP_COLOR_IMAGE_DIR;

    /**
     * Get all attributes for a given language
     *
     * @param integer $langId Language id
     * @param boolean $not_null Get only not null fields if true
     * @return array Attributes
     */
    public static function getAttributes($langId, $notNull = false){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return array(); }

        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT attribute_group.*, attribute_group_lang.*, attribute." . $db->quoteName('attribute_id') . ", attribute_lang." . $db->quoteName('name') . ", attribute_group_lang." . $db->quoteName('name'). " AS ";
        $query .= $db->quoteName('attribute_group_name') . " FROM " . $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS ";
        $query .= "attribute_group_lang ON (attribute_group." . $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') . " AND attribute_group_lang." . $db->quoteName('lang_id') . " = ";
        $query .= (int)$langId . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute') . " AS attribute ON (attribute." . $db->quoteName('attribute_group_id') . " = attribute_group." . $db->quoteName('attribute_group_id');
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id') . " AND ";
        $query .= "attribute_lang." . $db->quoteName('lang_id') . " = " . (int)$langId . ") " . JeproshopShopModelShop::addSqlAssociation('attribute_group') ;
        $notNullQuery =  ($notNull ? " WHERE attribute." . $db->quoteName('attribute_id') . " IS NOT NULL AND attribute_lang." . $db->quoteName('name') . " IS NOT NULL AND attribute_group_lang." . $db->quoteName('attribute_group_id') . " IS NOT NULL" : "");
        $query .= JeproshopShopModelShop::addSqlAssociation('attribute') . $notNullQuery  . " ORDER BY attribute_group_lang." . $db->quoteName('name') .  " ASC, attribute." . $db->quoteName('position') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

}


class JeproshopAttributeGroupModelAttributeGroup extends JeproshopModel{
    public $attribute_group_id;
    /** @var string Name */
    public $name;
    public $is_color_group;
    public $position;
    public $group_type;

    public $shop_id;
    public $lang_id;

    protected  $multiLang = true;
    protected  $multiLangShop = true;

    /** @var string Public Name */
    public $public_name;

    /**
     * Get all attributes groups for a given language
     *
     * @param integer $langId Language id
     * @return array Attributes groups
     */
    public static function getAttributesGroups($langId){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){
            return array();
        }

        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT attribute_group_lang." . $db->quoteName('name') . ", attribute_group.*, ";
        $query .= "attribute_group_lang.* FROM " . $db->quoteName('#__jeproshop_attribute_group') . " AS ";
        $query .= "attribute_group " . JeproshopShopModelShop::addSqlAssociation('attribute_group'). " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON (attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id');
        $query .= " AND " . $db->quoteName('lang_id') . " = " .(int)$langId . ") ORDER BY " . $db->quoteName('name') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}