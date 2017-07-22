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


class JeproshopAddressViewAddress extends JeproshopViewLegacy {
    public $address;

    public function renderDetails($tpl = null){
        $addressModel = new JeproshopAddressModelAddress();
        $addresses = $addressModel->getAddressList();
        $pagination = $addressModel->getPagination();

        $this->assignRef('addresses', $addresses);
        $this->assignRef('pagination', $pagination);

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }
    public function renderEditForm($tpl = null){
        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch($task){
            case 'add' : break;
            case 'edit' : break;
            default : break;
        }
        $this->addSideBar('customers');
    }

    public function loadObject($option = false){
        $app = JFactory::getApplication();
        $address_id = $app->input->get('address_id');
        if($address_id && JeproshopTools::isUnsignedInt($address_id)){
            if(!$this->address){
                $this->address = new JeproshopAddressModelAddress($address_id);
            }
            if(JeproshopTools::isLoadedObject($this->address, 'address_id')){ return true; }
            return false;
        }elseif($option){
            if(!$this->address){
                $this->address = new JeproshopAddressModelAddress();
            }
            return true;
        }else{
            return false;
        }
    }

}