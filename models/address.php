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
}