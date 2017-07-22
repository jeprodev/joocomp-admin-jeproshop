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

class JeproshopSettingController extends JeproshopController{
    public function product(){
        $view = $this->input->get('view', 'setting');
        $layout = $this->input->get('layout', 'product');

        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->renderProductSettingsForm();
    }

    public function customer(){
        $view = $this->input->get('view', 'setting');
        $layout = $this->input->get('layout', 'customer');

        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->renderCustomerSettingsForm();
    }

    public function order(){
        $view = $this->input->get('view', 'setting');
        $layout = $this->input->get('layout', 'order');

        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->renderOrderSettingForm();
    }

    public function general(){
        $view = $this->input->get('view', 'setting');
        $layout = $this->input->get('layout', 'general');

        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->editGeneralSettings();
    }

    public function geolocation(){
        $view = $this->input->get('view', 'setting');
        $layout = $this->input->get('layout', 'geolocation');

        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->renderGeolocationSettingsForm();
    }
}