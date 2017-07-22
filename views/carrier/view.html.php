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

class JeproshopCarrierViewCarrier extends JeproshopViewLegacy {
    protected $carrier = null;

    public function renderDetails($tpl = null){
        $carrierModel = new JeproshopCarrierModelCarrier();
        $carriers = $carrierModel->getCarriersList();
        $this->assignRef('carriers', $carriers);

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $tax_rules_groups = JeproshopTaxRulesGroupModelTaxRulesGroup::getTaxRulesGroups(true);
        $this->assignRef('tax_rules_groups', $tax_rules_groups);
        $carrier_logo = false;
        $this->assignRef('carrier_logo', $carrier_logo);
        $groups = JeproshopGroupModelGroup::getGroups(JeproshopContext::getContext()->language->lang_id);
        $this->assignRef('groups', $groups);
        $zones = JeproshopZoneModelZone::getZones();
        $this->assignRef('zones', $zones);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $tax_rules_groups = JeproshopTaxRulesGroupModelTaxRulesGroup::getTaxRulesGroups(true);
        $this->assignRef('tax_rules_groups', $tax_rules_groups);
        $carrier_logo = JeproshopTools::isLoadedObject($this->carrier, 'carrier_id') && file_exists(COM_JEPROSHOP_CARRIER_IMAGE_DIR . $this->carrier->carrier_id . '.jpg') ? COM_JEPROSHOP_CARRIER_IMAGE_DIR . $this->carrier->carrier_id . '.jpg' : false;
        $this->assignRef('carrier_logo', $carrier_logo);
        $groups = JeproshopGroupModelGroup::getGroups(JeproshopContext::getContext()->language->lang_id);
        $this->assignRef('groups', $groups);
        $zones = JeproshopZoneModelZone::getZones();
        $this->assignRef('zones', $zones);
        $carrierZones = $this->carrier->getZones();
        $carrier_zones_ids = array();
        if (is_array($carrierZones)) {
            foreach ($carrierZones as $carrier_zone)
                $carrier_zones_ids[] = $carrier_zone->zone_id;
        }
        $this->assignRef('selected_zones', $carrier_zones_ids);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function loadObject($option = false){
        $app = JFactory::getApplication();
        $carrier_id = $app->input->get('carrier_id');
        if($carrier_id && JeproshopTools::isUnsignedInt($carrier_id)){
            if(!$this->carrier){
                $this->carrier = new JeproshopCarrierModelcarrier($carrier_id);
            }
            if(JeproshopTools::isLoadedObject($this->carrier, 'carrier_id')){ return true; }
            return false;
        }elseif($option){
            if(!$this->carrier){
                $this->carrier = new JeproshopCarrierModelCarrier();
            }
            return true;
        }else{
            return false;
        }
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch ($task){
            case 'edit':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_CARRIER_TITLE'), 'carrier-jeproshop');
                JToolBarHelper::apply('update');
                JToolBarHelper::cancel('cancel');
                break;
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_CARRIERS_TITLE'), 'carrier-jeproshop');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_CARRIERS_LIST_TITLE'), 'carrier-jeproshop');
                JToolBarHelper::addNew('add');
                break;
        }

        $this->addSideBar('shipping');
    }
}