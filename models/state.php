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

class JeproshopStateModelState extends JeproshopModel {
    public $state_id;

    /** @var integer Country id which state belongs */
    public $country_id;

    /** @var integer Zone id which state belongs */
    public $zone_id;

    /** @var string 2 letters iso code */
    public $iso_code;

    /** @var string Name */
    public $name;

    /** @var boolean Status for delivery */
    public $published = true;

    public function __construct($stateId = null){

    }

    public function getStateList(JeproshopContext $context = NULL){
        jimport('joomla.html.pagination');
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if(!$context){ $context = JeproshopContext::getContext(); }
    }

    /**
     * Get a state name with its ID
     *
     * @param integer $stateId Country ID
     * @return string State name
     */
    public static function getNameByStateId($stateId){
        if (!$stateId)
            return false;
        $cacheKey = 'jeproshop_state_get_name_by_id_'. (int)$stateId;
        if (!JeproshopCache::isStored($cacheKey)) {
            $db = JFactory::getDBO();
            $query = "SELECT " . $db->quoteName('name') . "	FROM " . $db->quoteName('#__jeproshop_state') . " WHERE ";
            $query .= $db->quoteName('state_id') . "= " . (int)$stateId;

            $db->setQuery($query);
            $result = $db->loadResult();
            JeproshopCache::store($cacheKey, $result);
        }
        return JeproshopCache::retrieve($cacheKey);
    }
}