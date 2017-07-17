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

class JeproshopContext {
    /** @var JeproshopContext **/
    private static $instance;

    public $controller;

    public $cookie;
    

    /** @var JeproshopCartModelCart Description **/
    public $cart;

    /** @var JeproshopCustomerModelCustomer Description **/
    public $customer;

    /** @var JeproshopCountryModelCountry Description **/
    public $country;

    /** @var JeproshopEmployeeModelEmployee Description **/
    public $employee;

    /** @var JeproshopLanguageModelLanguage Description **/
    public $language;

    /** @var JeproshopCurrencyModelCurrency Description **/
    public $currency;

    /** @var JeproshopShopModelShop Description **/
    public $shop;

    /** @var JeproshopMobile Description **/
    public $mobile_detect;

    /** @var boolean Description **/
    public $mobile_device;

    /**
     * Get a singleton JeproshopContext
     * @return JeproshopContext
     */
    public static function getContext(){
        if(!isset(self::$instance)){
            self::$instance = new JeproshopContext();
        }
        return self::$instance;
    }

    /**
     * Clone current context
     *
     * @return JeproshopContext
     */
    public function cloneContext(){
        return clone($this);
    }
}