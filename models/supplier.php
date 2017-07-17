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

class JeproshopSupplierModelSupplier extends JeproshopModel {
    /** @var integer supplier ID */
    public $supplier_id;

    /** @var string Name */
    public $name;

    /** @var string A short description for the discount */
    public $description;

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

    /**
     * Return suppliers
     *
     * @param bool $get_nb_products
     * @param int $langId
     * @param bool $published
     * @param bool $p
     * @param bool $n
     * @param bool $allGroups
     * @return array Suppliers
     */
    public static function getSuppliers($get_nb_products = false, $langId = 0, $published = true, $p = false, $n = false, $allGroups = false){
        if (!$langId){ $langId = JeproshopSettingModelSetting::getValue('default_lang'); }
        if (!JeproshopGroupModelGroup::isFeaturePublished()){ $allGroups = true; }

        $db = JFactory::getDBO();

        $query = "SELECT supplier.*, supplier_lang." . $db->quoteName('description') . " FROM " . $db->quoteName('#__jeproshop_supplier');
        $query .= " AS supplier LEFT JOIN " . $db->quoteName('#__jeproshop_supplier_lang') . " AS supplier_lang ON(supplier.";
        $query .= $db->quoteName('supplier_id') . " = supplier_lang." . $db->quoteName('supplier_id') . " AND supplier_lang.";
        $query .= $db->quoteName('lang_id') . " = " . (int)$langId . JeproshopShopModelShop::addSqlAssociation('supplier') . ")";
        $query .= ($published ? " WHERE supplier." . $db->quoteName('published') . " = 1" : "") . " ORDER BY supplier.";
        $query .= $db->quoteName('name') . " ASC " . (($p && $n) ? " LIMIT " . $n . ", " . ($p - 1)*$n : "") ; //. " GROUP BY supplier" . $db->quoteName('supplier_id');

        $db->setQuery($query);
        $suppliers = $db->loadObjectList();

        if ($suppliers === false){ return false; }
        if ($get_nb_products){
            $sqlGroups = '';
            if (!$allGroups)
            {
                $groups = FrontController::getCurrentCustomerGroups();
                $sqlGroups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
            }

            foreach ($suppliers as $key => $supplier){
                $sql = '
					SELECT DISTINCT(ps.`id_product`)
					FROM `'._DB_PREFIX_.'product_supplier` ps
					JOIN `'._DB_PREFIX_.'product` p ON (ps.`id_product`= p.`id_product`)
					'.Shop::addSqlAssociation('product', 'p').'
					WHERE ps.`id_supplier` = '.(int)$supplier['id_supplier'].'
					AND ps.id_product_attribute = 0'.
                    ($active ? ' AND product_shop.`active` = 1' : '').
                    ' AND product_shop.`visibility` NOT IN ("none")'.
                    ($all_groups ? '' :'
					AND ps.`id_product` IN (
						SELECT cp.`id_product`
						FROM `'._DB_PREFIX_.'category_group` cg
						LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
						WHERE cg.`id_group` '.$sql_groups.'
					)');
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                $suppliers[$key]['nb_products'] = count($result);
            }
        }

        $nb_suppliers = count($suppliers);
        $rewrite_settings = (int)JeproshopSettingModelSetting::getValue('rewrite_settings');
        for ($i = 0; $i < $nb_suppliers; $i++){
            $suppliers[$i]->link_rewrite = ($rewrite_settings ? JeproshopValidator::link_rewrite($suppliers[$i]->name) : 0);
        }
        return $suppliers;
    }

}