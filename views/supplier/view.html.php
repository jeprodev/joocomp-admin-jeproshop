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

class JeproshopSupplierViewSupplier extends JeproshopViewLegacy {
    public $supplier;

    public function renderDetails($tpl = null){
        $supplierModel = new JeproshopSupplierModelSupplier();
        $suppliers = $supplierModel->getSuppliersList();
        $this->assignRef('suppliers', $suppliers);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $this->supplier = new JeproshopSupplierModelSupplier();

        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        if(!isset($this->context)){ $this->context = JeproshopContext::getContext(); }

        $image = COM_JEPROSHOP_SUPPLIER_IMAGE_DIR . $this->supplier->supplier_id . '.jpg';
        $imageUrl = JeproshopImageManager::thumbnail($image, 'supplier_' . $this->supplier->supplier_id . '.' . $this->imageType, 350, $this->imageType, true, true);
        $imageSize = file_exists($image) ? filesize($image) / 1000 : false;

        $helper = new JeproshopHelper();
        $this->assignRef('helper', $helper);

        $address = $this->supplier->getSupplierAddress();
        $this->assignRef('address', $address);
        $countries = JeproshopCountryModelCountry::getStaticCountries($this->context->language->lang_id, false);
        $this->assignRef('countries', $countries);
        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch ($task){
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_SUPPLIER_TITLE'), 'jeproshop-category');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');
                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_SUPPLIERS_LIST_TITLE'), 'jeproshop-category');
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

        $supplierId = (int)$app->input->get('supplier_id');
        if ($supplierId && JeproshopTools::isUnsignedInt($supplierId)) {
            if (!$this->supplier)
                $this->supplier = new JeproshopSupplierModelSupplier($supplierId);
            if (JeproshopTools::isLoadedObject($this->supplier, 'supplier_id'))
                return $this->supplier;
            // throw exception
            JError::raiseError(500, 'The supplier cannot be loaded (or found)');
            return false;
        } elseif ($opt) {
            if (!$this->supplier)
                $this->supplier = new JeproshopSupplierModelSupplier();
            return $this->supplier;
        } else {
            $this->errors[] = Tools::displayError('The supplier cannot be loaded (the identifier is missing or invalid)');
            return false;
        }
    }
}