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

class JeproshopCustomerViewCustomer extends JeproshopViewLegacy {
    protected $customer;

    protected $helper;

    protected $customers;

    public function renderDetails($tpl = null){
        $customerModel = new JeproshopCustomerModelCustomer();
        $this->customers = $customerModel->getCustomerList();
        $this->pagination = $customerModel->getPagination();
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        $groups = JeproshopGroupModelGroup::getStaticGroups($this->context->language->lang_id, true);

        $this->assignRef('groups', $groups);
        $this->helper = new JeproshopHelper();

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }
        $groups = JeproshopGroupModelGroup::getStaticGroups($this->context->language->lang_id, true);

        $this->assignRef('groups', $groups); 
        $this->helper = new JeproshopHelper();
        
        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch($task){
            case 'add' : break;
            case 'edit' :
                if(!$this->context->controller->can_add_customer){ }
                break;
            default : break;
        }

        $this->addSideBar('customers');
    }

    /**
     * Load class object using identifier in $_GET (if possible)
     * otherwise return an empty object, or die
     *
     * @param boolean $opt Return an empty object if load fail
     * @return object|boolean
     */
    public function loadObject($opt = false){
        $app = JFactory::getApplication();
        $customerId = (int)$app->input->get('customer_id');
        if ($customerId && JeproshopTools::isUnsignedInt($customerId)){
            if (!$this->customer)
                $this->customer = new JeproshopCustomerModelCustomer($customerId);
            if (JeproshopTools::isLoadedObject($this->customer, 'customer_id')){
                return $this->customer;
            }
            // throw exception
            //$this->errors[] = Tools::displayError('The object cannot be loaded (or found)');
            return false;
        }elseif ($opt){
            if (!$this->customer)
                $this->customer = new JeproshopCustomerModelCustomer();
            return $this->customer;
        }else{
            JeproshopTools::displayError('The object cannot be loaded (the identifier is missing or invalid)');
            return false;
        }
    }
}