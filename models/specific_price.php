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

class JeproshopSpecificPriceModelSpecificPrice extends JeproshopModel{
    public $product_id;

    public $specific_price_rule_id = 0;

    public $cart_id = 0;

    public $product_attribute_id;

    public $specific_price_id;

    public $shop_id;

    public $shop_group_id;

    public $currency_id;

    public $country_id;

    public $group_id;

    public $customer_id;

    public $price;

    public $from_quantity;

    public $reduction;

    public $reduction_type;
    
    public $reduction_tax;

    public $from;

    public $to;

    protected static $_specific_price_cache = array();
    protected static $_cache_priorities = array();

    public static function getSpecificPricesByProductId($productId, $productAttributeId = false, $cartId = FALSE){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_specific_price') . " WHERE " . $db->quoteName('product_id');
        $query .= " = " . (int)$productId . ($productAttributeId ? " AND " . $db->quoteName('product_attribute_id') . " = " . (int)$productAttributeId : " ");
        $query .= " AND cart_id = " . (int)$cartId;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getPriority($productId){
        if(!JeproshopSpecificPriceModelSpecificPrice::isFeaturePublished()){
            return explode(';', JeproshopSettingModelSetting::getValue('specific_price_priorities'));
        }

        if(!isset(JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$productId])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('priority') . ", " . $db->quoteName('specific_price_priority_id') . " FROM ";
            $query .= $db->quoteName('#__jeproshop_specific_price_priority') ." WHERE " . $db->quoteName('product_id') . " = ";
            $query .= (int)$productId . " ORDER BY " . $db->quoteName('specific_price_priority_id') . " DESC ";

            $db->setQuery($query);
            JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$productId] = $db->loadObject();
        }
        $priorities = JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$productId];
        if(!$priorities){
            $priority = JeproshopSettingModelSetting::getValue('specific_price_priorities');
            $priorities = 'customer_id;' . $priority;
        }else{
            $priorities = $priorities->priority;
        }
        return preg_split('/;/', $priorities);
    }

    public static function isFeaturePublished(){
        static $feature_active = NULL;
        if($feature_active === NULL){
            $feature_active = JeproshopSettingModelSetting::getValue('specific_price_feature_active');
        }
        return $feature_active;
    }

    public static function getSpecificPrice($productId, $shopId, $currencyId, $countryId, $groupId, $quantity, $productAttributeId = null, $customerId = 0, $cartId = 0, $realQuantity = 0){
        if (!JeproshopSpecificPriceModelSpecificPrice::isFeaturePublished()){ return array(); }
        /*
        ** The date is not taken into account for the cache, but this is for the better because it keeps the consistency for the whole script.
        ** The price must not change between the top and the bottom of the page
        */

        $db = JFactory::getDBO();
        $key = ((int)$productId . '_' . (int)$shopId . '_' . (int)$currencyId . '_' . (int)$countryId . '_' . (int)$groupId . '_' . (int)$quantity . '_' . (int)$productAttributeId . '_'.(int)$cartId . '_' . (int)$customerId . '_' . (int)$realQuantity);
        if (!array_key_exists($key, JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache)) {
            $now = date('Y-m-d H:i:s');
            $query = "SELECT *, " . JeproshopSpecificPriceModelSpecificPrice::getScoreQuery($productId, $shopId, $currencyId, $countryId, $groupId, $customerId);
            $query .= " FROM " . $db->quoteName('#__jeproshop_specific_price') . " WHERE " . $db->quoteName('product_id') . " IN (0, " .(int)$productId . ") AND ";
            $query .= $db->quoteName('product_attribute_id') . " IN (0, " .(int)$productAttributeId . ") AND " . $db->quoteName('shop_id') . " IN (0, " . (int)$shopId;
            $query .= ") AND " . $db->quoteName('currency_id') . " IN (0, " .(int)$currencyId . ") AND " . $db->quoteName('country_id') . " IN (0, " .(int)$countryId ;
            $query .= ") AND " . $db->quoteName('group_id') . " IN (0, " .(int)$groupId . ") AND " . $db->quoteName('customer_id') . " IN (0, " .(int)$customerId . ") ";
            $query .= "AND ( (" . $db->quoteName('from') . " = '0000-00-00 00:00:00' OR '" . $now . "' >= " . $db->quoteName('from') . ") AND (" . $db->quoteName('to') ;
            $query .= " = '0000-00-00 00:00:00' OR '" . $now. "' <= " . $db->quoteName('to') . ") ) AND cart_id IN (0, ".(int)$cartId . ") AND IF(" . $db->quoteName('from_quantity');
            $query .= " > 1, " . $db->quoteName('from_quantity') . ", 0) <= " ;
            $query .= (JeproshopSettingModelSetting::getValue('qty_discount_on_combination') || !$cartId || !$realQuantity) ? (int)$quantity : max(1, (int)$realQuantity);
            $query .= " ORDER BY " . $db->quoteName('product_attribute_id') . " DESC, " . $db->quoteName('from_quantity') . " DESC, " . $db->quoteName('specific_price_rule_id');
            $query .= " ASC, " . $db->quoteName('score') . " DESC";

            $db->setQuery($query);
            JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache[$key] = $db->loadObject();

        }
        return JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache[$key];
    }

    /**
     * score generation for quantity discount
     * @param $productId
     * @param $shopId
     * @param $currencyId
     * @param $countryId
     * @param $groupId
     * @param $customerId
     * @return string
     */
    protected static function getScoreQuery($productId, $shopId, $currencyId, $countryId, $groupId, $customerId){
        $db = JFactory::getDBO();
        $now = date('Y-m-d H:i:s');
        $select = "( IF ('" .$now. "' >= " . $db->quoteName('from') . " AND '" . $now. "' <= " . $db->quoteName('to') . ", ".pow(2, 0).", 0) + ";

        $priority = JeproshopSpecificPriceModelSpecificPrice::getPriority($productId);
        foreach (array_reverse($priority, ",") as $k => $field){
            if (!empty($field)){
                $select .= " IF (" . $db->quote($field) . " = ";
                if($field == 'country_id'){
                    $select .= (int)$countryId;
                }else if($field == 'currency_id') {
                    $select .= (int)$currencyId;
                }else if($field == 'group_id'){
                    $select .= (int)$groupId;
                }else if($field == 'customer_id'){
                    $select .= (int)$customerId;
                }else if($field == 'country_id'){
                    $select .= (int)$countryId;
                }else if($field == 'shop_id'){
                    $select.= (int)$shopId;
                }
                $select .= ", " .pow(2, $k + 1).", 0) + ";
            }
        }
        return rtrim($select, ' + '). ") AS " . $db->quoteName('score');
    }

    public function add(){
        $db = JFactory::getDBO();

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_specific_price') . "(" . $db->quoteName('specific_price_rule_id');
        $query .= ", " . $db->quoteName('product_id') . ", " . $db->quoteName('shop_id') . ", " . $db->quoteName('shop_group_id');
        $query .= ", " . $db->quoteName('currency_id') . ", " . $db->quoteName('country_id') . ", " . $db->quoteName('group_id');
        $query .= ", " . $db->quoteName('customer_id') . ", " . $db->quoteName('product_attribute_id') . ", " . $db->quoteName('price');
        $query .= ", " . $db->quoteName('from_quantity') . ", " . $db->quoteName('reduction') . ", " . $db->quoteName('reduction_type');
        $query .= ", " . $db->quoteName('from') . ", " . $db->quoteName('to') . ") VALUES (" . (int)$this->specific_price_rule_id . ", ";
        $query .= (int)$this->product_id . ", " . (int)$this->shop_id . ", " . (int)$this->shop_group_id . ", " . (int)$this->currency_id . ", ";
        $query .= (int)$this->country_id . ", " . (int)$this->group_id . ", " . (int)$this->customer_id . ", " . (int)$this->product_attribute_id;
        $query .= ", " . (float)$this->price . ", " . (int)$this->from_quantity . ", " . (float)$this->reduction . ", " . $db->quote($this->reduction_type);
        $query .= ", " . $db->quote($this->from) . ", " . $db->quote($this->to) . ")";

        $db->setQuery($query);
        if($db->query()){
            // Flush cache when we adding a new specific price
            JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache = array();
            JeproshopProductModelProduct::flushPriceCache();
            // Set cache of feature detachable to true
            JeproshopSettingModelSetting::updateValue('specific_price_feature_active', '1');
            return true;
        }
        return false;
    }

    public static function exists($productId, $productAttributeId, $shopId, $groupId, $countryId, $currencyId, $customerId, $fromQuantity, $from, $to, $rule = false) {
        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('specific_price_id') . " FROM " . $db->quoteName('#__jeproshop_specific_price') . " WHERE ";
        $query .= $db->quoteName('product_id') . " = " . (int)$productId . " AND " . $db->quoteName('product_attribute_id') . " = " ;
        $query .= (int)$productAttributeId . " AND " . $db->quoteName('shop_id') . " = " .(int)$shopId . " AND " . $db->quoteName('group_id');
        $query .= " = " . (int)$groupId . " AND " . $db->quoteName('country_id') . " = " . (int)$countryId . " AND " . $db->quoteName('currency_id');
        $query .= " = " . (int)$currencyId . " AND " . $db->quoteName('customer_id') . " = " . (int)$customerId . " AND " . $db->quoteName('from_quantity');
        $query .= " = " . (int)$fromQuantity . " AND " . $db->quoteName('from') . " >= " . $db->quote($from) . " AND " . $db->quoteName('to') . " <= ";
        $query .= $db->quote($to) . " AND " . $db->quoteName('specific_price_rule_id') .(!$rule ? '=0' : '!=0');

        $db->setQuery($query);
        $data = $db->loadObject();

        return (int)(isset($data) ? $data->specific_price_id : 0);
    }

    public static function setPriorities($priorities){
        $value = '';
        $db = JFactory::getDBO();
        if (is_array($priorities)) {
            foreach ($priorities as $priority) {
                $value .= $priority.';';
            }
        }

        JeproshopSpecificPriceModelSpecificPrice::deletePriorities();

        return JeproshopSettingModelSetting::updateValue('specific_price_priorities', rtrim($value, ';'));
    }

    public static function deletePriorities(){
        $db = JFactory::getDBO();
        
        $query = "TRUNCATE " . $db->quoteName('#__jeproshop_specific_price_priority') ; 
        $db->setQuery($query);
        return $db->query();
		
    }

    public static function setSpecificPriority($productId, $priorities){
        $value = '';
        $db = JFactory::getDBO();
        if (is_array($priorities)) {
            foreach ($priorities as $priority) {
                $value .= $db->quote($priority).';';
            }
        }

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_specific_price_priority') . "(" . $db->quoteName('product_id') . ", ";
        $query .= $db->quoteName('priority') . ") VALUES (" .(int)$productId . ", ". $db->quote(rtrim($value, ';')) . ") ON DUPLICATE";
        $query .= "KEY UPDATE " . $db->quoteName('priority') . " = " . $db->quote(rtrim($value, ';'));

        $db->setQuery($query);

        return $db->query();
    }

}


class JeproshopSpecificPriceRuleModelSpecificPriceRule extends JeproshopModel {
    public $specific_price_rule_id;
    public $name;
    public $shop_id;
    public $currency_id;
    public $country_id;
    public $group_id;
    public $from_quantity;
    public $price;
    public $reduction;
    public $reduction_type;
    public $from;
    public $to;

    protected static $rules_application_enable = true;

    public function __construct($specificPriceRuleId = null){
        if($specificPriceRuleId){
            $cacheKey = "jeproshop_specific_price_rule_" . $specificPriceRuleId;
            if(JeproshopCache::isStored($cacheKey)){
                $specificPriceRuleData = JeproshopCache::retrieve($cacheKey);
            }else {
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_specific_price_rule') . " WHERE " . $db->quoteName('specific_price_rule_id');
                $query .= " = " . (int)$specificPriceRuleId;

                $db->setQuery($query);
                $specificPriceRuleData = $db->loadObject();
            }

            if($specificPriceRuleData){
                $this->specific_price_rule_id = $specificPriceRuleId;
                foreach($specificPriceRuleData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
            
        }
    }

    /**
     * @param array|bool $products
     */
    public static function applyAllRules($products = false){
        if (!JeproshopSpecificPriceRuleModelSpecificPriceRule::$rules_application_enable){ return; }
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_specific_price_rule');

        $db->setQuery($query);
        $rules = $db->loadObjectList();

        foreach ($rules as $rule){
            (new JeproshopSpecificPriceRuleModelSpecificPriceRule($rule->specific_price_rule_id))->apply($products);
        }
    }

    public function apply($products = false){
        if (!JeproshopSpecificPriceRuleModelSpecificPriceRule::$rules_application_enable){ return; }

        $this->resetApplication($products);
        $products = $this->getAffectedProducts($products);
        foreach ($products as $product){
            JeproshopSpecificPriceRuleModelSpecificPriceRule::applyRuleToProduct((int)$this->specific_price_rule_id, (int)$product->product_id, (int)$product->product_attribute_id);
        }
    }

    public static function applyRuleToProduct($ruleId, $productId, $productAttributeId = null){
        $rule = new JeproshopSpecificPriceRuleModelSpecificPriceRule((int)$ruleId);
        if (!JeproshopTools::isLoadedObject($rule, 'specific-price_rule_id') || !$productId){
            return false;
        }

        $specificPrice = new JeproshopSpecificPriceModelSpecificPrice();
        $specificPrice->specific_price_rule_id = (int)$rule->specific_price_rule_id;
        $specificPrice->product_id = (int)$productId;
        $specificPrice->product_attribute_id = (int)$productAttributeId;
        $specificPrice->customer_id = 0;
        $specificPrice->shop_id = (int)$rule->shop_id;
        $specificPrice->country_id = (int)$rule->country_id;
        $specificPrice->currency_id = (int)$rule->currency_id;
        $specificPrice->group_id = (int)$rule->group_id;
        $specificPrice->from_quantity = (int)$rule->from_quantity;
        $specificPrice->price = (float)$rule->price;
        $specificPrice->reduction_type = $rule->reduction_type;
        $specificPrice->reduction = ($rule->reduction_type == 'percentage' ? $rule->reduction / 100 : (float)$rule->reduction);
        $specificPrice->from = $rule->from;
        $specificPrice->to = $rule->to;

        return $specificPrice->add();
    }

    /**
     * This method is allow to know if a entity is currently used
     *
     * @param string $table name of table linked to entity
     * @param bool $hasActiveColumn true if the table has an active column
     * @return bool
     */
    public static function isCurrentlyUsed($table = null, $hasActiveColumn = false){
        if ($table === null)
            $table = 'specific_rule';
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName($table .'_id') . " FROM " . $db->quoteName('#__jeproshop_' . $table);

        if ($hasActiveColumn)
            $query .= " WHERE " . $db->quoteName('published') . " = 1";

        $db->setQuery($query);
        $data = $db->loadObject();
        return (bool)(isset($data) ? (int)$data->{$table . '_id'} : 0);
    }

    public function deleteConditions(){
        $ids_condition_group = Db::getInstance()->executeS('SELECT id_specific_price_rule_condition_group
																		 FROM '._DB_PREFIX_.'specific_price_rule_condition_group
																		 WHERE id_specific_price_rule='.(int)$this->id);
        if ($ids_condition_group) {
            foreach ($ids_condition_group as $row) {
                Db::getInstance()->delete('specific_price_rule_condition_group', 'id_specific_price_rule_condition_group='.(int)$row['id_specific_price_rule_condition_group']);
                Db::getInstance()->delete('specific_price_rule_condition', 'id_specific_price_rule_condition_group='.(int)$row['id_specific_price_rule_condition_group']);
            }
        }
    }

    public static function disableAnyApplication() {
        JeproshopSpecificPriceRuleModelSpecificPriceRule::$rules_application_enable = false;
    }

    public static function enableAnyApplication(){
        JeproshopSpecificPriceRuleModelSpecificPriceRule::$rules_application_enable = true;
    }

    public function addConditions($conditions) {
        if (!is_array($conditions)) {
            return false;
        }

        $result = Db::getInstance()->insert('specific_price_rule_condition_group', array(
            'id_specific_price_rule' =>    (int)$this->id
        ));
        if (!$result) {
            return false;
        }
        $id_specific_price_rule_condition_group = (int)Db::getInstance()->Insert_ID();
        foreach ($conditions as $condition) {
            $result = Db::getInstance()->insert('specific_price_rule_condition', array(
                'id_specific_price_rule_condition_group' => (int)$id_specific_price_rule_condition_group,
                'type' => pSQL($condition['type']),
                'value' => (float)$condition['value'],
            ));
            if (!$result) {
                return false;
            }
        }
        return true;
    }

    public function resetApplication($products = null){
        $where = '';
        $db = JFactory::getDBO();
        if ($products && count($products)) {
            $where .= " AND " . $db->quoteName('product_id') . " IN (" .implode(', ', array_map('intval', $products)). ")";
        }
        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_specific_price') ."  WHERE " . $db->quoteName('specific_price_rule_id');
        $query .= " = " .(int)$this->specific_price_rule_id . $where;

        $db->setQuery($query);
        $db->query();
    }

    public function getConditions(){
        $db = JFactory::getDBO();
        $query = "SELECT condition_group.*, condition.* FROM " . $db->quoteName('#__jeproshop_specific_price_rule_condition_group');
        $query .= " AS condition_group LEFT JOIN " . $db->quoteName('#__jeproshop_specific_price_rule_condition') . " AS condition ";
        $query .= " ON (condition" . $db->quoteName('specific_price_rule_condition_group_id') . " = condition_group.";
        $query .= $db->quoteName('specific_price_rule_condition_group_id') . " ) WHERE condition_group." . $db->quoteName('specific_price_rule_id');
        $query .= " = " . (int)$this->specific_price_rule_id;

        $db->setQuery($query);
        $conditions = $db->loadOjectList();
        $conditionsGroup = array();

        if ($conditions) {
            foreach ($conditions as &$condition) {
                if ($condition->type == 'attribute') {
                    $query = "SELECT " .$db->quoteName('attribute_group_id')  . " FROM " . $db->quoteName('#__jeproshop_attribute');
                    $query .= " WHERE " .$db->quoteName('attribute_id') . " = " .(int)$condition->value;

                    $db->setQuery($query);
                    $data = $db->loadObject();
                    $condition->attribute_group_id = (isset($data) ? $data->attribute_group_id : 0);
                } elseif ($condition->type == 'feature') {
                    $query = "SELECT " . $db->quoteName('feature_id') . " FROM " . $db->quoteName('#__jeproshop_feature_value');
                    $query .= " WHERE " . $db->quoteName('feature_value_id') . " = " . (int)$condition->value;

                    $db->setQuery($query);
                    $data = $db->loadObject();
                    $condition->feature_id = (isset($data) ? $data->feature_id : 0);
                }
                $conditionsGroup[(int)$condition->specific_price_rule_condition_group_id][] = $condition;
            }
        }
        return $conditionsGroup;
    }

    /**
     * Return the product list affected by this specific rule.
     *
     * @param bool|array $products Products list limitation.
     * @return array Affected products list IDs.
     */
    public function getAffectedProducts($products = false){
        $conditionsGroup = $this->getConditions();
        $currentShopId = JeproshopContext::getContext()->shop->shop_id;

        $result = array();
        $db = JFactory::getDBO();
        if ($conditionsGroup){

            foreach ($conditionsGroup as $conditionGroupId => $conditionGroup) {
                // Base request
                $querySelect = "SELECT product." . $db->quoteName('product_id');
                $queryFrom = " FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
                $queryLeftJoin = " LEFT JOIN " . $db->quoteName('#__jeproshop_product_shop') . " AS product_shop ON (product_shop.";
                $queryLeftJoin .= $db->quoteName('product_id')  . " = product." . $db->quoteName('product_id') . ") ";
                $queryWhereClause = " WHERE product_shop." . $db->quoteName('shop_id') . " = " . $currentShopId;

                $attributesJoinAdded = false;

                // Add the conditions
                foreach ($conditionGroup as $conditionId => $condition) {
                    if ($condition->type == 'attribute') {
                        if (!$attributesJoinAdded) {
                            $querySelect .= ", product_attribute." . $db->quoteName('product_attribute_id');
                            $queryLeftJoin .=  " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
                            $queryLeftJoin .= " ON (product_attribute." . $db->quoteName('product_id') . " = product." ;
                            $queryLeftJoin .= $db->quoteName('product_id') . ") " . JeproshopShopModelShop::addSqlAssociation('product_attribute', false);

                            $attributesJoinAdded = true;
                        }

                        /*$queryLeftJoin .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination ";
                        $queryLeftJoin .= " ON (product_attribute_combination.", 'pac'.(int)$id_condition, 'pa.`id_product_attribute` = pac'.(int)$id_condition.'.`id_product_attribute`')
                            ->where('pac'.(int)$id_condition.'.`id_attribute` = '.(int)$condition['value']); */
                    } elseif ($condition->type == 'manufacturer') {
                        $queryWhereClause .= " AND product." . $db->quoteName('manufacturer_id') . " = " . (int)$condition->value;
                    } elseif ($condition->type == 'category') {
                        /*$queryLeftJoin .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_category') . " AS product_category ON product_category.";
                        $queryLeftJoin .=  'cp'.(int)$id_condition, 'p.`id_product` = cp'.(int)$id_condition.'.`id_product`')
                            ->where('cp'.(int)$id_condition.'.id_category = '.(int)$condition['value']); */
                    } elseif ($condition->type == 'supplier') {
                        /*$queryWhereClause .= " AND EXISTS( SELECT product_supplier'.(int)$id_condition.'`.`id_product`
							FROM
								`'._DB_PREFIX_.'product_supplier` `ps'.(int)$id_condition.'`
							WHERE
								`p`.`id_product` = `ps'.(int)$id_condition.'`.`id_product`
								AND `ps'.(int)$id_condition.'`.`id_supplier` = '.(int)$condition['value'].'
						)');*/
                    } elseif ($condition->type == 'feature') {
                        /*$query->leftJoin('feature_product', 'fp'.(int)$id_condition, 'p.`id_product` = fp'.(int)$id_condition.'.`id_product`')
                            ->where('fp'.(int)$id_condition.'.`id_feature_value` = '.(int)$condition['value']);*/
                    }
                }

                // Products limitation
                if ($products && count($products)) {
                    //$query->where('p.`id_product` IN ('.implode(', ', array_map('intval', $products)).')');
                }

                // Force the column id_product_attribute if not requested
                if (!$attributesJoinAdded) {
                    $querySelect .= ", NULL AS " . $db->quoteName('product_attribute_id');
                }

                $query = $querySelect . $queryFrom .  $queryLeftJoin  . $queryWhereClause;
                $db->setQuery($query);

                $result = array_merge($result, $db->loadObjectList());
            }
        } else {
            // All products without conditions
            if ($products && count($products)) {
                /*$query = new DbQuery();
                $query->select('p.`id_product`')
                    ->select('NULL as `id_product_attribute`')
                    ->from('product', 'p')
                    ->leftJoin('product_shop', 'ps', 'p.`id_product` = ps.`id_product`')
                    ->where('ps.id_shop = '.(int)$current_shop_id);
                $query->where('p.`id_product` IN ('.implode(', ', array_map('intval', $products)).')');
                $result = Db::getInstance()->executeS($query); */
            } else {
                $result = array(array('id_product' => 0, 'id_product_attribute' => null));
            }
        }

        return $result;
    }
}