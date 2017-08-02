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
        $domain = JeproshopShopUrlModelShopUrl::getMainShopDomain();
        if(!$domain){
            $domain = JeproshopTools::getHttpHost();
        }

        if($entities) {
            $domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
        }

        if($http){
            $domain = 'http://' . $domain;
        }
        return $domain;
    }
    
    public static function getShopSslDomain($http = false, $entities = false){
        $domain = JeproshopShopUrlModelShopUrl::getMainShopSslDomain();
        if(!$domain){
            $domain = JeproshopTools::getHttpHost();
        }

        if($entities) {
            $domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
        }

        if($http){
            $domain = (JeproshopSettingModelSetting::getValue('ssl_enabled') ? 'https://' : 'http://') . $domain;
        } 
        return $domain;
    }

    public static function getHttpHost($http = false, $entities = false, $ignorePort = false){
        $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
        if ($ignorePort && $pos = strpos($host, ':')) {
            $host = substr($host, 0, $pos);
        }
        if ($entities) {
            $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
        }
        if ($http) {
            $host = (JeproshopSettingModelSetting::getValue('ssl_enabled') ? 'https://' : 'http://') . $host;
        }
        return $host;
    }

    public static function displayWarning($code, $message){
        JError::raiseWarning($code, $message);
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

    /**
     * Change language in cookie while clicking on a flag
     *
     * @param null $cookie
     * @return string iso code
     */
    public static function setCookieLanguage($cookie = null){
        if (!$cookie){
            $cookie = JeproshopContext::getContext()->cookie;
        }
        /* If language does not exist or is disabled, erase it */
        if ($cookie->lang_id){
            $lang = new JeproshopLanguageModelLanguage((int)$cookie->lang_id);
            if (!JeproshopTools::isLoadedObject($lang, 'lang_id') || !$lang->published || !$lang->isAssociatedToShop()){
                $cookie->lang_id = null;
            }
        }

        /* Automatically detect language if not already defined, detect_language is set in Cookie::update **/
        if ((!$cookie->lang_id || isset($cookie->detect_language)) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $array  = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            $string = $array[0];

            if (JeproshopTools::isLanguageCode($string)){
                $lang = JeproshopLanguageModelLanguage::getLanguageByIETFCode($string);
                if (JeproshopTools::isLoadedObject($lang, 'lang_id') && $lang->published && $lang->isAssociatedToShop()){
                    JeproshopContext::getContext()->language = $lang;
                    $cookie->lang_id = (int)$lang->lang_id;
                }
            }
        }

        if (isset($cookie->detect_language)){
            unset($cookie->detect_language);
        }
        /* If language file not present, you must use default language file **/
        if (!$cookie->lang_id || !JeproshopTools::isUnsignedInt($cookie->lang_id)){
            $cookie->lang_id = (int)JeproshopSettingModelSetting::getValue('default_lang');
        }
        $iso = JeproshopLanguageModelLanguage::getIsoById((int)$cookie->lang_id);
        //@include_once(_PS_THEME_DIR_.'lang/'.$iso.'.php');

        return $iso;
    }

    /**
     * Check for language code (ISO) validity
     *
     * @param string $iso_code Language code (ISO) to validate
     * @return boolean Validity is ok or not
     */
    public static function isLanguageIsoCode($iso_code){
        return preg_match('/^[a-zA-Z]{2,3}$/', $iso_code);
    }

    public static function isLanguageCode($s){
        return preg_match('/^[a-zA-Z]{2}(-[a-zA-Z]{2})?$/', $s);
    }

    /**
     * Set cookie currency from POST or default currency
     *
     * @param $cookie
     * @return JeproshopCurrencyModelCurrency object
     */
    public static function setCurrency($cookie){
        $app = JFactory::getApplication();
        if ($app->input->get('SubmitCurrency')){
            $currencyId = $app->input->get('currency_id');
            if (isset($currencyId) && is_numeric($currencyId)){
                $currency = JeproshopCurrencyModelCurrency::getCurrencyInstance($currencyId);
                if (JeproshopTools::isLoadedObject($currency, 'currency_id') && $currency->currency_id && !$currency->deleted && $currency->isAssociatedToShop()){
                    $cookie->currency_id = (int)$currency->currency_id;
                }
            }
        }

        $currency = null;
        if ((int)$cookie->currency_id){
            $currency = JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$cookie->currency_id);
        }

        if (!JeproshopTools::isLoadedObject($currency, 'currency_id') || (bool)$currency->deleted || !(bool)$currency->published){
            $currency = JeproshopCurrencyModelCurrency::getCurrencyInstance(JeproshopSettingModelSetting::getValue('default_currency'));
        }

        $cookie->currency_id = (int)$currency->currency_id;
        if ($currency->currency_id > 0 && $currency->isAssociatedToShop()){
            return $currency;
        }else{
            // get currency from context
            $currency = JeproshopShopModelShop::getEntityIds('currency', JeproshopContext::getContext()->shop->shop_id, true, true);
            if (isset($currency[0]) && $currency[0]->currency_id)
            {
                $cookie->currency_id = $currency[0]->currency_id;
                return JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$cookie->currency_id);
            }
        }
        return $currency;
    }

    /**
     * Sanitize a string
     *
     * @param string $string String to sanitize
     * @param bool $html
     * @internal param bool $full String contains HTML or not (optional)
     * @return string Sanitized string
     */
    public static function safeOutput($string, $html = false) {
        if (!$html)
            $string = strip_tags($string);
        return @JeproshopTools::htmlEntitiesUTF8($string, ENT_QUOTES);
    }

    public static function safePostVars() {
        if (!isset($_POST) || !is_array($_POST))
            $_POST = array();
        else
            $_POST = array_map(array('JeproshopTools', 'htmlEntitiesUTF8'), $_POST);
    }

    public static function htmlEntitiesUTF8($string, $type = ENT_QUOTES)	{
        if (is_array($string)){
            return array_map(array('JeproshopTools', 'htmlEntitiesUTF8'), $string);
        }
        return htmlentities((string)$string, $type, 'utf-8');
    }

    /**
     * Delete unicode class from regular expression patterns
     * @param string $pattern
     * @return pattern
     */
    public static function cleanNonUnicodeSupport($pattern){
        if (!defined('PREG_BAD_UTF8_OFFSET')){
            return $pattern;
        }
        return preg_replace('/\\\[px]\{[a-z]\}{1,2}|(\/[a-z]*)u([a-z]*)$/i', "$1$2", $pattern);
    }

    public static function htmlEntitiesDecodeUTF8($string){
        if (is_array($string)){
            $string = array_map(array('JeproshopTools', 'htmlEntitiesDecodeUTF8'), $string);
            return (string)array_shift($string);
        }
        return html_entity_decode((string)$string, ENT_QUOTES, 'utf-8');
    }

    /**
     * Get the user's journey
     *
     * @param integer $category_id Category ID
     * @param string $path Path end
     * @param bool $link_on_the_item
     * @param string $category_type
     * @param JeproshopContext $context
     * @return string
     * @internal param bool $linkOnTheLastItem Put or not a link on the current category
     * @internal param $string [optional] $categoryType defined what type of categories is used (products or cms)
     */
    public static function getPath($category_id, $path = '', $link_on_the_item = false, $category_type = 'products', JeproshopContext $context = null){
        if (!$context){
            $context = JeproshopContext::getContext();
        }
        $category_id = (int)$category_id;
        if ($category_id == 1)
            return '<span class="navigation_end">'.$path.'</span>';

        $pipe = '>' ; //Configuration::get('PS_NAVIGATION_PIPE');
        if (empty($pipe))
            $pipe = '>';

        $full_path = '';
        if ($category_type === 'products'){
            $interval = JeproshopCategoryModelCategory::getInterval($category_id);
            $root_category_id = $context->shop->getCategoryId();
            $interval_root = JeproshopCategoryModelCategory::getInterval($root_category_id);
            $db = JFactory::getDBO();
            if ($interval){
                $query = "SELECT category.category_id, category_lang.name, category_lang.link_rewrite FROM " . $db->quoteName('#__jeproshop_category');
                $query .= " AS category LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (category_lang.category_id =";
                $query .= "category.category_id" . JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . ")" . JeproshopShopModelShop::addSqlAssociation('category');
                $query .= "	WHERE category.n_left <= " . $interval->n_left . " AND category.n_right >= ".$interval->n_right . "	AND category.n_left >= ";
                $query .= $interval_root->n_left . " AND category.n_right <= " . $interval_root->n_right . " AND category_lang.lang_id = " . (int)$context->language->lang_id;
                $query .= "	AND category.published = 1 AND category.depth_level > " . (int)$interval_root->depth_level . " ORDER BY category.depth_level ASC";

                $db->setQuery($query);
                $categories = $db->loadObjectList();

                $n = 1;
                $n_categories = count($categories);
                foreach ($categories as $category)
                {
                    $full_path .=
                        (($n < $n_categories || $link_on_the_item) ? '<a href="'.JeproshopTools::safeOutput($context->controller->getCategoryLink((int)$category->category_id, $category->link_rewrite)).'" title="'.htmlentities($category->name, ENT_NOQUOTES, 'UTF-8').'">' : '').
                        htmlentities($category->name, ENT_NOQUOTES, 'UTF-8').
                        (($n < $n_categories || $link_on_the_item) ? '</a>' : '').
                        (($n++ != $n_categories || !empty($path)) ? '<span class="navigation-pipe">'.$pipe.'</span>' : '');
                }

                return $full_path.$path;
            }
        }else if ($category_type === 'CMS'){
            $category = new CMSCategory($category_id, $context->language->lang_id);
            if (!JeproshopTools::isLoadedObject($category, 'category_id'))
                die(Tools::displayError());
            $category_link = $context->link->getCMSCategoryLink($category);

            if ($path != $category->name)
                $full_path .= '<a href="'.Tools::safeOutput($category_link).'">'.htmlentities($category->name, ENT_NOQUOTES, 'UTF-8').'</a><span class="navigation-pipe">'.$pipe.'</span>'.$path;
            else
                $full_path = ($link_on_the_item ? '<a href="'.Tools::safeOutput($category_link).'">' : '').htmlentities($path, ENT_NOQUOTES, 'UTF-8').($link_on_the_item ? '</a>' : '');

            return JeproshopTools::getPath($category->parent_id, $full_path, $link_on_the_item, $category_type);
        }
    }

    /**
     * Return a friendly url made from the provided string
     * If the mbstring library is available, the output is the same as the js function of the same name
     *
     * @param string $str
     * @return string
     */
    public static function str2url($str){
        static $allow_accented_chars = null;

        if ($allow_accented_chars === null){
            $allow_accented_chars = JeproshopSettingModelSetting::getValue('allow_accented_chars_url');
        } if(is_array($str)){ print_r($str); }
        $str = trim($str);

        if (function_exists('mb_strtolower'))
            $str = mb_strtolower($str, 'utf-8');
        if (!$allow_accented_chars)
            $str = JeproshopTools::replaceAccentedChars($str);

        // Remove all non-white list chars.
        if ($allow_accented_chars)
            $str = preg_replace('/[^a-zA-Z0-9\s\'\:\/\[\]-\pL]/u', '', $str);
        else
            $str = preg_replace('/[^a-zA-Z0-9\s\'\:\/\[\]-]/','', $str);

        $str = preg_replace('/[\s\'\:\/\[\]\-]+/', ' ', $str);
        $str = str_replace(array(' ', '/'), '-', $str);

        // If it was not possible to lowercase the string with mb_strtolower, we do it after the transformations.
        // This way we lose fewer special chars.
        if (!function_exists('mb_strtolower'))
            $str = strtolower($str);

        return $str;
    }

    public static function replaceAccentedChars($str){
        /* One source among others:
            http://www.tachyonsoft.com/uc0000.htm
            http://www.tachyonsoft.com/uc0001.htm
            http://www.tachyonsoft.com/uc0004.htm
        */
        $patterns = array(

            /* Lowercase */
            /* a  */ '/[\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}\x{0101}\x{0103}\x{0105}\x{0430}]/u',
            /* b  */ '/[\x{0431}]/u',
            /* c  */ '/[\x{00E7}\x{0107}\x{0109}\x{010D}\x{0446}]/u',
            /* d  */ '/[\x{010F}\x{0111}\x{0434}]/u',
            /* e  */ '/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{0113}\x{0115}\x{0117}\x{0119}\x{011B}\x{0435}\x{044D}]/u',
            /* f  */ '/[\x{0444}]/u',
            /* g  */ '/[\x{011F}\x{0121}\x{0123}\x{0433}\x{0491}]/u',
            /* h  */ '/[\x{0125}\x{0127}]/u',
            /* i  */ '/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}\x{0129}\x{012B}\x{012D}\x{012F}\x{0131}\x{0438}\x{0456}]/u',
            /* j  */ '/[\x{0135}\x{0439}]/u',
            /* k  */ '/[\x{0137}\x{0138}\x{043A}]/u',
            /* l  */ '/[\x{013A}\x{013C}\x{013E}\x{0140}\x{0142}\x{043B}]/u',
            /* m  */ '/[\x{043C}]/u',
            /* n  */ '/[\x{00F1}\x{0144}\x{0146}\x{0148}\x{0149}\x{014B}\x{043D}]/u',
            /* o  */ '/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}\x{014D}\x{014F}\x{0151}\x{043E}]/u',
            /* p  */ '/[\x{043F}]/u',
            /* r  */ '/[\x{0155}\x{0157}\x{0159}\x{0440}]/u',
            /* s  */ '/[\x{015B}\x{015D}\x{015F}\x{0161}\x{0441}]/u',
            /* ss */ '/[\x{00DF}]/u',
            /* t  */ '/[\x{0163}\x{0165}\x{0167}\x{0442}]/u',
            /* u  */ '/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{0169}\x{016B}\x{016D}\x{016F}\x{0171}\x{0173}\x{0443}]/u',
            /* v  */ '/[\x{0432}]/u',
            /* w  */ '/[\x{0175}]/u',
            /* y  */ '/[\x{00FF}\x{0177}\x{00FD}\x{044B}]/u',
            /* z  */ '/[\x{017A}\x{017C}\x{017E}\x{0437}]/u',
            /* ae */ '/[\x{00E6}]/u',
            /* ch */ '/[\x{0447}]/u',
            /* kh */ '/[\x{0445}]/u',
            /* oe */ '/[\x{0153}]/u',
            /* sh */ '/[\x{0448}]/u',
            /* shh*/ '/[\x{0449}]/u',
            /* ya */ '/[\x{044F}]/u',
            /* ye */ '/[\x{0454}]/u',
            /* yi */ '/[\x{0457}]/u',
            /* yo */ '/[\x{0451}]/u',
            /* yu */ '/[\x{044E}]/u',
            /* zh */ '/[\x{0436}]/u',

            /* Uppercase */
            /* A  */ '/[\x{0100}\x{0102}\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}\x{0410}]/u',
            /* B  */ '/[\x{0411}]]/u',
            /* C  */ '/[\x{00C7}\x{0106}\x{0108}\x{010A}\x{010C}\x{0426}]/u',
            /* D  */ '/[\x{010E}\x{0110}\x{0414}]/u',
            /* E  */ '/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{0112}\x{0114}\x{0116}\x{0118}\x{011A}\x{0415}\x{042D}]/u',
            /* F  */ '/[\x{0424}]/u',
            /* G  */ '/[\x{011C}\x{011E}\x{0120}\x{0122}\x{0413}\x{0490}]/u',
            /* H  */ '/[\x{0124}\x{0126}]/u',
            /* I  */ '/[\x{0128}\x{012A}\x{012C}\x{012E}\x{0130}\x{0418}\x{0406}]/u',
            /* J  */ '/[\x{0134}\x{0419}]/u',
            /* K  */ '/[\x{0136}\x{041A}]/u',
            /* L  */ '/[\x{0139}\x{013B}\x{013D}\x{0139}\x{0141}\x{041B}]/u',
            /* M  */ '/[\x{041C}]/u',
            /* N  */ '/[\x{00D1}\x{0143}\x{0145}\x{0147}\x{014A}\x{041D}]/u',
            /* O  */ '/[\x{00D3}\x{014C}\x{014E}\x{0150}\x{041E}]/u',
            /* P  */ '/[\x{041F}]/u',
            /* R  */ '/[\x{0154}\x{0156}\x{0158}\x{0420}]/u',
            /* S  */ '/[\x{015A}\x{015C}\x{015E}\x{0160}\x{0421}]/u',
            /* T  */ '/[\x{0162}\x{0164}\x{0166}\x{0422}]/u',
            /* U  */ '/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{0168}\x{016A}\x{016C}\x{016E}\x{0170}\x{0172}\x{0423}]/u',
            /* V  */ '/[\x{0412}]/u',
            /* W  */ '/[\x{0174}]/u',
            /* Y  */ '/[\x{0176}\x{042B}]/u',
            /* Z  */ '/[\x{0179}\x{017B}\x{017D}\x{0417}]/u',
            /* AE */ '/[\x{00C6}]/u',
            /* CH */ '/[\x{0427}]/u',
            /* KH */ '/[\x{0425}]/u',
            /* OE */ '/[\x{0152}]/u',
            /* SH */ '/[\x{0428}]/u',
            /* SHH*/ '/[\x{0429}]/u',
            /* YA */ '/[\x{042F}]/u',
            /* YE */ '/[\x{0404}]/u',
            /* YI */ '/[\x{0407}]/u',
            /* YO */ '/[\x{0401}]/u',
            /* YU */ '/[\x{042E}]/u',
            /* ZH */ '/[\x{0416}]/u');

        // � to oe
        // � to aa
        // � to ae

        $replacements = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 'ss', 't', 'u', 'v', 'w', 'y', 'z', 'ae', 'ch', 'kh', 'oe', 'sh', 'shh', 'ya', 'ye', 'yi', 'yo', 'yu', 'zh',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'Y', 'Z', 'AE', 'CH', 'KH', 'OE', 'SH', 'SHH', 'YA', 'YE', 'YI', 'YO', 'YU', 'ZH'
        );

        return preg_replace($patterns, $replacements, $str);
    }

    public static function fileExistsInCache($fileName){
        if (!isset(self::$file_exists_cache[$fileName])) {
            self::$file_exists_cache[$fileName] = file_exists($fileName);
        }
        return self::$file_exists_cache[$fileName];
    }

    public static function getUserPlatform(){
        if (isset(self::$_user_plate_form))
            return self::$_user_plate_form;

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        self::$_user_plate_form = 'unknown';

        if (preg_match('/linux/i', $user_agent))
            self::$_user_plate_form = 'Linux';
        elseif (preg_match('/macintosh|mac os x/i', $user_agent))
            self::$_user_plate_form = 'Mac';
        elseif (preg_match('/windows|win32/i', $user_agent))
            self::$_user_plate_form = 'Windows';

        return self::$_user_plate_form;
    }

    public static function getBrightness($hex){
        if (JeproshopTools::strToLower($hex) == 'transparent') {
            return '129';
        }

        $hex = str_replace('#', '', $hex);

        if (JeproshopTools::strLen($hex) == 3) {
            $hex .= $hex;
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    }

    public static function strToLower($str)
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'utf-8');
        }
        return strtolower($str);
    }

    public static function strLen($str, $encoding = 'UTF-8')
    {
        if (is_array($str)) {
            return false;
        }
        $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, $encoding);
        }
        return strlen($str);
    }

    public static function unSerialize($serialized, $object = false)
    {
        if (is_string($serialized) && (strpos($serialized, 'O:') === false || !preg_match('/(^|;|{|})O:[0-9]+:"/', $serialized)) && !$object || $object) {
            return @unserialize($serialized);
        }

        return false;
    }

    /**
     * getMemoryLimit allow to get the memory limit in octet
     *
     * @since 1.4.5.0
     * @return int the memory limit value in octet
     */
    public static function getMemoryLimit(){
        $memoryLimit = @ini_get('memory_limit');
        return JeproshopTools::getOctets($memoryLimit);
    }

    public static function convertAndFormatPrice($price){ return ""; }

    public static function usingSecureMode(){ return FALSE; }


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