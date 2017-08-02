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

require_once 'customer_thread.php';

class JeproshopCustomerModelCustomer extends JeproshopModel {
    public $customer_id;

    public $shop_id;

    public $shop_group_id;

    public $secure_key;

    public $note;

    public $gender_id = 0;

    public $default_group_id;

    public $lang_id;

    public $title;

    public $lastname;

    public $firstname;

    public $birthday = null;

    public $email;

    public $newsletter;

    public $ip_registration_newsletter;

    public $newsletter_date_add;

    public $optin;

    public $is_guest;

    public $website;

    public $company;

    public $siret;

    public $ape;

    public $published;

    public $state_id;

    public $postcode;

    public $geolocation_country_id;

    public $date_add;
    public $date_upd;

    protected static $_customer_groups = array();
    protected static $_customer_has_address = array();

    public function __construct($customerId = NULL){
        if($customerId){
            $cacheKey = 'jeproshop_customer_model_' . $customerId . ( $this->shop_id ? '_' . $this->shop_id : '');
            if(!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_customer') . " AS customer ";


                /** Get shop information **/
                if(JeproshopShopModelShop::isTableAssociated('order')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_order_shop') . " AS order_shop ON (";
                    $query .= "ord.order_id = order_shop.order_id AND order_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE customer.customer_id = " . (int)$customerId ;

                $db->setQuery($query);
                $customerData = $db->loadObject();

                if($customerData){
                    JeproshopCache::store($cacheKey, $customerData);
                }
            }else{
                $customerData = JeproshopCache::retrieve($cacheKey);
            }

            if($customerData){
                $customerData->customer_id = $customerId;
                foreach($customerData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
        $this->default_group_id = JeproshopSettingModelSetting::getValue('customer_group');
    }

    /**
     * Return customer addresses
     *
     * @param integer $langId Language ID
     * @return array Addresses
     */
    public function getAddresses($langId){
        $shareOrder = (bool)JeproshopContext::getContext()->shop->getShopGroup()->share_order;
        $cacheKey = 'jeproshop_customer_getAddresses_'.(int)$this->customer_id.'-'.(int)$langId . '_' . $shareOrder;
        if (!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();
            $query = "SELECT DISTINCT address.*, country_lang." . $db->quoteName('name') . " AS country, stat.name AS ";
            $query .= "state, stat.iso_code AS state_iso FROM " . $db->quoteName('#__jeproshop_address') . " AS address";
            $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_country') . " AS country ON (address.";
            $query .= $db->quoteName('country_id') . " = country." . $db->quoteName('country_id') . ") LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country." . $db->quoteName('country_id');
            $query .= " = country_lang." . $db->quoteName('country_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_state');
            $query .= " AS stat ON (stat." . $db->quoteName('state_id') . " = address." . $db->quoteName('state_id') . ") ";
            $query .= ($shareOrder ? "" : JeproshopShopModelShop::addSqlAssociation('country')) . " WHERE " . $db->quoteName('lang_id');
            $query .= " = " .(int)$langId . " AND " . $db->quoteName('customer_id') . " = " .(int)$this->customer_id . " AND address.";
            $query .= $db->quoteName('deleted') . " = 0";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cacheKey, $result);
        }
        return JeproshopCache::retrieve($cacheKey);
    }

    /**
     * Return several useful statistics about customer
     *
     * @return array Stats
     */
    public function getStats(){
        $db = JFactory::getDBO();

        $query = "SELECT COUNT(" . $db->quoteName('order_id') . ") AS nb_orders, SUM(" . $db->quoteName('total_paid');
        $query .= " / ord." . $db->quoteName('conversion_rate') . ") AS total_orders FROM " . $db->quoteName('#__jeproshop_orders');
        $query .= " AS ord WHERE ord." . $db->quoteName('customer_id') . " = " . (int)$this->customer_id . " AND ord.valid = 1";

        $db->setQuery($query);
        $result = $db->loadObject();

        $query = "SELECT MAX(connection." . $db->quoteName('date_add') . ") AS last_visit FROM " .$db->quoteName('#__jeproshop_guest');
        $query .= " AS guest LEFT JOIN " . $db->quoteName('#__jeproshop_connection') . " AS connection ON connection.guest_id = guest.";
        $query .= "guest_id WHERE guest." . $db->quoteName('customer_id') . " = " .(int)$this->customer_id ;

        $db->setQuery($query);
        $result2 = $db->loadObject();

        $query = "SELECT (YEAR(CURRENT_DATE)-YEAR(customer." . $db->quoteName('birthday') . ")) - (RIGHT(CURRENT_DATE, 5) < RIGHT(customer.";
        $query .= $db->quoteName('birthday') . ", 5)) AS age FROM " . $db->quoteName('#__jeproshop_customer') . " AS customer WHERE customer.";
        $query .= $db->quoteName('customer_id') . " = " . (int)$this->customer_id;

        $db->setQuery($query);
        $result3 = $db->loadObject();

        $result->last_visit =  isset($result2) ? $result2->last_visit : '';
        $result->age = (isset($result3) &&($result3->age != date('Y')) ? $result3->age : '--');
        return $result;
    }

    public function getCustomerList(){
        jimport('joomla.html.pagination');
        $context = JeproshopContext::getContext();

        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');
        $context->controller->default_form_language = $context->language->lang_id;

        $deleted = $app->getUserStateFromRequest($option. $view. '.deleted', 'deleted', 0, 'int');
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');

        /* Manage default params values */
        $use_limit = true;
        if ($limit === false)
            $use_limit = false;

        do {
            $query = "SELECT SQL_CALC_FOUND_ROWS customer."  .  $db->quoteName('lastname'). ", customer." . $db->quoteName('firstname');
            $query .= ", customer." . $db->quoteName('customer_id') .  ", customer."  . $db->quoteName('email') . ", customer.published AS ";
            $query .= "published, customer." . $db->quoteName('newsletter')  . ", customer." . $db->quoteName('optin') . ", customer.";
            $query .= $db->quoteName('date_add') . ", customer." . $db->quoteName('title') . ", shop.shop_name AS shop_name, ( SELECT SUM(";
            $query .= "total_paid_tax_excl / conversion_rate) FROM "  . $db->quoteName('#__jeproshop_orders')  . " AS ord WHERE ord.customer_id";
            $query .= " = customer.customer_id AND ord.shop_id IN (" . implode(',', JeproshopShopModelShop::getContextListShopIds()) . ") AND ";
            $query .= "customer.published  = 1 ) AS total_spent, ( SELECT connection." . $db->quoteName('date_add') . " FROM " . $db->quoteName('#__jeproshop_guest');
            $query .= " AS guest LEFT JOIN " . $db->quoteName('#__jeproshop_connection') . " AS connection ON (connection." . $db->quoteName('guest_id') . " = guest.";
            $query .= $db->quoteName('guest_id') . ") WHERE guest." . $db->quoteName('customer_id') . " = customer." . $db->quoteName('customer_id') ;
            $query .= " ORDER BY connection." . $db->quoteName('date_add') . " DESC LIMIT 1) AS connect ";
            if (JeproshopSettingModelSetting::getValue('enable_b2b_mode')) {
                $query .= ", customer." . $db->quoteName('company') . ", customer." . $db->quoteName('website');
            }
            $query .= "  FROM " . $db->quoteName('#__jeproshop_customer') . " AS customer LEFT JOIN " . $db->quoteName('#__jeproshop_shop');
            $query .= " AS shop ON(customer." . $db->quoteName('shop_id') . " = shop." . $db->quoteName('shop_id') . ") WHERE 1 ";
            $query .= JeproshopShopModelShop::addSqlRestriction(JeproshopShopModelShop::SHARE_CUSTOMER, 'customer');
            $query .= ($deleted ? " AND customer." . $db->quoteName('deleted') . " = 0 " : " ");


            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= ( $use_limit ? "LIMIT " . $limitstart . ", " . $limit : "");
            $db->setQuery($query);
            $customers = $db->loadObjectList();

            if($use_limit == true){
                $limitstart = (int)$limitstart -(int)$limit;
                if($limitstart < 0){ break; }
            }else{ break; }
        }while(empty($customers));

        $this->pagination = new JPagination($total, $limitstart, $limit);

        return $customers;
    }

    /**
     * Specify if a customer already in base
     *
     * @param $customer_id Customer id
     * @return boolean
     */
    // DEPRECATED
    public function customerIdExists($customer_id){
        return JeproshopCustomerModelCustomer::customerIdExistsStatic((int)$customer_id);
    }

    public function isGuest(){
        return (bool)$this->is_guest;
    }

    public static function customerIdExistsStatic($customerId){
        $cache_id = 'jeproshop_customer_exists_id_'.(int)$customerId;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('customer_id') . " FROM " . $db->quoteName('#__jeproshop_customer');
            $query .= " AS customer WHERE customer." . $db->quoteName('customer_id') . " = " . (int)$customerId;;

            $db->setQuery($query);
            $result = $db->loadResult();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    public function getGroups(){
        return JeproshopCustomerModelCustomer::getStaticGroups((int)$this->customer_id);
    }

    public static function getStaticGroups($customerId){
        if (!JeproshopGroupModelGroup::isFeaturePublished()){
            return array(JeproshopSettingModelSetting::getValue('customer_group'));
        }

        if($customerId == 0){
            self::$_customer_groups[$customerId] = array((int)  JeproshopSettingModelSetting::getValue('unidentified_group'));
        }

        if (!isset(self::$_customer_groups[$customerId])){
            self::$_customer_groups[$customerId] = array();
            $db = JFactory::getDBO();

            $query = "SELECT customer_group." . $db->quoteName('group_id') . " FROM " . $db->quoteName('#__jeproshop_customer_group');
            $query .= " AS customer_group WHERE customer_group." . $db->quoteName('customer_id') . " = " .(int)$customerId;
            $db->setQuery($query);
            $result = $db->loadObjectList();
            foreach ($result as $group){
                self::$_customer_groups[$customerId][] = (int)$group->group_id;
            }
        }
        return self::$_customer_groups[$customerId];
    }

    public function getBoughtProducts(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord LEFT JOIN " . $db->quoteName('#__jeproshop_order_detail') . " AS order_detail ON ord.order_id";
        $query .= " = order_detail.order_id WHERE ord.valid = 1 AND ord." . $db->quoteName('customer_id') . " = " . (int)$this->customer_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getLastConnections(){
        $db = JFactory::getDBO();

        $query = "SELECT connection.date_add, COUNT(connection_page.page_id) AS pages, TIMEDIFF(MAX(connection_page.time_end), connection.date_add) as time, http_referrer,";
        $query .= " INET_NTOA(ip_address) as ip_address FROM " . $db->quoteName('#__jeproshop_guest') . " AS g LEFT JOIN "  . $db->quoteName('#__jeproshop_connection') . " AS ";
        $query .= " connection ON connection.guest_id = g.guest_id LEFT JOIN " . $db->quoteName('#__jeproshop_connection_page') . " AS connection_page  ON connection.";
        $query .= "connection_id = connection_page.connection_id WHERE g." . $db->quoteName('customer_id') . " = " . (int)$this->customer_id . " GROUP BY connection.";
        $query .= $db->quoteName('connection_id') . " ORDER BY connection.date_add DESC LIMIT 10 ";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Check if e-mail is already registered in database
     *
     * @param string $email e-mail
     * @param $returnId boolean
     * @param $ignoreGuest boolean, to exclude guest customer
     * @return Customer ID if found, false otherwise
     */
    public static function customerExists($email, $returnId = false, $ignoreGuest = true){
        if (!JeproshopTools::isEmail($email)){
            if (defined('COM_JEPROSHOP_DEV_MODE') && COM_JEPROSHOP_DEV_MODE)
                die (Tools::displayError('Invalid email'));
            else
                return false;
        }
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('customer_id') . " FROM " . $db->quoteName('#__jeproshop_customer');
        $query .= " WHERE " . $db->quoteName('email') . " = " . $db->quote($db->escape($email));
        $query .= JeproshopShopModelShop::addSqlRestriction(JeproshopShopModelShop::SHARE_CUSTOMER);
        $query .= ($ignoreGuest ? " AND " . $db->quoteName('is_guest') . " = 0" : "");

        $db->setQuery($query);
        $result = $db->loadObject();

        if ($returnId)
            return $result->customer_id;
        return isset($result->customer_id);
    }

    public function getInterestedProducts(){
        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT cart_product.product_id, cart.cart_id, cart.shop_id, cart_product.shop_id ";
        $query .= " AS cart_product_shop_id FROM " . $db->quoteName('#__jeproshop_cart_product') . " AS cart_product";
        $query .= " JOIN " . $db->quoteName('#__jeproshop_cart') . " AS cart ON (cart.cart_id = cart_product.cart_id) ";
        $query .= "JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON (cart_product.product_id = product.";
        $query .= "product_id) WHERE cart.customer_id = " . (int)$this->customer_id . " AND cart_product.product_id";
        $query .= " NOT IN ( SELECT product_id FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord JOIN ";
        $query .= $db->quoteName('#__jeproshop_order_detail') . " AS ord_detail ON (ord.order_id = ord_detail.order_id ) WHERE ";
        $query .= "ord.valid = 1 AND ord.customer_id = " . (int)$this->customer_id . ")";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function searchCustomerByValue($fieldValue){
        $db = JFactory::getDBO();
        $sql = '%' . $fieldValue . '%';
        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_customer') . " WHERE ( " . $db->quoteName('email') ;
        $query .= " LIKE " . $db->quote($sql) . " OR " . $db->quoteName('customer_id') . " LIKE " . $db->quote($sql);
        $query .= " OR "  . $db->quoteName('lastname') . " LIKE " . $db->quote($sql) ." OR " . $db->quoteName('firstname');
        $query .= " LIKE " . $db->quote($sql). " ) " ; // . JeproshopShopModelShop::addSqlRestriction(JeproshopShopModelShop::SHARE_CUSTOMER);
        $db->setQuery($query);
        return $db->loadObjectList();
    }

}