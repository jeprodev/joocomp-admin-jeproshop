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

class JeproshopManufacturerViewManufacturer extends JeproshopViewLegacy {
    public $manufacturer;

    public function renderDetails($tpl = null){
        $manufacturerModel = new JeproshopManufacturerModelManufacturer();
        $manufacturers = $manufacturerModel->getManufacturerList();
        $this->assignRef('manufacturers', $manufacturers);

        $pagination = $manufacturerModel->getPagination();
        $this->assignRef('pagination', $pagination);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $this->addToolBar();

        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $this->addToolBar();

        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch ($task){
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_MANUFACTURER_TITLE'), 'jeproshop-manufacture');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            case 'edit':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_MANUFACTURER_TITLE'), 'jeproshop-manufacture');
                JToolBarHelper::apply('update', JText::_('COM_JEPROSHOP_UPDATE_LABEL'));
                JToolBarHelper::cancel('cancel');
                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_MANUFACTURERS_LIST_TITLE'), 'jeproshop-manufacturer');
                JToolBarHelper::addNew('add');
                break;
        }
        $this->addSideBar('catalog');
    }

    /**
     * Load class supplier using identifier in $_GET (if possible)
     * otherwise return an empty supplier, or die
     *
     * @param boolean $opt Return an empty supplier if load fail
     * @return supplier|boolean
     */
    public function loadObject($opt = false){
        $app =JFactory::getApplication();

        $manufacturerId = (int)$app->input->get('manufacturer_id');
        if ($manufacturerId && JeproshopTools::isUnsignedInt($manufacturerId)) {
            if (!$this->manufacturer)
                $this->manufacturer = new JeproshopManufacturerModelManufacturer($manufacturerId);
            if (JeproshopTools::isLoadedObject($this->manufacturer, 'manufacturer_id'))
                return $this->manufacturer;
            // throw exception
            JError::raiseError(500, 'The manufacturer cannot be loaded (or found)');
            return false;
        } elseif ($opt) {
            if (!$this->manufacturer)
                $this->manufacturer = new JeproshopManufacturerModelManufacturer();
            return $this->manufacturer;
        } else {
            $this->errors[] = Tools::displayError('The manufacturer cannot be loaded (the identifier is missing or invalid)');
            return false;
        }
    }
    
}