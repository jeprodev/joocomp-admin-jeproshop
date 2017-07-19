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


class JeproshopTools {
    protected static $file_exists_cache = array();
    protected static $_forceCompile;
    protected static $_caching;
    protected static $_user_plate_form;
    protected static $_user_browser;

    protected static $_cache_nb_media_servers = null;

    /**
     * Check for date validity
     *
     * @param string $date Date to validate
     * @return boolean Validity is ok or not
     */
    public static function isDate($date){
        $matches = NULL;
        if (!preg_match('/^([0-9]{4})-((?:0?[0-9])|(?:1[0-2]))-((?:0?[0-9])|(?:[1-2][0-9])|(?:3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $matches)){
            return false;
        }
        return checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }

    /**
     * Check for e-mail validity
     *
     * @param string $email e-mail address to validate
     * @return boolean Validity is ok or not
     */
    public static function isEmail($email){
        return !empty($email) && preg_match(JeproshopTools::cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z\p{L}0-9]+$/ui'), $email);
    }

    /**
     * Display date regarding to language preferences
     *
     * @param $date
     * @param null $full
     * @return string Date
     * @throws JException
     */
    public static function dateFormat($date, $full = NULL){
        return JeproshopTools::displayDate($date, $full);
    }

    /**
     * Display date regarding to language preferences
     *
     * @param string $date Date to display format UNIX
     * @param boolean $full With time or not (optional)
     * @return string Date
     * @throws JException
     */
    public static function displayDate($date, $full = false){
        if(!$date || !($time = strtotime($date))){
            return $date;
        }

        if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00'){
            return '';
        }

        if (!JeproshopTools::isDate($date) || !JeproshopTools::isBool($full)){
            throw new JException('Invalid date');
        }
        $context = JeproshopContext::getContext();
        $date_format = ($full ? $context->language->date_format_full : $context->language->date_format_lite);
        return date($date_format, $time);
    }

    public static function dateDays()
    {
        $tab = array();
        for ($i = 1; $i != 32; $i++)
            $tab[] = $i;
        return $tab;
    }

    public static function dateMonths()
    {
        $tab = array();
        for ($i = 1; $i != 13; $i++)
            $tab[$i] = date('F', mktime(0, 0, 0, $i, date('m'), date('Y')));
        return $tab;
    }

    public static function dateYears(){
        $tab = array();
        for ($i = date('Y'); $i >= 1900; $i--)
            $tab[] = $i;
        return $tab;
    }

    public static function displayAddressDetail($address, $new_line = ''){

    }


    public static function escape($text){
        return $text;
    }

    public static function getShopDomain($http = false, $entities = false){
        
    }
    
    public static function getShopSslDomain($http = false, $entities = false){
        
    }

    public static function displayWarning($message){
        JError::raiseWarning($message);
    }

    /**
     * Check for configuration key validity
     *
     * @param $settingName
     * @return boolean Validity is ok or not
     */
    public static function isSettingName($settingName){
        return preg_match('/^[a-zA-Z_0-9-]+$/', $settingName);
    }

    public static function displayError($message, $key = null){
        
    }

    /**
     * verify object validity
     * @param object $object element to verify
     * @param $key
     * @return boolean true ro false
     */
    public static function isLoadedObject($object, $key){
        $is_object = is_object($object);
        if($is_object){
            $object_id = $object->{$key};
            return $is_object && ($object_id);
        }else{
            return FALSE;
        }
    }

    public static function getValue($key, $default){
        $inputs = JFactory::getApplication()->input;
        return $inputs->get($key, $default);
    }

    /**
     *
     * @return bool true if php-cli is used
     **/
    public static function isPHPCLI(){
        return (defined('STDIN') || (strtolower(php_sapi_name()) == 'cli' && (!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR']))));
    }

    /**
     * Check for an integer validity (unsigned)
     *
     * @param integer $value Integer to validate
     * @return boolean Validity is ok or not
     **/
    public static function isUnsignedInt($value){
        return (preg_match('#^[0-9]+$#', (string)$value) && $value < 4294967296 && $value >= 0);
    }

    /**
     * Checks for integer validity
     * @param int $value integer to validate
     * @return boolean
     */
    public static function isInt($value){
        return((string)(int)$value === (string)$value || $value === false);
    }

    /**
     * return converted price
     * @param $price
     * @param null $currency
     * @param bool $to_currency
     * @param JeproshopContext $context
     * @return float
     */
    public static function convertPrice($price, $currency = null, $to_currency = true, JeproshopContext $context = null){
        static $default_currency = null;
        if($default_currency === null){
            $default_currency = (int)JeproshopSettingModelSetting::getValue('default_currency');
        }
        if(!$context){ $context = JeproshopContext::getContext(); }

        if($currency === null){
            $currency = $context->currency;
        }elseif(is_numeric($currency)){
            $currency = JeproshopCurrencyModelCurrency::getCurrencyInstance($currency);
        }
        $currency_id = (is_object($currency) ? $currency->currency_id : $currency['currency_id']);
        $conversion_rate = (is_object($currency) ? $currency->conversion_rate : $currency['conversion_rate']);

        if($currency_id != $default_currency){
            if($to_currency) {$price *= $conversion_rate; }
            else { $price /= $conversion_rate; }
        }
        return $price;
    }

    /**
     * Return price with currency sign for a given product
     *
     * @param float $price Product price
     * @param object|array $currency Current currency (object, id_currency, NULL => context currency)
     * @param bool $no_utf8
     * @param JeproshopContext $context
     * @return string Price correctly formatted (sign, decimal separator...)
     */
    public static function displayPrice($price, $currency = null, $no_utf8 = false, JeproshopContext $context = null){
        if (!is_numeric($price)){ return $price; }

        if (!$context){ $context = JeproshopContext::getContext(); }

        if ($currency === null){
            $currency = $context->currency;
        }elseif (is_int($currency)){
            // if you modified this function, don't forget to modify the Javascript function formatCurrency (in Tools.js)
            $currency = JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$currency);
        }

        if (is_object($currency)){
            $c_char = $currency->sign;
            $c_format = $currency->format;
            $c_decimals = (int)$currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION;
            $c_blank = $currency->blank;
        }else{
            return false;
        }
        $blank = ($c_blank ? ' ' : '');
        $ret = 0;
        if (($is_negative = ($price < 0))){ $price *= -1; }

        $price = JeproshopTools::roundPrice($price, $c_decimals);

        /**
         * If the language is RTL and the selected currency format contains spaces as thousands separator
         * then the number will be printed in reverse since the space is interpreted as separating words.
         * To avoid this we replace the currency format containing a space with the one containing a comma (,) as thousand
         * separator when the language is RTL.
         *
         * TODO: This is not ideal, a currency format should probably be tied to a language, not to a currency.
         */
        if(($c_format == 2) && ($context->language->is_rtl == 1)){
            $c_format = 4;
        }

        switch ($c_format){
            /* X 0,000.00 */
            case 1:
                $ret = $c_char.$blank.number_format($price, $c_decimals, '.', ',');
                break;
            /* 0 000,00 X*/
            case 2:
                $ret = number_format($price, $c_decimals, ',', ' ').$blank.$c_char;
                break;
            /* X 0.000,00 */
            case 3:
                $ret = $c_char.$blank.number_format($price, $c_decimals, ',', '.');
                break;
            /* 0,000.00 X */
            case 4:
                $ret = number_format($price, $c_decimals, '.', ',').$blank.$c_char;
                break;
            /* X 0'000.00  Added for the switzerland currency */
            case 5:
                $ret = $c_char.$blank.number_format($price, $c_decimals, '.', "'");
                break;
        }
        if ($is_negative){
            $ret = '-'.$ret;
        }

        if ($no_utf8){
            return str_replace('�', chr(128), $ret);
        }
        return $ret;
    }

    public static function roundPrice($value, $precision = 0){
        static $method = null;
        if($method == null){
            $method = (int)JeproshopSettingModelSetting::getValue('price_round_mode');
        }
        if($method == COM_JEPROSHOP_ROUND_UP_PRICE){
            return JeproshopTools::priceCeil($value, $precision);
        }elseif($method == COM_JEPROSHOP_ROUND_DOWN_PRICE){
            return JeproshopTools::priceFloor($value, $precision);
        }
        return round($value, $precision);
    }

    public static function priceCeil($value, $precision = 0){
        $precision_factor = $precision == 0 ? 1 : pow(10, $precision);
        $tmp = $value * $precision_factor;
        $tmp2 = (string)$tmp;
        // If the current value has already the desired precision
        if(strpos($tmp2, '.') == false){ return $value; }
        if($tmp2[strlen($tmp2) - 1] == 0){  return $value; }

        return ceil($tmp) / $precision_factor;
    }

    public static function priceFloor($value, $precision = 0){
        $precision_factor = $precision == 0 ? 1 : pow(10, $precision);
        $tmp = $value * $precision_factor;
        $tmp2 = (string)$tmp;

        // If the current value has already the desired precision
        if(strpos($tmp2, '.') == false){
            return $value;
        }
        if($tmp2[strlen($tmp2) - 1] == 0){
            return $value;
        }
        return floor($tmp) / $precision_factor;
    }

    /**
     * getOctet allow to gets the value of a configuration option in octet
     *
     * @since 1.5.0
     * @param $option
     * @return int the value of a configuration option in octet
     */
    public static function getOctets($option){
        if (preg_match('/[0-9]+k/i', $option)){
            return 1024 * (int)$option;
        }
        if (preg_match('/[0-9]+m/i', $option)){
            return 1024 * 1024 * (int)$option;
        }
        if (preg_match('/[0-9]+g/i', $option)){
            return 1024 * 1024 * 1024 * (int)$option;
        }
        return $option;
    }

    public static function getUserBrowser()	{
        if (isset(self::$_user_browser))
            return self::$_user_browser;

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        self::$_user_browser = 'unknown';

        if(preg_match('/MSIE/i',$user_agent) && !preg_match('/Opera/i',$user_agent))
            self::$_user_browser = 'Internet Explorer';
        elseif(preg_match('/Firefox/i',$user_agent))
            self::$_user_browser = 'Mozilla Firefox';
        elseif(preg_match('/Chrome/i',$user_agent))
            self::$_user_browser = 'Google Chrome';
        elseif(preg_match('/Safari/i',$user_agent))
            self::$_user_browser = 'Apple Safari';
        elseif(preg_match('/Opera/i',$user_agent))
            self::$_user_browser = 'Opera';
        elseif(preg_match('/Netscape/i',$user_agent))
            self::$_user_browser = 'Netscape';

        return self::$_user_browser;
    }

    /**
     * Check for table or identifier validity
     * Mostly used in database for ordering : ASC / DESC
     *
     * @param string $way Keyword to validate
     * @return boolean Validity is ok or not
     **/
    public static function isOrderWay($way){
        return ($way === 'ASC' | $way === 'DESC' | $way === 'asc' | $way === 'desc');
    }

    /**
     * Check for table or identifier validity
     * Mostly used in database for ordering : ORDER BY field
     * @param string $order Field to validate
     * @return boolean Validity is ok or not
     **/
    public static function isOrderBy($order){
        return preg_match('/^[a-zA-Z0-9._-]+$/', $order);
    }

    /**
     * Check for boolean validity
     *
     * @param boolean $bool Boolean to validate
     * @return boolean Validity is ok or not
     */
    public static function isBool($bool){
        return $bool === null || is_bool($bool) || preg_match('/^0|1$/', $bool);
    }

    /**
     * Check for date format
     *
     * @param string $date Date to validate
     * @return boolean Validity is ok or not
     */
    public static function isDateFormat($date){
        return (bool)preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[0-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date);
    }


    public static function getOrderFormToken(){
        return 'a';
    }

    public static function checkCategoryToken(){
        return true;
    }

    public static function getCategoryToken(){
        return '';
    }

    public static function getProductToken(){
        return '';
    }

    public static function checkProductToken(){
        return true;
    }

    public static function getAttributeGroupToken(){
        return '';
    }

    public static function checkAttributeGroupToken(){
        return true;
    }

    public static function getAttachmentToken(){
        return '';
    }

    public static function checkAttachmentToken(){
        return true;
    }

    public static function getDiscountToken(){
        return '';
    }

    public static function checkDiscountToken(){
        return true;
    }

    public static function getFeatureToken(){
        return '';
    }

    public static function checkFeatureToken(){
        return true;
    }
    public static function getSupplierToken(){
        return '';
    }

    public static function checkSupplierToken(){
        return true;
    }

    public static function getAddressToken(){
        return '';
    }

    public static function checkAddressToken(){
        return true;
    }

    public static function getCartToken(){
        return '';
    }

    public static function checkCartToken(){
        return true;
    }

    public static function checkCountryToken(){
        return true;
    }

    public static function getCountryToken(){
        return '';
    }

    public static function getCustomerToken(){
        return '';
    }

    public static function checkCustomerToken(){
        return true;
    }
    public static function getCurrencyToken(){
        return '';
    }

    public static function checkCurrencyToken(){
        return true;
    }

    public static function getGroupToken(){
        return '';
    }

    public static function checkGroupToken(){
        return true;
    }

    public static function getTaxToken(){
        return '';
    }

    public static function checkTaxToken(){
        return true;
    }
    
    public static function getDocumentToken(){ return 'a'; }

    public static function checkDocumentToken(){ return true; }
}