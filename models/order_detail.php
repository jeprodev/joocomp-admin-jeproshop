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

class JeproshopOrderDetailModelOrderDetail extends JeproshopModel {
    /** @var integer */
    public $order_detail_id;

    /** @var integer */
    public $order_id;

    /** @var integer */
    public $order_invoice_id;

    /** @var integer */
    public $product_id;

    /** @var integer */
    public $shop_id;

    /** @var integer */
    public $product_attribute_id;

    /** @var string */
    public $product_name;

    /** @var integer */
    public $product_quantity;

    /** @var integer */
    public $product_quantity_in_stock;

    /** @var integer */
    public $product_quantity_return;

    /** @var integer */
    public $product_quantity_refunded;

    /** @var integer */
    public $product_quantity_reinserted;

    /** @var float */
    public $product_price;

    /** @var float */
    public $original_product_price;

    /** @var float */
    public $unit_price_tax_incl;

    /** @var float */
    public $unit_price_tax_excl;

    /** @var float */
    public $total_price_tax_incl;

    /** @var float */
    public $total_price_tax_excl;

    /** @var float */
    public $reduction_percent;

    /** @var float */
    public $reduction_amount;

    /** @var float */
    public $reduction_amount_tax_excl;

    /** @var float */
    public $reduction_amount_tax_incl;

    /** @var float */
    public $group_reduction;

    /** @var float */
    public $product_quantity_discount;

    /** @var string */
    public $product_ean13;

    /** @var string */
    public $product_upc;

    /** @var string */
    public $product_reference;

    /** @var string */
    public $product_supplier_reference;

    /** @var float */
    public $product_weight;

    /** @var float */
    public $ecotax;

    /** @var float */
    public $ecotax_tax_rate;

    /** @var integer */
    public $discount_quantity_applied;

    /** @var string */
    public $download_hash;

    /** @var integer */
    public $download_nb;

    /** @var date */
    public $download_deadline;

    /** @var string $tax_name **/
    public $tax_name;

    /** @var float $tax_rate **/
    public $tax_rate;

    /** @var float $tax_computation_method **/
    public $tax_computation_method;

    /** @var int Id warehouse */
    public $warehouse_id;

    /** @var float additional shipping price tax excl */
    public $total_shipping_price_tax_excl;

    /** @var float additional shipping price tax incl */
    public $total_shipping_price_tax_incl;

    /** @var float */
    public $purchase_supplier_price;

    public static function getOderDetails($order_id){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_detail') . " WHERE " . $db->quoteName('order_id');
        $query .= " = " . (int)$order_id;

        $db->setQuery($query);
        return  $db->loadObjectList();
    }
}