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

    /**
     * Specify if an address is already in base
     *
     * @param int $addressId Address id
     * @return boolean
     */
    public static function addressExists($addressId){
        $key = 'address_exists_'.(int)$addressId;
        if (!JeproshopCache::isStored($key)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('address_id') . " FROM " . $db->quoteName('#__jeproshop_address');
            $query .= " AS address WHERE address." . $db->quoteName('address_id') . " = " . (int)$addressId;

            $db->setQuery($query);
            $addressId = $db->loadResult();
            JeproshopCache::store($key, (bool)$addressId);
        }
        return JeproshopCache::retrieve($key);
    }

    public static function getCountryAndState($addressId) {
        if (isset(self::$_countriesIds[$addressId]))
            return self::$_countriesIds[$addressId];
        if ($addressId) {
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('country_id') . ", " . $db->quoteName('state_id') . ", " . $db->quoteName('vat_number') . ", " . $db->quoteName('postcode') . " FROM ";
            $query .= $db->quoteName('#__jeproshop_address') . " WHERE " . $db->quoteName('address_id') . " = " . (int)$addressId;
            $db->setQuery($query);
            $result = $db->loadObject();
        }else
            $result = false;
        self::$_countriesIds[$addressId] = $result;
        return $result;
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

    const _CLEANING_REGEX_ = '#([^\w:_]+)#i';

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

    /**
     * Return a data array containing ordered, formatValue and object fields
     * @param $address
     * @return array
     */
    public static function getFormattedLayoutData($address){
        $layoutData = array();

        if ($address && $address instanceof JeproshopAddressModelAddress){
            $layoutData['ordered'] = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields((int)$address->country_id);
            $layoutData['format'] = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($address, $layoutData['ordered']);
            $layoutData['object'] = array();

            $reflect = new ReflectionObject($address);
            $public_properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($public_properties as $property)
                if (isset($address->{$property->getName()}))
                    $layoutData['object'][$property->getName()] = $address->{$property->getName()};
        }
        return $layoutData;
    }

    /**
     * Returns address format fields in array by country
     *
     * @param int $country_id
     * @param bool $split_all
     * @param bool $cleaned
     * @return Array String field address format
     */
    public static function getOrderedAddressFields($country_id = 0, $split_all = false, $cleaned = false){
        $out = array();
        $field_set = explode("\n", JeproshopAddressFormatModelAddressFormat::getAddressCountryFormat($country_id));
        foreach ($field_set as $field_item){
            if ($split_all){
                $keyList = array();
                if ($cleaned){
                    $keyList = ($cleaned) ? preg_split(self::_CLEANING_REGEX_, $field_item, -1, PREG_SPLIT_NO_EMPTY) : explode(' ', $field_item);
                }
                foreach ($keyList as $word_item){ $out[] = trim($word_item); }
            } else{
                $out[] = ($cleaned) ? implode(' ', preg_split(self::_CLEANING_REGEX_, trim($field_item), -1, PREG_SPLIT_NO_EMPTY)) : trim($field_item);
            }
        }
        return $out;
    }

    /***
     * Returns the formatted fields with associated values
     *
     * @param $address is an instantiated Address object
     * @param $addressFormat is the format
     * @param null $langId
     * @return float Array
     */
    public static function getFormattedAddressFieldsValues($address, $addressFormat, $langId = null){
        if (!$langId) {
            $langId = JeproshopContext::getContext()->language->lang_id;
        }
        $tab = array();
        $temporaryObject = array();

        // Check if $address exist and it's an instantiate object of Address
        if ($address && ($address instanceof JeproshopAddressFormatModelAddressFormat))
            foreach ($addressFormat as $line)
            {
                if (($keyList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY)) && is_array($keyList))
                {
                    foreach ($keyList as $pattern)
                        if ($associateName = explode(':', $pattern))
                        {
                            $totalName = count($associateName);
                            if ($totalName == 1 && isset($address->{$associateName[0]}))
                                $tab[$associateName[0]] = $address->{$associateName[0]};
                            else
                            {
                                $tab[$pattern] = '';

                                // Check if the property exist in both classes
                                if (($totalName == 2) && class_exists($associateName[0]) &&
                                    property_exists($associateName[0], $associateName[1]) &&
                                    property_exists($address, 'id_'.strtolower($associateName[0])))
                                {
                                    $idFieldName = 'id_'.strtolower($associateName[0]);

                                    if (!isset($temporaryObject[$associateName[0]]))
                                        $temporaryObject[$associateName[0]] = new $associateName[0]($address->{$idFieldName});
                                    if ($temporaryObject[$associateName[0]])
                                        $tab[$pattern] = (is_array($temporaryObject[$associateName[0]]->{$associateName[1]})) ?
                                            ((isset($temporaryObject[$associateName[0]]->{$associateName[1]}[$langId])) ?
                                                $temporaryObject[$associateName[0]]->{$associateName[1]}[$langId] : '') :
                                            $temporaryObject[$associateName[0]]->{$associateName[1]};
                                }
                            }
                        }
                    JeproshopAddressFormatModelAddressFormat::setOriginalDisplayFormat($tab, $line, $keyList);
                }
            }
        JeproshopAddressFormatModelAddressFormat::cleanOrderedAddress($addressFormat);
        // Free the instantiate objects
        foreach ($temporaryObject as &$object)
            unset($object);
        return $tab;
    }

    /**
     ** Cleaned the layout set by the user
     * @param $orderedAddressField
     */
    public static function cleanOrderedAddress(&$orderedAddressField){
        foreach ($orderedAddressField as &$line){
            $cleanedLine = '';
            if (($keyList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY))){
                foreach ($keyList as $key)
                    $cleanedLine .= $key.' ';
                $cleanedLine = trim($cleanedLine);
                $line = $cleanedLine;
            }
        }
    }

    /**
     ** Set the layout key with the liable value
     ** example : (firstname) =>
     **         : (firstname-lastname) =>
     * @param $formattedValueList
     * @param $currentLine
     * @param $currentKeyList
     */
    protected static function setOriginalDisplayFormat(&$formattedValueList, $currentLine, $currentKeyList){
        if ($currentKeyList && is_array($currentKeyList)) {
            if ($originalFormattedPatternList = explode(' ', $currentLine)) {
                // Foreach the available pattern
                foreach ($originalFormattedPatternList as $patternNum => $pattern) {
                    // Var allows to modify the good formatted key value when multiple key exist into the same pattern
                    $mainFormattedKey = '';

                    // Multiple key can be found in the same pattern
                    foreach ($currentKeyList as $key) {
                        // Check if we need to use an older modified pattern if a key has already be matched before
                        $replacedValue = empty($mainFormattedKey) ? $pattern : $formattedValueList[$mainFormattedKey];
                        if (($formattedValue = preg_replace('/' . $key . '/', $formattedValueList[$key], $replacedValue, -1, $count))) {
                            if ($count) {
                                // Allow to check multiple key in the same pattern,
                                if (empty($mainFormattedKey))
                                    $mainFormattedKey = $key;
                                // Set the pattern value to an empty string if an older key has already been matched before
                                if ($mainFormattedKey != $key)
                                    $formattedValueList[$key] = '';
                                // Store the new pattern value
                                $formattedValueList[$mainFormattedKey] = $formattedValue;
                                unset($originalFormattedPatternList[$patternNum]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Generates the full address text
     * @param is|JeproshopAddressModelAddress $address is an instantiate object of Address class
     * @param array $patternRules
     * @param is|string $newLine is a string containing the newLine format
     * @param is|string $separator is a string containing the separator format
     * @param array $style
     * @return string
     */
    public static function generateAddress(JeproshopAddressModelAddress $address, $patternRules = array(), $newLine = "\r\n", $separator = ' ', $style = array()){
        $addressFields = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields($address->country_id);
        $addressFormattedValues = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($address, $addressFields);

        $addressText = '';
        foreach ($addressFields as $line)
            if (($patternsList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY)))
            {
                $tmpText = '';
                foreach ($patternsList as $pattern)
                    if ((!array_key_exists('avoid', $patternRules)) ||
                        (array_key_exists('avoid', $patternRules) && !in_array($pattern, $patternRules['avoid'])))
                        $tmpText .= (isset($addressFormattedValues[$pattern]) && !empty($addressFormattedValues[$pattern])) ?
                            (((isset($style[$pattern])) ?
                                    (sprintf($style[$pattern], $addressFormattedValues[$pattern])) :
                                    $addressFormattedValues[$pattern]).$separator) : '';
                $tmpText = trim($tmpText);
                $addressText .= (!empty($tmpText)) ? $tmpText.$newLine: '';
            }

        $addressText = preg_replace('/'.preg_quote($newLine,'/').'$/i', '', $addressText);
        $addressText = rtrim($addressText, $separator);

        return $addressText;
    }

}