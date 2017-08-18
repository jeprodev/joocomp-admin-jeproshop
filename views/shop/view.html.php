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

class JeproshopShopViewShop extends JeproshopViewLegacy{
    public $shop_group = null;

    public $shop = null;

    public function renderDetails($tpl = null){
        $app = JFactory::getApplication();
        $tab = $app->input->get('tab');
        if($tab == 'group') { 
            $shopGroupModel = new JeproshopShopGroupModelShopGroup();
            $shopGroups = $shopGroupModel->getShopGroupList(false);
            $this->assignRef('shop_groups', $shopGroups);
        }else{
            $shopModel = new JeproshopShopModelShop();
            $shops = $shopModel->getShopList();

            $this->assignRef('shops', $shops);
        }
        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderAddForm($tpl = null){
        $this->renderEditForm($tpl);
    }

    public function renderEditForm($tpl = null){
        $tab = JFactory::getApplication()->input->get('tab');
        
        if($this->context == null){
            $this->context = JeproshopContext::getContext();
        }

        $helper = new JeproshopHelper();
        if($tab == 'group') {
            $defaultShop = new JeproshopShopModelShop(JeproshopSettingModelSetting::getValue('default_shop'));
            $defaultShopId = $defaultShop->shop_group_id;
            $this->assignRef('default_shop_group_id', $defaultShopId);

            if(JeproshopShopModelShop::getTotalShops() > 1 && $this->shop_group->shop_group_id) {
                $disableShareCustomer = true;
                $disableShareStock = true;
                $disableShareOrder = true;
                $disablePublished = true;
                $disableDeleted = true;
            }else{
                $disableShareCustomer = false;
                $disableShareStock = false;
                $disableShareOrder = false;
                $disablePublished = false;
                $disableDeleted = false;
            }
            $this->assignRef('disable_share_customer', $disableShareCustomer);
            $this->assignRef('disable_share_stock', $disableShareStock);
            $this->assignRef('disable_share_order', $disableShareOrder);
            $this->assignRef('disable_published', $disablePublished);
            $this->assignRef('disable_deleted', $disableDeleted);
        }else{
            $shop_groups = JeproshopShopGroupModelShopGroup::getShopGroups();
            $this->assignRef('shop_groups', $shop_groups);

            $categories = JeproshopCategoryModelCategory::getRootCategories($this->context->language->lang_id);
            $themes = JeproshopThemeModelTheme::getThemes();
            $this->assignRef('themes', $themes);
            $this->assignRef('categories', $categories);
        }
        $this->assignRef('helper', $helper);
        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        $tab = JFactory::getApplication()->input->get('tab', '');
        echo $task . ' ' . $tab;
        switch($task){
            case 'add' :
                if($tab == 'group'){
                    JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_SHOP_GROUP_LABEL'));
                    JToolBarHelper::apply('save', JText::_('COM_JEPROSHOP_SAVE_LABEL'));
                }
                break;
            case 'edit':
                if($tab == 'group'){
                    JToolBarHelper::title(JText::_('COM_JEPROSHOP_EDIT_SHOP_GROUP_LABEL'));
                    JToolBarHelper::apply('update', JText::_('COM_JEPROSHOP_UPDATE_LABEL'));
                }
                break;
            default :
                if($tab == 'group'){
                    JToolBarHelper::title(JText::_('COM_JEPROSHOP_SHOP_GROUP_LIST_LABEL'));
                }else{
                    JToolBarHelper::title(JText::_('COM_JEPROSHOP_SHOP_LIST_LABEL'));
                    JToolBarHelper::addNew('add');
                }
                break;
        }

        $this->addSideBar('administration');
    }

    public function loadObject(){
        $app = JFactory::getApplication();
        $tab = $app->input->get('tab');
        $isLoaded = false;
        if($tab == 'group'){
            $shopGroupId = $app->input->get('shop_group_id', 0);
            if($shopGroupId && JeproshopTools::isUnsignedInt($shopGroupId)){
                $this->shop_group = new JeproshopShopGroupModelShopGroup($shopGroupId);

                if(!JeproshopTools::isLoadedObject($this->shop_group, 'shop_group_id')){
                    JEroor::raiseError(500, JText::_('COM_JEPROSHOP_SHOP_GROUP_NOT_FOUND_MESSAGE'));
                    $isLoaded = false;
                }else{ $isLoaded = true; }
            }else{
                $this->shop_group = new JeproshopShopGroupModelShopGroup();
                $isLoaded = true;
            }
        }else{
            $shopId = $app->input->get('shop_id', 0);
            if($shopId && JeproshopTools::isUnsignedInt($shopId)){
                $this->shop = new JeproshopShopModelShop($shopId);

                if(!JeproshopTools::isLoadedObject($this->shop, 'shop_id')){
                    JEroor::raiseError(500, JText::_('COM_JEPROSHOP_SHOP_NOT_FOUND_MESSAGE'));
                    $isLoaded = false;
                }else{ $isLoaded = true; }
            }else{
                $this->shop = new JeproshopShopModelShop();
                $isLoaded = true;
            }
        }
        return $isLoaded;
    }


}