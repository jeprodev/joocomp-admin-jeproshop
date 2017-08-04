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

class JeproshopStockAvailableModelStockAvailable extends JeproshopModel {
    public $product_id;
    public $product_attribute_id;
    public $shop_id;
    public $shop_group_id;
    public $quantity = 0;
    public $depends_on_stock = false;
    public $out_of_stock = false;

    public static function addShopRestriction($shop = NULL, $alias = NULL){
        $context = JeproshopContext::getContext();

        if(!empty($alias)) { $alias .= '.'; }

        /** If there is no shop id, get the context one **/
        if ($shop === null){
            if (JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_GROUP){
                $shopGroup = JeproshopShopModelShop::getContextShopGroup();
            }else{
                $shopGroup = $context->shop->getShopGroup();
            }

            $shop = $context->shop;
        }elseif (is_object($shop)){
            $shopGroup = $shop->getShopGroup();
        }else{
            $shop = new JeproshopShopModelShop($shop);
            $shopGroup = $shop->getShopGroup();
        }

        /* if quantities are shared between shops of the group */
        $db = JFactory::getDBO();
        if ($shopGroup->share_stock){
            $query = " AND " . $db->escape($alias). "shop_group_id = " .(int)$shopGroup->shop_group_id . " AND " . $db->escape($alias) . "shop_id = 0 ";

        }else{
            $query = " AND " . $db->escape($alias). "shop_group_id = 0 AND " . $db->escape($alias) . "shop_id = " .(int)$shop->shop_id.' ';
        }
        return $query;
    }

    public static function getQuantityAvailableByProduct($productId = null, $productAttributeId = null, $shopId = null){
        // if null, it's a product without attributes
        if ($productAttributeId === null){ $productAttributeId = 0; }

        $db = JFactory::getDBO();
        $query = "SELECT SUM(quantity) FROM " . $db->quoteName('#__jeproshop_stock_available');
        $query .= " WHERE product_attribute_id = " . (int)$productAttributeId;
        if($productId !== null){
            $query .= " AND product_id = " . (int)$productId;
        }
        $query .= JeproshopStockAvailableModelStockAvailable::addShopRestriction($shopId);

        $db->setQuery($query);
        $quantity = $db->loadResult();
        return ($quantity ? $quantity : 0);
    }

    public static function outOfStock($productId, $shopId = null){
        if (!JeproshopTools::isUnsignedInt($productId)){ return false; }

        $db = JFactory::getDBO();
        $query = "SELECT out_of_stock FROM " . $db->quoteName('#__jeproshop_stock_available') . " WHERE product_id = ";
        $query .= (int)$productId . " AND product_attribute_id = 0 " . JeproshopStockAvailableModelStockAvailable::addShopRestriction($shopId);

        $db->setQuery($query);
        $data = $db->loadObject();
        return (isset($data->out_of_stock) ? (int)$data->out_of_stock : 0);
    }

    /**
     * For a given product, tells if it depends on the physical (usable) stock
     *
     * @param int $productId
     * @param int $shopId Optional : gets context if null @see Context::getContext()
     * @return bool : depends on stock @see $depends_on_stock
     */
    public static function dependsOnStock($productId, $shopId = null){
        if(!JeproshopTools::isUnsignedInt($productId)){ return false; }
        $db = JFactory::getDBO();

        $query = "SELECT depends_on_stock FROM " . $db->quoteName('#__jeproshop_stock_available') . " WHERE product_id = " . (int)$productId;
        $query .= " AND product_attribute_id = 0 " . JeproshopStockAvailableModelStockAvailable::addShopRestriction($shopId);

        $db->setQuery($query);
        $data = $db->loadObject();

        return (isset($data->depends_on_stock) ? $data->depends_on_stock : 0);
    }
}


/** ---- JeproshopStockManagerFactory ---- **/
class JeproshopStockManagerFactory
{
    /**
     * @var $stock_manager : instance of the current StockManager.
     */
    protected static $stock_manager;

    /**
     * Returns a StockManager
     *
     * @return JeproshopStockManagerInterface
     */
    public static function getManager(){
        if (!isset(JeproshopStockManagerFactory::$stock_manager)){
            $stock_manager = JeproshopStockManagerFactory::executeStockManagerFactory();
            if (!($stock_manager instanceof JeproshopStockManagerInterface)){
                $stock_manager = new JeproshopStockManager();
            }
            JeproshopStockManagerFactory::$stock_manager = $stock_manager;
        }
        return JeproshopStockManagerFactory::$stock_manager;
    }

    /**
     *  Looks for a StockManager in the modules list.
     *
     *  @return JeproshopStockManagerInterface
     */
    public static function executeStockManagerFactory(){

    }
}


class JeproshopStockManager  implements JeproshopStockManagerInterface{
    /**
     * @see StockManagerInterface::isAvailable()
     */
    public static function isAvailable(){
        // Default Manager : always available
        return true;
    }

    /**
     * @see StockManagerInterface::addProduct()
     * @param int $productId
     * @param int $productAttributeId
     * @param JeproshopWarehouseModelWarehouse $warehouse
     * @param int $quantity
     * @param int $stockMovementReasonId
     * @param float $priceTaxExcluded
     * @param bool $isUsable
     * @param null $supplyOrderId
     * @return bool
     */
    public function addProduct($productId, $productAttributeId = 0, JeproshopWarehouseModelWarehouse $warehouse, $quantity, $stockMovementReasonId, $priceTaxExcluded, $isUsable = true, $supplyOrderId = null){
        if (!JeproshopTools::isLoadedObject($warehouse, 'warehouse_id') || !$price_tax_excluded || !$quantity || !$product_id){
            return false;
        }
        $priceTaxExcluded = (float)round($price_tax_excluded, 6);

        if (!StockMvtReason::exists($id_stock_mvt_reason))
            $id_stock_mvt_reason = Configuration::get('PS_STOCK_MVT_INC_REASON_DEFAULT');

        $context = Context::getContext();

        $mvt_params = array(
            'id_stock' => null,
            'physical_quantity' => $quantity,
            'id_stock_mvt_reason' => $id_stock_mvt_reason,
            'id_supply_order' => $id_supply_order,
            'price_te' => $price_te,
            'last_wa' => null,
            'current_wa' => null,
            'id_employee' => $context->employee->id,
            'employee_firstname' => $context->employee->firstname,
            'employee_lastname' => $context->employee->lastname,
            'sign' => 1
        );

        $stock_exists = false;

        // switch on MANAGEMENT_TYPE
        switch ($warehouse->management_type)
        {
            // case CUMP mode
            case 'WA':

                $stock_collection = $this->getStockCollection($id_product, $id_product_attribute, $warehouse->id);

                // if this product is already in stock
                if (count($stock_collection) > 0)
                {
                    $stock_exists = true;

                    // for a warehouse using WA, there is one and only one stock for a given product
                    $stock = $stock_collection->current();

                    // calculates WA price
                    $last_wa = $stock->price_te;
                    $current_wa = $this->calculateWA($stock, $quantity, $price_te);

                    $mvt_params['id_stock'] = $stock->id;
                    $mvt_params['last_wa'] = $last_wa;
                    $mvt_params['current_wa'] = $current_wa;

                    $stock_params = array(
                        'physical_quantity' => ($stock->physical_quantity + $quantity),
                        'price_te' => $current_wa,
                        'usable_quantity' => ($is_usable ? ($stock->usable_quantity + $quantity) : $stock->usable_quantity),
                        'id_warehouse' => $warehouse->id,
                    );

                    // saves stock in warehouse
                    $stock->hydrate($stock_params);
                    $stock->update();
                }
                else // else, the product is not in sock
                {
                    $mvt_params['last_wa'] = 0;
                    $mvt_params['current_wa'] = $price_te;
                }
                break;

            // case FIFO / LIFO mode
            case 'FIFO':
            case 'LIFO':

                $stock_collection = $this->getStockCollection($id_product, $id_product_attribute, $warehouse->id, $price_te);

                // if this product is already in stock
                if (count($stock_collection) > 0)
                {
                    $stock_exists = true;

                    // there is one and only one stock for a given product in a warehouse and at the current unit price
                    $stock = $stock_collection->current();

                    $stock_params = array(
                        'physical_quantity' => ($stock->physical_quantity + $quantity),
                        'usable_quantity' => ($is_usable ? ($stock->usable_quantity + $quantity) : $stock->usable_quantity),
                    );

                    // updates stock in warehouse
                    $stock->hydrate($stock_params);
                    $stock->update();

                    // sets mvt_params
                    $mvt_params['id_stock'] = $stock->id;

                }

                break;

            default:
                return false;
                break;
        }

        if (!$stock_exists)
        {
            $stock = new StockMo();

            $stock_params = array(
                'id_product_attribute' => $id_product_attribute,
                'id_product' => $id_product,
                'physical_quantity' => $quantity,
                'price_te' => $price_te,
                'usable_quantity' => ($is_usable ? $quantity : 0),
                'id_warehouse' => $warehouse->id
            );

            // saves stock in warehouse
            $stock->hydrate($stock_params);
            $stock->add();
            $mvt_params['id_stock'] = $stock->id;
        }

        // saves stock mvt
        $stock_mvt = new StockMvt();
        $stock_mvt->hydrate($mvt_params);
        $stock_mvt->add();

        return true;
    }

    /**
     * @see StockManagerInterface::removeProduct()
     */
    public function removeProduct($productId, $product_attribute_id = null, JeproshopWarehouseModelWarehouse $warehouse, $quantity, $stock_mvt_reason_id, $is_usable = true, $order_id = null){
        $return = array();

        if (!Validate::isLoadedObject($warehouse) || !$quantity || !$id_product)
            return $return;

        if (!StockMvtReason::exists($id_stock_mvt_reason))
            $id_stock_mvt_reason = Configuration::get('PS_STOCK_MVT_DEC_REASON_DEFAULT');

        $context = Context::getContext();

        // Special case of a pack
        if (Pack::isPack((int)$id_product))
        {
            // Gets items
            $products_pack = Pack::getItems((int)$id_product, (int)Configuration::get('PS_LANG_DEFAULT'));
            // Foreach item
            foreach ($products_pack as $product_pack)
            {
                $pack_id_product_attribute = Product::getDefaultAttribute($product_pack->id, 1);
                if ($product_pack->advanced_stock_management == 1)
                    $this->removeProduct($product_pack->id, $pack_id_product_attribute, $warehouse, $product_pack->pack_quantity * $quantity, $id_stock_mvt_reason, $is_usable, $id_order);
            }
        }
        else
        {
            // gets total quantities in stock for the current product
            $physical_quantity_in_stock = (int)$this->getProductPhysicalQuantities($id_product, $id_product_attribute, array($warehouse->id), false);
            $usable_quantity_in_stock = (int)$this->getProductPhysicalQuantities($id_product, $id_product_attribute, array($warehouse->id), true);

            // check quantity if we want to decrement unusable quantity
            if (!$is_usable)
                $quantity_in_stock = $physical_quantity_in_stock - $usable_quantity_in_stock;
            else
                $quantity_in_stock = $usable_quantity_in_stock;

            // checks if it's possible to remove the given quantity
            if ($quantity_in_stock < $quantity)
                return $return;

            $stock_collection = $this->getStockCollection($id_product, $id_product_attribute, $warehouse->id);
            $stock_collection->getAll();

            // check if the collection is loaded
            if (count($stock_collection) <= 0)
                return $return;

            $stock_history_qty_available = array();
            $mvt_params = array();
            $stock_params = array();
            $quantity_to_decrement_by_stock = array();
            $global_quantity_to_decrement = $quantity;

            // switch on MANAGEMENT_TYPE
            switch ($warehouse->management_type)
            {
                // case CUMP mode
                case 'WA':
                    // There is one and only one stock for a given product in a warehouse in this mode
                    $stock = $stock_collection->current();

                    $mvt_params = array(
                        'id_stock' => $stock->id,
                        'physical_quantity' => $quantity,
                        'id_stock_mvt_reason' => $id_stock_mvt_reason,
                        'id_order' => $id_order,
                        'price_te' => $stock->price_te,
                        'last_wa' => $stock->price_te,
                        'current_wa' => $stock->price_te,
                        'id_employee' => $context->employee->id,
                        'employee_firstname' => $context->employee->firstname,
                        'employee_lastname' => $context->employee->lastname,
                        'sign' => -1
                    );
                    $stock_params = array(
                        'physical_quantity' => ($stock->physical_quantity - $quantity),
                        'usable_quantity' => ($is_usable ? ($stock->usable_quantity - $quantity) : $stock->usable_quantity)
                    );

                    // saves stock in warehouse
                    $stock->hydrate($stock_params);
                    $stock->update();

                    // saves stock mvt
                    $stock_mvt = new StockMvt();
                    $stock_mvt->hydrate($mvt_params);
                    $stock_mvt->save();

                    $return[$stock->id]['quantity'] = $quantity;
                    $return[$stock->id]['price_te'] = $stock->price_te;

                    break;

                case 'LIFO':
                case 'FIFO':

                    // for each stock, parse its mvts history to calculate the quantities left for each positive mvt,
                    // according to the instant available quantities for this stock
                    foreach ($stock_collection as $stock)
                    {
                        $left_quantity_to_check = $stock->physical_quantity;
                        if ($left_quantity_to_check <= 0)
                            continue;

                        $resource = Db::getInstance(_PS_USE_SQL_SLAVE_)->query('
							SELECT sm.`id_stock_mvt`, sm.`date_add`, sm.`physical_quantity`,
								IF ((sm2.`physical_quantity` is null), sm.`physical_quantity`, (sm.`physical_quantity` - SUM(sm2.`physical_quantity`))) as qty
							FROM `'._DB_PREFIX_.'stock_mvt` sm
							LEFT JOIN `'._DB_PREFIX_.'stock_mvt` sm2 ON sm2.`referer` = sm.`id_stock_mvt`
							WHERE sm.`sign` = 1
							AND sm.`id_stock` = '.(int)$stock->id.'
							GROUP BY sm.`id_stock_mvt`
							ORDER BY sm.`date_add` DESC'
                        );

                        while ($row = Db::getInstance()->nextRow($resource))
                        {
                            // break - in FIFO mode, we have to retreive the oldest positive mvts for which there are left quantities
                            if ($warehouse->management_type == 'FIFO')
                                if ($row['qty'] == 0)
                                    break;

                            // converts date to timestamp
                            $date = new DateTime($row['date_add']);
                            $timestamp = $date->format('U');

                            // history of the mvt
                            $stock_history_qty_available[$timestamp] = array(
                                'id_stock' => $stock->id,
                                'id_stock_mvt' => (int)$row['id_stock_mvt'],
                                'qty' => (int)$row['qty']
                            );

                            // break - in LIFO mode, checks only the necessary history to handle the global quantity for the current stock
                            if ($warehouse->management_type == 'LIFO')
                            {
                                $left_quantity_to_check -= (int)$row['physical_quantity'];
                                if ($left_quantity_to_check <= 0)
                                    break;
                            }
                        }
                    }

                    if ($warehouse->management_type == 'LIFO')
                        // orders stock history by timestamp to get newest history first
                        krsort($stock_history_qty_available);
                    else
                        // orders stock history by timestamp to get oldest history first
                        ksort($stock_history_qty_available);

                    // checks each stock to manage the real quantity to decrement for each of them
                    foreach ($stock_history_qty_available as $entry)
                    {
                        if ($entry['qty'] >= $global_quantity_to_decrement)
                        {
                            $quantity_to_decrement_by_stock[$entry['id_stock']][$entry['id_stock_mvt']] = $global_quantity_to_decrement;
                            $global_quantity_to_decrement = 0;
                        }
                        else
                        {
                            $quantity_to_decrement_by_stock[$entry['id_stock']][$entry['id_stock_mvt']] = $entry['qty'];
                            $global_quantity_to_decrement -= $entry['qty'];
                        }

                        if ($global_quantity_to_decrement <= 0)
                            break;
                    }

                    // for each stock, decrements it and logs the mvts
                    foreach ($stock_collection as $stock)
                    {
                        if (array_key_exists($stock->id, $quantity_to_decrement_by_stock) && is_array($quantity_to_decrement_by_stock[$stock->id]))
                        {
                            $total_quantity_for_current_stock = 0;

                            foreach ($quantity_to_decrement_by_stock[$stock->id] as $id_mvt_referrer => $qte)
                            {
                                $mvt_params = array(
                                    'id_stock' => $stock->id,
                                    'physical_quantity' => $qte,
                                    'id_stock_mvt_reason' => $id_stock_mvt_reason,
                                    'id_order' => $id_order,
                                    'price_te' => $stock->price_te,
                                    'sign' => -1,
                                    'referer' => $id_mvt_referrer,
                                    'id_employee' => $context->employee->id
                                );

                                // saves stock mvt
                                $stock_mvt = new StockMvt();
                                $stock_mvt->hydrate($mvt_params);
                                $stock_mvt->save();

                                $total_quantity_for_current_stock += $qte;
                            }

                            $stock_params = array(
                                'physical_quantity' => ($stock->physical_quantity - $total_quantity_for_current_stock),
                                'usable_quantity' => ($is_usable ? ($stock->usable_quantity - $total_quantity_for_current_stock) : $stock->usable_quantity)
                            );

                            $return[$stock->id]['quantity'] = $total_quantity_for_current_stock;
                            $return[$stock->id]['price_te'] = $stock->price_te;

                            // saves stock in warehouse
                            $stock->hydrate($stock_params);
                            $stock->update();
                        }
                    }
                    break;
            }
        }

        // if we remove a usable quantity, exec hook
        if ($is_usable)
            Hook::exec('actionProductCoverage',
                array(
                    'id_product' => $id_product,
                    'id_product_attribute' => $id_product_attribute,
                    'warehouse' => $warehouse
                )
            );

        return $return;
    }

    /**
     * @see StockManagerInterface::getProductPhysicalQuantities()
     * @param int $product_id
     * @param int $product_attribute_id
     * @param null $warehouse_ids
     * @param bool $usable
     * @return int
     */
    public function getProductPhysicalQuantities($product_id, $product_attribute_id, $warehouse_ids = null, $usable = false){
        $db = JFactory::getDBO();
        if (!is_null($warehouse_ids)){
            // in case $ids_warehouse is not an array
            if (!is_array($warehouse_ids)) {
                $warehouse_ids = array($warehouse_ids);
            }

            // casts for security reason
            $warehouse_ids = array_map('intval', $warehouse_ids);
            if (!count($warehouse_ids)){ return 0; }
        }else {
            $warehouse_ids = array();
        }

        $query = "SELECT SUM(" . ($usable ? " stock.usable_quantity" : "stock.physical_quantity") . ") AS quantity FROM " . $db->quoteName('#__jeproshop_stock');
        $query .= " AS stock WHERE stock." . $db->quoteName('product_id') . " = " . (int)$product_id;

        if (0 != $product_attribute_id)
            $query .= " AND stock." . $db->quoteName('product_attribute_id') . " = " . (int)$product_attribute_id;

        if (count($warehouse_ids))
            $query .= " AND stock." . $db->quoteName('warehouse_id') . " IN(" .implode(', ', $warehouse_ids) . ")" ;

        $db->setQuery($query);
        return (int)$db->loaResult();
    }

    /**
     * @see StockManagerInterface::getProductRealQuantities()
     * @param $product_id
     * @param $product_attribute_id
     * @param null $warehouse_ids
     * @param bool $usable
     * @return int
     */
    public function getProductRealQuantities($product_id, $product_attribute_id, $warehouse_ids = null, $usable = false){
        $db = JFactory::getDBO();
        if (!is_null($warehouse_ids)){
            // in case $ids_warehouse is not an array
            if (!is_array($warehouse_ids)){ $warehouse_ids = array($warehouse_ids); }

            // casts for security reason
            $warehouse_ids = array_map('intval', $warehouse_ids);
        }

        // Gets client_orders_qty
        $query = "SELECT order_detail.product_quantity, order_detail.product_quantity_refunded FROM " . $db->quoteName('#__jeproshop_order_detail') . " AS order_detail LEFT JOIN " . $db->quoteName('#__jeproshop_orders');
        $query .= " AS ord ON (ord." . $db->quoteName('order_id') . " = order_detail." . $db->quoteName('order_id');

        $where = " WHERE order_detail." . $db->quoteName('product_id') . " = " . (int)$product_id;

        if (0 != $product_attribute_id) {
            $where .= " AND order_detail." . $db->quoteName('product_attribute_id') . " = "  . (int)$product_attribute_id;
        }
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_order_history') .  " AS order_history ON (order_history." . $db->quoteName('order_id') . " = ord." . $db->quoteName('order_id') . " AND order_history.";
        $query .= $db->quoteName('order_status_id') . " = ord." . $db->quoteName('current_status') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_order_status') . " AS order_status ON(order_status."  . $db->quoteName('order_status_id');
        $query .= " = order_history." . $db->quoteName('order_status_id') . ") ";
        $where .= " AND order_status." . $db->quoteName('shipped') . " != 1 AND ord." . $db->quoteName('valid') . " = 1 OR (order_status." . $db->quoteName('order_status_id') . " != " .(int)JeproshopSettingModelSetting::getValue('order_status_error');
        $where .= " AND order_status." . $db->quoteName('order_status_id') . " != " . (int)JeproshopSettingModelSetting::getValue('order_status_canceled');
        $groupBy = " GROUP BY order_detail." . $db->quoteName('order_detail_id');
        if (count($warehouse_ids)){
            $where .= " AND order_detail.warehouse_id IN(" .implode(', ', $warehouse_ids) . ") ";
        }
        $db->setQuery($query . $where . ")". $groupBy);
        $res = $db->loadObjectList();
        $client_orders_quantity = 0;
        if (count($res)) {
            foreach ($res as $row) {
                $client_orders_quantity += ($row->product_quantity - $row->product_quantity_refunded);
            }
        }

        // Gets supply_orders_qty
        $query = "SELECT supply_order_detail." . $db->quoteName('quantity_expected') . ", supply_order_detail." . $db->quoteName('quantity_received') . " FROM " . $db->quoteName('#__jeproshop_supply_order');
        $query .= " AS supply_order LEFT JOIN " . $db->quoteName('#__jeproshop_supply_order_detail') . " AS supply_order_detail ON (supply_order_detail." . $db->quoteName('supply_order_id') . " = supply_order.";
        $query .= $db->quoteName('supply_order_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_supply_order_status') . " AS supply_order_status ON (supply_order_status." . $db->quoteName('supply_order_status_id');
        $query .= " = supply_order." . $db->quoteName('supply_order_status_id') . ") WHERE supply_order_status." . $db->quoteName('pending_receipt') . " = 1 AND supply_order_detail." . $db->quoteName('product_id');
        $query .= " = " . (int)$product_id . " AND supply_order_detail." . $db->quoteName('product_attribute_id') . " = " . (int)$product_attribute_id ;
        if (!is_null($warehouse_ids) && count($warehouse_ids)) {
            $query .= " AND supply_order." . $db->quoteName('warehouse_id') . " IN (" . implode(', ', $warehouse_ids) . ")" ;
        }

        $db->setQuery($query);
        $supply_orders_quantities = $db->loadObjectList();

        $supply_orders_quantity = 0;
        foreach ($supply_orders_quantities as $quantity) {
            if ($quantity->quantity_expected > $quantity->quantity_received) {
                $supply_orders_quantity += ($quantity->quantity_expected - $quantity->quantity_received);
            }
        }

        // Gets {physical OR usable}_qty
        $quantity = $this->getProductPhysicalQuantities($product_id, $product_attribute_id, $warehouse_ids, $usable);

        //real qty = actual qty in stock - current client orders + current supply orders
        return ($quantity - $client_orders_quantity + $supply_orders_quantity);
    }

    /**
     * @see StockManagerInterface::transferBetweenWarehouses()
     * @param int $product_id
     * @param $product_attribute_id
     * @param int $quantity
     * @param int $warehouse_from_id
     * @param int $warehouse_to_id
     * @param bool $usable_from
     * @param bool $usable_to
     * @return bool
     */
    public function transferBetweenWarehouses($product_id, $product_attribute_id, $quantity, $warehouse_from_id, $warehouse_to_id, $usable_from = true, $usable_to = true){
        // Checks if this transfer is possible
        if ($this->getProductPhysicalQuantities($id_product, $id_product_attribute, array($id_warehouse_from), $usable_from) < $quantity)
            return false;

        if ($id_warehouse_from == $id_warehouse_to && $usable_from == $usable_to)
            return false;

        // Checks if the given warehouses are available
        $warehouse_from = new Warehouse($id_warehouse_from);
        $warehouse_to = new Warehouse($id_warehouse_to);
        if (!Validate::isLoadedObject($warehouse_from) ||
            !Validate::isLoadedObject($warehouse_to))
            return false;

        // Removes from warehouse_from
        $stocks = $this->removeProduct($id_product,
            $id_product_attribute,
            $warehouse_from,
            $quantity,
            Configuration::get('PS_STOCK_MVT_TRANSFER_FROM'),
            $usable_from);
        if (!count($stocks))
            return false;

        // Adds in warehouse_to
        foreach ($stocks as $stock)
        {
            $price = $stock['price_te'];

            // convert product price to destination warehouse currency if needed
            if ($warehouse_from->id_currency != $warehouse_to->id_currency)
            {
                // First convert price to the default currency
                $price_converted_to_default_currency = Tools::convertPrice($price, $warehouse_from->id_currency, false);

                // Convert the new price from default currency to needed currency
                $price = Tools::convertPrice($price_converted_to_default_currency, $warehouse_to->id_currency, true);
            }

            if (!$this->addProduct($id_product,
                $id_product_attribute,
                $warehouse_to,
                $stock['quantity'],
                Configuration::get('PS_STOCK_MVT_TRANSFER_TO'),
                $price,
                $usable_to))
                return false;
        }
        return true;
    }

    /**
     * @see StockManagerInterface::getProductCoverage()
     * Here, $coverage is a number of days
     * @return int number of days left (-1 if infinite)
     */
    public function getProductCoverage($id_product, $id_product_attribute, $coverage, $id_warehouse = null)
    {
        if (!$id_product_attribute)
            $id_product_attribute = 0;

        if ($coverage == 0 || !$coverage)
            $coverage = 7; // Week by default

        // gets all stock_mvt for the given coverage period
        $query = '
			SELECT SUM(view.quantity) as quantity_out
			FROM
			(	SELECT sm.`physical_quantity` as quantity
				FROM `'._DB_PREFIX_.'stock_mvt` sm
				LEFT JOIN `'._DB_PREFIX_.'stock` s ON (sm.`id_stock` = s.`id_stock`)
				LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = s.`id_product`)
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
				'.Shop::addSqlAssociation('product_attribute', 'pa', false).'
				WHERE sm.`sign` = -1
				AND sm.`id_stock_mvt_reason` != '.Configuration::get('PS_STOCK_MVT_TRANSFER_FROM').'
				AND TO_DAYS(NOW()) - TO_DAYS(sm.`date_add`) <= '.(int)$coverage.'
				AND s.`id_product` = '.(int)$id_product.'
				AND s.`id_product_attribute` = '.(int)$id_product_attribute.
            ($id_warehouse ? ' AND s.`id_warehouse` = '.(int)$id_warehouse : '').'
				GROUP BY sm.`id_stock_mvt`
			) as view';

        $quantity_out = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        if (!$quantity_out)
            return -1;

        $quantity_per_day = Tools::ps_round($quantity_out / $coverage);
        $physical_quantity = $this->getProductPhysicalQuantities($id_product,
            $id_product_attribute,
            ($id_warehouse ? array($id_warehouse) : null),
            true);
        $time_left = ($quantity_per_day == 0) ? (-1) : Tools::ps_round($physical_quantity / $quantity_per_day);

        return $time_left;
    }

    /**
     * For a given stock, calculates its new WA(Weighted Average) price based on the new quantities and price
     * Formula : (physicalStock * lastCump + quantityToAdd * unitPrice) / (physicalStock + quantityToAdd)
     *
     * @param Stock|PrestaShopCollection $stock
     * @param int $quantity
     * @param float $price_te
     * @return int WA
     */
    protected function calculateWA(Stock $stock, $quantity, $price_te)
    {
        return (float)Tools::ps_round(((($stock->physical_quantity * $stock->price_te) + ($quantity * $price_te)) / ($stock->physical_quantity + $quantity)), 6);
    }

    /**
     * For a given product, retrieves the stock collection
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_warehouse Optional
     * @param int $price_te Optional
     * @return PrestaShopCollection Collection of Stock
     */
    protected function getStockCollection($id_product, $id_product_attribute, $id_warehouse = null, $price_te = null){
        $stocks = new PrestaShopCollection('Stock');
        $stocks->where('id_product', '=', $id_product);
        $stocks->where('id_product_attribute', '=', $id_product_attribute);
        if ($id_warehouse)
            $stocks->where('id_warehouse', '=', $id_warehouse);
        if ($price_te)
            $stocks->where('price_te', '=', $price_te);

        return $stocks;
    }
}


/** -- JeproshopStockManagerInterface ---**/
interface JeproshopStockManagerInterface
{
    /**
     * Checks if the StockManager is available
     *
     * @return StockManagerInterface
     */
    public static function isAvailable();

    /**
     * For a given product, adds a given quantity
     *
     * @param int $productId
     * @param int $productAttributeId
     * @param JeproshopWarehouseModelWarehouse $warehouse
     * @param int $quantity
     * @param int $stockMovementReasonId
     * @param float $priceTaxExcluded
     * @param bool $isUsable
     * @param int $supplyOrderId optional
     * @return bool
     */
    public function addProduct($productId, $productAttributeId, JeproshopWarehouseModelWarehouse $warehouse, $quantity, $stockMovementReasonId, $priceTaxExcluded, $isUsable = true, $supplyOrderId = null);

    /**
     * For a given product, removes a given quantity
     *
     * @param int $productId
     * @param int $productAttributeId
     * @param JeproshopWarehouseModelWarehouse $warehouse
     * @param int $quantity
     * @param int $stockMovementReasonId
     * @param bool $isUsable
     * @param int $orderId Optional
     * @return array - empty if an error occurred | details of removed products quantities with corresponding prices otherwise
     */
    public function removeProduct($productId, $productAttributeId, JeproshopWarehouseModelWarehouse $warehouse, $quantity, $stockMovementReasonId, $isUsable = true, $orderId = null);

    /**
     * For a given product, returns its physical quantity
     * If the given product has combinations and $id_product_attribute is null, returns the sum for all combinations
     *
     * @param int $productId
     * @param int $productAttributeId
     * @param array|int $warehouseIds optional
     * @param bool $usable false default - in this case we retrieve all physical quantities, otherwise we retrieve physical quantities flagged as usable
     * @return int
     */
    public function getProductPhysicalQuantities($productId, $productAttributeId, $warehouseIds = null, $usable = false);

    /**
     * For a given product, returns its real quantity
     * If the given product has combinations and $id_product_attribute is null, returns the sum for all combinations
     * Real quantity : (physical_qty + supply_orders_qty - client_orders_qty)
     * If $usable is defined, real quantity: usable_qty + supply_orders_qty - client_orders_qty
     *
     * @param int $productId
     * @param int $productAttributeId
     * @param array|int $warehouseIds optional
     * @param bool $usable false by default
     * @return int
     */
    public function getProductRealQuantities($productId, $productAttributeId, $warehouseIds = null, $usable = false);

    /**
     * For a given product, transfers quantities between two warehouses
     * By default, it manages usable quantities
     * It is also possible to transfer a usable quantity from warehouse 1 in an unusable quantity to warehouse 2
     * It is also possible to transfer a usable quantity from warehouse 1 in an unusable quantity to warehouse 1
     *
     * @param int $productId
     * @param int $productAttributeId
     * @param int $quantity
     * @param int $warehouseFrom
     * @param int $warehouseTo
     * @param bool $usableFrom Optional, true by default
     * @param bool $usableTo Optional, true by default
     * @return bool
     */
    public function transferBetweenWarehouses($productId, $productAttributeId, $quantity, $warehouseFrom, $warehouseTo, $usableFrom = true, $usableTo = true);

    /**
     * For a given product, returns the time left before being out of stock.
     * By default, for the given product, it will use sum(quantities removed in all warehouses)
     *
     * @param int $productId
     * @param int $productAttributeId
     * @param int $coverage
     * @param int $warehouseId Optional
     * @return int time
     */
    public function getProductCoverage($productId, $productAttributeId, $coverage, $warehouseId = null);
}

