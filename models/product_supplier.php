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

class JeproshopProductSupplierModelProductSupplier extends JeproshopModel{
    public $product_supplier_id;
    /**
     * @var integer product ID
     * */
    public $product_id;

    /**
     * @var integer product attribute ID
     * */
    public $product_attribute_id;

    /**
     * @var integer the supplier ID
     * */
    public $supplier_id;

    /**
     * @var string The supplier reference of the product
     * */
    public $product_supplier_reference;

    /**
     * @var integer the currency ID for unit price tax excluded
     * */
    public $currency_id;

    /**
     * @var string The unit price tax excluded of the product
     * */
    public $product_supplier_price_te;

    /**
     * For a given product, retrieves its suppliers
     *
     * @param $productId
     * @param bool $groupBySupplier
     * @return Array Collection of Product Supplier
     *
     * @internal param int $id_product
     */
    public static function getSuppliers($productId, $groupBySupplier = true){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product_supplier') . " WHERE " . $db->quoteName('product_id');
        $query .= " = " . (int)$productId . ($groupBySupplier ? " GROUP BY " . $db->quoteName('supplier_id') : "");

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}