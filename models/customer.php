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

    /**
     * Return customer addresses
     *
     * @param integer $langId Language ID
     * @return array Addresses
     */
    public function getAddresses($langId){
        $share_order = (bool)JeproshopContext::getContext()->shop->getShopGroup()->share_order;
        $cacheKey = 'jeproshop_customer_getAddresses_'.(int)$this->customer_id.'-'.(int)$langId . '_' . $share_order;
        if (!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();
            $query = "SELECT DISTINCT address.*, country_lang." . $db->quoteName('name') . " AS country, stat.name AS ";
            $query .= "state, stat.iso_code AS state_iso FROM " . $db->quoteName('#__jeproshop_address') . " AS address";
            $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_country') . " AS country ON (address.";
            $query .= $db->quoteName('country_id') . " = country." . $db->quoteName('country_id') . ") LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country." . $db->quoteName('country_id');
            $query .= " = country_lang." . $db->quoteName('country_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_state');
            $query .= " AS stat ON (stat." . $db->quoteName('state_id') . " = address." . $db->quoteName('state_id') . ") ";
            $query .= ($share_order ? "" : JeproshopShopModelShop::addSqlAssociation('country')) . " WHERE " . $db->quoteName('lang_id');
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

}