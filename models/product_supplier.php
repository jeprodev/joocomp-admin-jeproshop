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
    public $product_supplier_price_tax_excluded;

    /**
     * For a given product, retrieves its suppliers
     *
     * @param $productId
     * @param bool $groupBySupplier
     * @return Array Collection of Product Supplier
     *
     */
    public static function getSuppliers($productId, $groupBySupplier = true){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product_supplier') . " WHERE " . $db->quoteName('product_id');
        $query .= " = " . (int)$productId . ($groupBySupplier ? " GROUP BY " . $db->quoteName('supplier_id') : "");

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function save($fromPost = true){
        if($this->product_supplier_id > 0){
            $this->update($fromPost);
        }else{
            $this->add($fromPost);
        }
    }

    public function add($fromPost = true)
    {
        if ($fromPost) {
            $this->copyFromPost();
        }

        $db = JFactory::getDBO();

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_product_supplier') . "(" . $db->quoteName('product_id') . ", ";
        $query .= $db->quoteName('product_attribute_id') . ", " . $db->quoteName('supplier_id') . ", " . $db->quoteName('currency_id') ;
        $query .= ", " . $db->quoteName('product_supplier_reference') . ", " . $db->quoteName('product_supplier_price_tax_excluded');
        $query .= ") VALUES (" . (int)$this->product_id . ", " . (int)$this->product_attribute_id . ", " . (int)$this->supplier_id;
        $query .= (int)$this->currency_id . ", " . $db->quote($this->product_supplier_reference) . ", " . (float)$this->product_supplier_price_tax_excluded . ")";

        $db->setQuery($query);
        $db->query();
    }

    public function update($fromPost = true){
        if ($fromPost) {
            $this->copyFromPost();
        }

        $db = JFactory::getDBO();

        $query = "UPDATE " . $db->quoteName('#__jeproshop_product_supplier') . " SET " . $db->quoteName('product_id') . " = ";
        $query .= (int)$this->product_id . ", " . $db->quoteName('product_attribute_id') . " = " . (int)$this->product_attribute_id;
        $query .= ", " . $db->quoteName('supplier_id') . " = "  . (int)$this->supplier_id . ", " . $db->quoteName('currency_id') ;
        $query .= " = " . (int)$this->currency_id . ", ". $db->quoteName('product_supplier_reference') . " = " . $db->quote($this->product_supplier_reference);
        $query .= ", " . $db->quoteName('product_supplier_price_tax_excluded') . " = " . (float)$this->product_supplier_price_tax_excluded;
        $query .= " WHERE " . $db->quoteName('product_supplier_id') . " = " . $this->product_supplier_id;

        $db->setQuery($query);
        $db->query();
    }
}