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

class JeproshopCombinationModelCombination extends JeproshopModel{

    public  $product_attribute_id;

    public $product_id;

    public $reference;

    public $supplier_reference;

    public $location;

    public $ean13;

    public $upc;

    public $wholesale_price;

    public $price;

    public $unit_price_impact;

    public $ecotax;

    public $minimal_quantity = 1;

    public $quantity;

    public $weight;

    public $default_on;

    public $shop_list_id = array();

    public $available_date = '0000-00-00';

    public function __construct()
    {
    }

    /**
     * This method is allow to know if a feature is active
     * @return bool
     */
    public static function isFeaturePublished(){
        static $feature_active = NULL;
        if($feature_active === NULL){
            $feature_active = JeproshopSettingModelSetting::getValue('combination_feature_active');
        }
        return $feature_active;
    }

}