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

class JeproshopTaxManagerFactory
{
    protected static $cache_tax_manager;

    /**
     *
     * @param JeproshopAddressModelAddress $address
     * @param int $type
     */
    public static function getManager(JeproshopAddressModelAddress $address, $type){
        $cacheKey = JeproshopTaxManagerFactory::getCacheKey($address);
        if(!isset(JeproshopTaxManagerFactory::$cache_tax_manager[$cacheKey])){
            $taxManager = JeproshopTaxManagerFactory::getTaxManager($address, $type);
            if(!($taxManager instanceof JeproshopTaxManagerInterface)){
                $taxManager = new JeproshopTaxRulesTaxManager($address, $type);
            }
            JeproshopTaxManagerFactory::$cache_tax_manager[$cacheKey] = $taxManager;
        }
        return JeproshopTaxManagerFactory::$cache_tax_manager[$cacheKey];
    }

    public static function getTaxManager(JeproshopAddressModelAddress $address, $type){
        return NULL;
    }

    /**
     *
     * Create a unique identifier for the address
     * @param JeproshopAddressModelAddress $address
     * @return string
     * @internal param $Address
     */
    protected static function getCacheKey(JeproshopAddressModelAddress $address){
        return $address->country_id . '_' . (int)$address->state_id . '_' . $address->postcode . '_'
        . $address->vat_number . '_' . $address->dni;
    }
}


class JeproshopTaxRulesTaxManager  implements JeproshopTaxManagerInterface {
    public $address;

    public $type;

    public $tax_calculator;

    /**
     *
     * @param JeproshopAddressModelAddress $address
     * @param mixed an additional parameter for the tax manager (ex: tax rules id for JeproshopTaxRuleTaxManager)
     */
    public function __construct(JeproshopAddressModelAddress $address, $type) {
        $this->address = $address;
        $this->type = $type;
    }

    /**
     * Returns true if this tax manager is available for this address
     *
     * @param JeproshopAddressModelAddress $address
     * @return boolean
     */
    public static function isAvailableForThisAddress(JeproshopAddressModelAddress $address){
        return true; // default manager, available for all addresses
    }

    /**
     * Return the tax calculator associated to this address
     *
     * @return JeproshopTaxCalculator
     */
    public function getTaxCalculator(){
        static $tax_enabled = null;
        if (isset($this->tax_calculator)){
            return $this->tax_calculator;
        }

        if ($tax_enabled === null){
            $tax_enabled = JeproshopSettingModelSetting::getValue('use_tax');
        }

        if (!$tax_enabled){
            return new JeproshopTaxCalculator(array());
        }

        $taxes = array();
        $postcode = 0;
        if (!empty($this->address->postcode)){
            $postcode = $this->address->postcode;
        }

        $cacheKey = (int)$this->address->country_id . '_' . (int)$this->address->state_id .'_' . $postcode . '_' . (int)$this->type;
        if (!JeproshopCache::isStored($cacheKey)){
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_tax_rule') . " WHERE " . $db->quoteName('country_id');
            $query .= " = " . (int)$this->address->country_id . " AND " . $db->quoteName('tax_rules_group_id') . " = ";
            $query .= (int)$this->type . " AND " . $db->quoteName('state_id') . " IN (0, " . (int)$this->address->state_id;
            $query .= ") AND ( " . $db->quote($db->escape($postcode)) . " BETWEEN " . $db->quoteName('zipcode_from') . " AND ";
            $query .= $db->quoteName('zipcode_to') . " OR (" . $db->quote('zipcode_to') . " = 0 AND " . $db->quoteName('zipcode_from');
            $query .= " IN(0, " . $db->quote($db->escape($postcode)) . "))) ORDER BY " . $db->quoteName('zipcode_from') ;
            $query .= " DESC, " . $db->quoteName('zipcode_to') . " DESC, " . $db->quoteName('state_id') . " DESC, " . $db->quoteName('country_id') . " DESC" ;

            $db->setQuery($query);
            $results = $db->loadObjectList();

            $behavior = 0;
            $firstRow = true;

            foreach ($results as $result){
                $tax = new JeproshopTaxModelTax((int)$result->tax_id);

                $taxes[] = $tax;

                // the applied behavior correspond to the most specific rules
                if ($firstRow){
                    $behavior = $result->behavior;
                    $firstRow = false;
                }

                if ($result->behavior == 0){ break; }
            }
            JeproshopCache::store($cacheKey, new JeproshopTaxCalculator($taxes, $behavior));
        }
        return JeproshopCache::retrieve($cacheKey);
    }

    public static function getTaxManager(JeproshopAddressModelAddress $address, $type){
        return NULL;
    }

    /**
     *
     * Create a unique identifier for the address
     * @param JeproshopAddressModelAddress $address
     * @return string
     * @internal param $Address
     */
    protected static function getCacheKey(JeproshopAddressModelAddress $address){
        return $address->country_id . '_' . (int)$address->state_id . '_' . $address->postcode . '_'
        . $address->vat_number . '_' . $address->dni;
    }
}


/***** --------- TAX CALCULATOR ----------- *******/
class JeproshopTaxCalculator
{
    const COMBINE_METHOD = 1;
    const ONE_AFTER_ANOTHER_METHOD = 2;

    public $taxes;

    public $computation_method;

    public function __construct(array $taxes = array(), $computationMethod = JeproshopTaxCalculator::COMBINE_METHOD) {
        /*/ sanity check
        foreach($taxes as $tax){
            if(!($tax instanceof JeproshopTaxModelTax)){
                JError::raiseError(500, JText::_('COM_JEPROSHOP_INVALID_TAX_OBJECT_MESSAGE'));
            }
        }*/
        $this->taxes = $taxes;
        $this->computation_method = (int)$computationMethod;
    }

    public function getTotalRate(){
        $taxes = 0;
        if($this->computation_method == JeproshopTaxCalculator::ONE_AFTER_ANOTHER_METHOD){
            $taxes = 1;
            foreach($this->taxes as $tax){
                $taxes *= (1 + (abs($tax->rate) / 100));
            }
            $tax = $taxes -1;
            $taxes = $tax * 100;
        }else{
            foreach($this->taxes as $tax){
                $taxes += abs($tax->rate);
            }
        }
        return (float)$taxes;
    }

    /**
     * Compute and remove the taxes to the specified price
     *
     * @param float $priceTaxIncluded price tax inclusive
     * @return float price without taxes
     */
    public function removeTaxes($priceTaxIncluded){
        return $priceTaxIncluded / (1 + $this->getTotalRate() / 100);
    }

    /**
     * Compute and add the taxes to the specified price
     *
     * @param float $priceTaxExcluded price tax excluded
     * @return float price with taxes
     */
    public function addTaxes($priceTaxExcluded){
        return $priceTaxExcluded * (1 + ($this->getTotalRate() / 100));
    }

    public function getTaxesName(){
        $name = '';
        foreach ($this->taxes as $tax) {
            $name .= $tax->name[(int)JeproshopContext::getContext()->language->lang_id].' - ';
        }

        $name = rtrim($name, ' - ');

        return $name;
    }
}



interface JeproshopTaxManagerInterface
{
    /**
     * This method determine if the tax manager is available for the specified address
     * @param JeproshopAddressModelAddress $address
     *
     * @return JeproshopTaxManager Description
     */
    public static function isAvailableForThisAddress(JeproshopAddressModelAddress $address);

    /**
     * Return the tax calculator associated to this address
     *
     * @return JeproshopTaxCalculator
     */
    public function getTaxCalculator();
}
