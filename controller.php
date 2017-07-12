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
        if(!defined('JEPROSHOP_BASE_URL')){ defined('JEPROSHOP_BASE_URL', JeproshopTools::getShopDomain(true)); }
        if(!defined('JEPROSHOP_BASE_SSL_URL')){ defined('JEPROSHOP_BASE_SSL_URL', JeproshopTools::getShopSslDomain(true)); }
    }

    public function display($cachable = FALSE, $urlParams = FALSE){
        //$this->initContent();
        $view = $this->input->get('view', 'dashboard');
        $layout = $this->input->get('layout', 'default');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->display();
    }

}

