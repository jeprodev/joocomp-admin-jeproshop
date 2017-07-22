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

class JeproshopAddressModelAddress extends JeproshopModel{
    public $address_id = null;

    public $customer_id = null;

    public $manufacturer_id = null;

    public $supplier_id = null;

    public $developer_id = null;

    public $warehouse_id = null;

    public $lang_id;

    public $country_id;

    public $state_id;

    public $country;

    public $alias;

    public $company;

    public $lastname;

    public $firstname;

    public $address1;

    public $address2;

    public $postcode;

    public $city;

    public $other;

    public $phone;

    public $phone_mobile;

    public $vat_number;

    public $dni;

    public $date_add;

    public $date_upd;

    public $deleted = 0;

    protected static $_zonesIds = array();
    protected static $_countriesIds = array();

    public function __construct($addressId = NULL, $langId = NULL) {
        if($langId !== NULL){
            $this->lang_id = JeproshopLanguageModelLanguage::getLanguage($langId) !== FALSE ? (int)$langId : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($addressId){
            //Load address from database if address id is provided
            $cacheKey = 'jeproshop_address_model_' . $addressId . '_' . $langId;
            if(!JeproshopCache::isStored($cacheKey)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_address') . " AS address ";
                $query .= " WHERE address.address_id = " . (int)$addressId;

                $db->setQuery($query);
                $address_data = $db->loadObject();
                if($address_data){
                    JeproshopCache::store($cacheKey, $address_data);
                }
            }  else {
                $address_data = JeproshopCache::retrieve($cacheKey);
            }

            if($address_data){
                $address_data->address_id = $addressId;
                foreach($address_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        if($this->address_id){
            $this->country = JeproshopCountryModelCountry::getCountryNameByCountryId($langId, $this->country_id);
        }
    }

    /**
     * Initialize an address corresponding to the specified id address or if empty to the
     * default shop configuration
     *
     * @param int $addressId
     * @return JeproshopAddressModelAddress address
     */
    public static function initialize($addressId = null){
        //if an addressId has been specified retrieve the address
        if($addressId){
            $address = new JeproshopAddressModelAddress($addressId);

            if(!JeproshopTools::isLoadedObject($address, 'address_id')){
                JError::raiseError(500, JText::_('COM_JEPROSHOP_INVALID_ADDRESS_MESSAGE'));
            }
        }else{
            // Set the default address
            $address = new JeproshopAddressModelAddress();
            $address->country_id = (int)  JeproshopContext::getContext()->country->country_id;
            $address->state_id = 0;
            $address->postcode = 0;
        }
        return $address;
    }

    public function getAddressList(){
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');
        $context = JeproshopContext::getContext();

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');
        $langId = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');
        $orderBy = $app->getUserStateFromRequest($option. $view. '.order_by', 'order_by', 'address_id', 'string');
        $orderWay = $app->getUserStateFromRequest($option. $view. '.order_way', 'order_way', 'ASC' , 'string');

        $use_limit = true;
        if($limit === false){
            $use_limit = false;
        }

        do{
            $query = "SELECT SQL_CALC_FOUND_ROWS address." . $db->quoteName('address_id') . ", address." . $db->quoteName('firstname');
            $query .= ", address." . $db->quoteName('lastname') . ", address." . $db->quoteName('address1') . ", address.";
            $query .= $db->quoteName('postcode') . ", address." . $db->quoteName('city') . ", country_lang." . $db->quoteName('name');
            $query .= " AS country " . " FROM " . $db->quoteName('#__jeproshop_address') . " AS address  LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_country_lang') . "country_lang ON (country_lang." . $db->quoteName('country_id');
            $query .= " = address." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id') . " = " . $langId;
            $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_customer') . " AS customer ON address." . $db->quoteName('customer_id');
            $query .= " = customer." . $db->quoteName('customer_id') . " WHERE address.customer_id != 0 ";
            $query .= JeproshopShopModelShop::addSqlRestriction(JeproshopShopModelShop::SHARE_CUSTOMER, 'customer') . " ORDER BY ";
            $query .= ((str_replace('`', '', $orderBy) == 'address_id') ? "address." : "") . $orderBy . " " . $orderWay;

            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($use_limit == true) ? " LIMIT " . (int)$limitStart . ", " . (int)$limit : " ");

            $db->setQuery($query);
            $addresses = $db->loadObjectList();

            if($use_limit == true){
                $limitStart = (int)$limitStart -(int)$limit;
                if($limitStart < 0){ break; }
            }else{ break; }
        }while(empty($addresses));

        $this->pagination = new JPagination($total, $limitStart, $limit);
        return $addresses;
    }
}


class JeproshopAddressFormatModelAddressFormat extends JeproshopModel {
    /** @var integer */
    public $address_format_id;

    /** @var integer */
    public $country_id;

    /** @var string */
    public $format;

    public static $forbiddenClassList = array(
        'Manufacturer',
        'Supplier');

    /**
     * Returns address format by country if not defined using default country
     *     *
     * @param int $country_id
     * @return String field address format
     */
    public static function getAddressCountryFormat($country_id = 0){
        $country_id = (int)$country_id;

        $tmp_obj = new JeproshopAddressFormatModelAddressFormat();
        $tmp_obj->country_id = $country_id;
        $out = $tmp_obj->getFormat($tmp_obj->country_id);
        unset($tmp_obj);
        return $out;
    }

    /**
     * Returns address format by country
     *
     * @param $countryId
     * @return String field address format
     */
    public function getFormat($countryId) {
        $out = $this->getFormatFromDataBase($countryId);
        if (empty($out))
            $out = $this->getFormatFromDataBase(JeproshopSettingModelSetting::getValue('default_country'));
        return $out;
    }

    protected function getFormatFromDataBase($countryId){
        $cacheKey = 'jeproshop_address_format_get_format_from_data_base_'.$countryId;
        if (!JeproshopCache::isStored($cacheKey)) {
            $db = JFactory::getDBO();

            $query = "SELECT format FROM " . $db->quoteName('#__jeproshop_address_format') . " WHERE " ;
            $query .= $db->quoteName('country_id') . " = " . (int)$countryId;

            $db->setQuery($query);
            $format = $db->loadResult();
            JeproshopCache::store($cacheKey, trim($format));
        }
        return JeproshopCache::retrieve($cacheKey);
    }

    /**
     * Return a list of liable class of the className
     * @param $className
     * @return array
     */
    public static function getLiableClass($className){
        $objectList = array();

        if (class_exists($className))
        {
            $object = new $className();
            $reflect = new ReflectionObject($object);

            // Get all the name object liable to the Address class
            $publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($publicProperties as $property)
            {
                $propertyName = $property->getName();
                if (preg_match('#\w_id#', $propertyName) && strlen($propertyName) > 3)
                {
                    $nameObject = ucfirst(substr($propertyName, 3));
                    if (!in_array($nameObject, self::$forbiddenClassList) &&
                        class_exists($nameObject))
                        $objectList[$nameObject] = new $nameObject();
                }
            }
            unset($object);
            unset($reflect);
        }
        return $objectList;
    }

}