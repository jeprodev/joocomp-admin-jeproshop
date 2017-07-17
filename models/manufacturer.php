<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
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

class JeproshopManufacturerModelManufacturer extends JeproshopModel {
    /** @var integer manufacturer ID //FIXME is it really usefull...? */
    public $manufacturer_id;

    /** @var string Name */
    public $name;

    /** @var string A description */
    public $description;
    public $short_description;

    /** @var int Address */
    public $address_id;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var string Friendly URL */
    public $link_rewrite;

    /** @var string Meta title */
    public $meta_title;

    /** @var string Meta keywords */
    public $meta_keywords;

    /** @var string Meta description */
    public $meta_description;

    /** @var boolean active */
    public $published;

    protected static $_cache_name = array();


    /**
     * Return manufacturers
     *
     * @param boolean $getNumberOfProducts [optional] return products numbers for each
     * @param int $langId
     * @param bool $published
     * @param int $p
     * @param int $n
     * @param bool $all_group
     * @return array Manufacturers
     */
    public static function getManufacturers($getNumberOfProducts = false, $langId = 0, $published = true, $p = false, $n = false, $allGroup = false, $groupBy = false){
        if (!$langId){
            $langId = (int)JeproshopSettingModelSetting::getValue('default_lang');
        }
        if (!JeproshopGroupModelGroup::isFeaturePublished()){ $allGroup = true; }

        $db = JFactory::getDBO();

        $query = "SELECT manufacturer.*, manufacturer_lang."  . $db->quoteName('description') . ", manufacturer_lang.";
        $query .= $db->quoteName('short_description') . " FROM " . $db->quoteName('#__jeproshop_manufacturer') . " AS ";
        $query .= "manufacturer " . JeproshopShopModelShop::addSqlAssociation('manufacturer') . " INNER JOIN ";
        $query .= $db->quoteName('#__jeproshop_manufacturer_lang') . " AS manufacturer_lang ON (manufacturer.";
        $query .= $db->quoteName('manufacturer_id') . " = manufacturer_lang." . $db->quoteName('manufacturer_id') ;
        $query .= " AND manufacturer_lang." . $db->quoteName('lang_id') . " = " . (int)$langId . ")";
        $query .= ($published  ? " WHERE manufacturer." . $db->quoteName('published') . " = 1" : "");
        $query .= ($groupBy ? " GROUP BY manufacturer." . $db->quoteName('manufacturer_id') : "" ) . " ORDER BY ";
        $query .= "manufacturer." . $db->quoteName('name') . " ASC " . ($p ? " LIMIT ".(((int)$p - 1) * (int)$n).", ".(int)$n : "");

        $db->setQuery($query);
        $manufacturers = $db->loadObjectList();
        if ($manufacturers === false)
            return false;
        /*
            if ($get_nb_products)
            {
                $sql_groups = '';
                if (!$all_group)
                {
                    $groups = FrontController::getCurrentCustomerGroups();
                    $sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
                }

                foreach ($manufacturers as $key => $manufacturer)
                {
                    $manufacturers[$key]['nb_products'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                    SELECT COUNT(DISTINCT p.`id_product`)
                    FROM `'._DB_PREFIX_.'product` p
                    '.Shop::addSqlAssociation('product', 'p').'
                    WHERE p.`id_manufacturer` = '.(int)$manufacturer['id_manufacturer'].'
                    AND product_shop.`visibility` NOT IN ("none")
                    '.($active ? ' AND product_shop.`active` = 1 ' : '').'
                    '.($all_group ? '' : ' AND p.`id_product` IN (
                        SELECT cp.`id_product`
                        FROM `'._DB_PREFIX_.'category_group` cg
                        LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
                        WHERE cg.`id_group` '.$sql_groups.'
                    )'));
                }
            }
            */
        $totalManufacturers = count($manufacturers);
        $rewrite_settings = (int)JeproshopSettingModelSetting::getValue('rewrite_settings');
        for ($i = 0; $i < $totalManufacturers; $i++)
            $manufacturers[$i]->link_rewrite = ($rewrite_settings ? JeproshopTools::link_rewrite($manufacturers[$i]->name) : 0);
        return $manufacturers;
    }

    /**
     * Return name from id
     *
     * @param int $manufacturer_id
     * @return string name
     */
    public static function getNameById($manufacturer_id){
        if(!isset(self::$_cache_name[$manufacturer_id])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_manufacturer') . " WHERE " ;
            $query .= $db->quoteName('manufacturer_id') . " = " . (int)$manufacturer_id . " AND " . $db->quoteName('published') . " = 1";

            $db->setQuery($query);
            self::$_cache_name[$manufacturer_id] = $db->loadResult();
        }
        return self::$_cache_name[$manufacturer_id];
    }

}