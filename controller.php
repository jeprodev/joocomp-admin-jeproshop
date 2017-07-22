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

class JeproshopController extends JControllerLegacy
{
    public $use_ajax = true;

    public $default_form_language;

    public $allow_employee_form_language;

    public $shop_link_type = "";

    public $multi_shop_context = -1;

    public $multi_shop_context_group = true;

    public $has_errors;

    /**
     * check if the controller is available for the current user/visitor
     */
    public function checkAccess(){}

    /**
     * Check if the current user/visitor has valid view permissions
     */
    public function viewAccess(){}

    /**
     * initialize jeproshop
     */
    public function initialize(){
        if(!defined('JEPROSHOP_BASE_URL')){ define('JEPROSHOP_BASE_URL', JeproshopTools::getShopDomain(true)); }
        if(!defined('JEPROSHOP_BASE_SSL_URL')){ define('JEPROSHOP_BASE_SSL_URL', JeproshopTools::getShopSslDomain(true)); }
    }

    public function initContent(){
        if(!$this->viewAccess()){
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_YOU_DO_NOT_HAVE_PERMISSION_TO_VIEW_THIS_PAGE_MESSAGE'));
        }

        $this->getLanguages();
        $app = JFactory::getApplication();

        $task = $app->input->get('task');
        $view = $app->input->get('view');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());

        if($task == 'edit' || $task == 'add'){
            if(!$viewClass->loadObject(true)){ return false; }
            $viewClass->setLayout('edit');
            $viewClass->renderEditForm();
        }elseif($task == 'added'){
            $viewClass->setLayout('edit');
            $viewClass->renderAddForm();
        }elseif($task == 'view'){
            if(!$viewClass->loadObject(true)){  return false; }
            $viewClass->setLayout('view');
            $viewClass->renderViewForm();
        }elseif($task == 'display' || $task  == ''){
            $viewClass->renderDetails();
        }elseif(!$this->use_ajax){

        }else{
            $this->execute($task);
        }
    }

    public function display($cache = FALSE, $urlParams = FALSE){
        //$this->initContent();
        $view = $this->input->get('view', 'dashboard');
        $layout = $this->input->get('layout', 'default');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->display();
    }

    public function catalog(){
        $app = JFactory::getApplication();
        $app->input->set('category_id', null);
        $app->input->set('parent_id', null);
        $app->redirect('index.php?option=com_jeproshop&view=product');
    }

    public function orders(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=order');
    }

    public function customers(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=customer');
    }

    public function price_rules(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=cart&task=rules');
    }

    public function shipping(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=carrier');
    }

    public function localization(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=country');
    }

    public function settings(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=setting');
    }

    public function administration(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=administration');
    }

    public function stats(){
        $app = JFactory::getApplication();
        $app->redirect('index.php?option=com_jeproshop&view=stats');
    }

    public function getLanguages(){
        $cookie = JeproshopContext::getContext()->cookie;
        $this->allow_employee_form_language = (int)JeproshopSettingModelSetting::getValue('allow_employee_form_lang');
        if($this->allow_employee_form_language && !$cookie->employee_form_lang){
            $cookie->employee_form_lang = (int)JeproshopSettingModelSetting::getValue('default_lang');
        }

        $lang_exists = false;
        $languages = JeproshopLanguageModelLanguage::getLanguages(false);
        foreach($languages as $language){
            if(isset($cookie->employee_form_language) && $cookie->employee_form_language == $language->lang_id){
                $lang_exists = true;
            }
        }

        $this->default_form_language = $lang_exists ? (int)$cookie->employee_form_language : (int)JeproshopSettingModelSetting::getValue('default_lang');

        return $languages;
    }


}

