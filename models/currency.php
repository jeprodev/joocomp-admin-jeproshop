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

class JeproshopCurrencyModelCurrency extends JeproshopModel
{
    public $currency_id;

    /** @var string name */
    public $name;

    /** @var string Iso code */
    public $iso_code;

    /** @var  string Iso code numeric */
    public $iso_code_num;

    /** @var string symbol for short display */
    public $sign;

    /** @var int bool used for displaying blank between sign and price */
    public $blank;

    /**
     * contains the sign to display before price, according to its format
     * @var string
     */
    public $prefix;

    /**
     * contains the sign to display after price, according to its format
     * @var string
     */
    public $suffix;

    /** @var double conversion rate  */
    public $conversion_rate;

    /** @var int ID used for displaying prices */
    public $format;

    /** @var boolean True if currency has been deleted(staying in database as deleted) */
    public $deleted;

    /** @var int bool Display decimals on prices */
    public $decimals;

    /** @var int bool published  */
    public $published;

    public $shop_id;

    static protected $currencies = array();


    public function __construct($currencyId = null, $shopId = null){
        $db = JFactory::getDBO();

        if($shopId && $this->isMultiShop('currency', false)){
            $this->shop_id = (int)$shopId;
            $this->get_shop_from_context = false;
        }

        if($this->isMultiShop('currency', false) && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($currencyId){
            //load object from the  database if the currency id is provided
            $cacheKey = 'jeproshop_currency_model_' . (int)$currencyId . '_' . (int)$shopId;
            if(!JeproshopCache::isStored($cacheKey)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_currency') . " AS currency ";

                if(JeproshopShopModelShop::isTableAssociated('currency')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_currency_shop') ." AS currency_shop ON( currency.currency_id = currency_shop.currency_id AND currency_shop.shop_id = " . (int)$this->shop_id . ")";
                }
                $query .= " WHERE currency.currency_id = " . (int)$currencyId ;

                $db->setQuery($query);
                $currencyData = $db->loadObject();
                if($currencyData){
                    JeproshopCache::store($cacheKey, $currencyData);
                }
            }else{
                $currencyData = JeproshopCache::retrieve($cacheKey);
            }

            if($currencyData){
                $currencyData->currency_id = $currencyId;
                foreach($currencyData as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        /* prefix and suffix are convenient short cut for displaying price sign before or after the price number */
        $this->prefix = $this->format % 2 != 0 ? $this->sign . " " : "";
        $this->suffix = $this->format % 2 == 0 ? " " . $this->sign : "";
    }

    public static function getCurrencyInstance($currency_id){
        if (!isset(self::$currencies[$currency_id])){
            self::$currencies[(int)($currency_id)] = new JeproshopCurrencyModelCurrency($currency_id);
        }
        return self::$currencies[(int)($currency_id)];
    }

    /**
     * Return available currencies
     *
     * @param bool $object
     * @param bool $published
     * @param bool $groupBy
     * @return array Currencies
     */
    public static function getStaticCurrencies($object = false, $published = true, $groupBy = false) {
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_currency') . " AS currency " . JeproshopShopModelShop::addSqlAssociation('currency');
        $query .= " WHERE " . $db->quoteName('deleted') . " = 0" . ($published ? " AND currency." . $db->quoteName('published') . " = 1" : "");
        $query .= ($groupBy ? " GROUP BY currency." . $db->quoteName('currency_id') : ""). " ORDER BY " . $db->quoteName('name') . " ASC";

        $db->setQuery($query);
        $tab = $db->loadObjectList();
        if ($object){
            foreach ($tab as $key => $currency)
                $tab[$key] = JeproshopCurrencyModelCurrency::getCurrencyInstance($currency->currency_id);
        }
        return $tab;
    }

    public static function getCurrenciesByShopId($shop_id = 0){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_currency') . " AS currency LEFT JOIN " . $db->quoteName('#__jeproshop_currency_shop');
        $query .= " AS currency_shop ON (currency_shop." . $db->quoteName('currency_id') . " = currency." . $db->quoteName('currency_id') . ") ";
        $query .= ($shop_id ? " WHERE currency_shop." . $db->quoteName('shop_id') . " = " .(int)$shop_id : "") . "	ORDER BY " . $db->quoteName('name') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getCurrenciesList(JeproshopContext $context = NULL){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!isset($context)){ $context = JeproshopContext::getContext(); }


        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitStart = $app->getUserStateFromRequest($option. $view. '.limitstart', 'limitstart', 0, 'int');
        $langId = $app->getUserStateFromRequest($option. $view. '.lang_id', 'lang_id', $context->language->lang_id, 'int');

        $useLimit = true;
        if ($limit === false)
            $useLimit = false;

        do{
            $query = "SELECT currency." . $db->quoteName('currency_id') . ", currency." . $db->quoteName('name') . ", currency." . $db->quoteName('iso_code') . ", currency." . $db->quoteName('iso_code_num') . ", currency.";
            $query .= $db->quoteName('sign') . ", currency_shop." . $db->quoteName('conversion_rate') . ", currency." . $db->quoteName('published') . " FROM " . $db->quoteName('#__jeproshop_currency') . " AS currency ";
            $query .= JeproshopShopModelShop::addSqlAssociation('currency') . " WHERE currency." . $db->quoteName('deleted') . " = 0 GROUP BY currency." . $db->quoteName('currency_id');

            $db->setQuery($query);
            $total = count($db->loadObjectList());

            $query .= (($useLimit === true) ? " LIMIT " .(int)$limitStart . ", " .(int)$limit : "");

            $db->setQuery($query);
            $currencies = $db->loadObjectList();

            if($useLimit == true){
                $limitStart = (int)$limitStart -(int)$limit;
                if($limitStart < 0){ break; }
            }else{ break; }
        }while(empty($currencies));

        $this->pagination = new JPagination($total, $limitStart, $limit);
        return $currencies;
    }
}