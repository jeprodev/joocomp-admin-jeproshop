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
class JeproshopCookie
{
    public $lang_id;

    public $employee_id;

    public $currency_id;

    public $cart_id = null;

    public $customer_id = null;

    public $guest_id = null;

    public $no_mobile;

    public $products_filter_category_id;

    public $account_created = FALSE;

    public $employee_form_lang;

    public $last_activity;

    public $passwd;

    public $remote_addr;

    /** @var array Contain cookie content in a key => value format */
    protected $_content;

    /** @var array Crypted cookie name */
    protected $_name;

    /** @var array expiration date for setcookie() */
    protected $_expire;

    /** @var array website domain for setcookie() */
    protected $_domain;

    /** @var array Path for setcookie() */
    protected $_path;

    /** @var array cipher tool instance  */
    protected $_cipherTool;

    protected $_modified = false;

    public function __construct()
    {
        $this->_content = array();
    }

    public function __get($key){
        return isset($this->_content[$key]) ? $this->_content[$key] : false;
    }

    /**
     * Set expiration date
     * @param integer $expire Expiration time from now
     */
    public function setExpire($expire){
        $this->_expire = (int)$expire;
    }

    /**
     * Setcookie according to php version
     *
     * @param $cookie
     * @return bool
     */
    protected function _setCookie($cookie = NULL){
        if($cookie){
            $content = $this->_cipherTool->encrypt($cookie);
            $time = $this->_expire;
        }else{
            $content = 0; $time = 1;
        }

        if(PHP_VERSION_ID <= 50200){
            return setcookie($this->_name, $content, $time, $this->_path,  $this->_domain, 0);
        }else{
            return setcookie($this->_name, $content, $time, $this->_path,  $this->_domain, 0, TRUE);
        }
    }

    /**
     * Magic method which adds data into _content array.
     *
     * @param string $key   Access key for the value
     * @param mixed  $value Value corresponding to the key
     *
     * @throws Exception
     */
    public function __set($key, $value) {
        if (is_array($value)) {
            JeproshopTools::displayError(JText::_('COM_JEPROSHOP_COOKIE_VALUE_SHOULD_NOT_BE_AN_ARRAY_MESSAGE'));
        }
        if (preg_match('/¤|\|/', $key.$value)) {
            throw new Exception('Forbidden chars in cookie');
        }
        if (!$this->_modified && (!isset($this->_content[$key]) || (isset($this->_content[$key]) && $this->_content[$key] != $value))) {
            $this->_modified = true;
        }
        $this->_content[$key] = $value;
    }

}