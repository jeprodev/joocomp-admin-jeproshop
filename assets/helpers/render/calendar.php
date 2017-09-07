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

class JeproshopCalendarHelper {
    const CALENDAR_DEFAULT_DATE_FORMAT  = 'Y-mm-dd';
    const CALENDAR_DEFAULT_COMPARE_OPTION = 1;

    private $tpl = 'calendar';

    private $_actions;
    private $_compare_actions;
    private $_compare_date_from;
    private $_compare_date_to;
    private $_compare_date_option;
    private $_date_format;
    private $_date_from;
    private $_date_to;
    private $_rtl;

    public function __construct($tpl ){
        $this->tpl = $tpl;
    }

    public function setDateFrom($value){
        if (!isset($value) || $value == '') {
            $value = date('Y-m-d', strtotime('-31 days'));
        }

        if (!is_string($value)) {
            JeproshopTools::displayError(JText::_('COM_JEPROSHOP_DATE_MUST_BE_A_STRING_MESSAGE'));
        }

        $this->_date_from = $value;
        return $this;
    }

    public function setDateTo($value){
        if (!isset($value) || $value == '') {
            $value = date('Y-m-d');
        }

        if (!is_string($value)) {
            JeproshopTools::displayError(JText::_('COM_JEPROSHOP_DATE_MUST_BE_A_STRING_MESSAGE'));
        }

        $this->_date_to = $value;
        return $this;
    }

    public function setCompareDateTo($value)
    {
        $this->_compare_date_to = $value;
        return $this;
    }

    public function getCompareDateTo()
    {
        return $this->_compare_date_to;
    }

    public function setCompareOption($value)
    {
        $this->_compare_date_option = (int)$value;
        return $this;
    }

    public function getCompareOption()
    {
        if (!isset($this->_compare_date_option)) {
            $this->_compare_date_option = self::CALENDAR_DEFAULT_COMPARE_OPTION;
        }

        return $this->_compare_date_option;
    }

    public function setDateFormat($value)
    {
        if (!is_string($value)) {
            JeproshopTools::displayError(JText::_('COM_JEPROSHOP_DATE_FORMAT_MUST_BE_A_STRING_MESSAGE'));
        }

        $this->_date_format = $value;
        return $this;
    }

    public function setCompareDateFrom($value)
    {
        $this->_compare_date_from = $value;
        return $this;
    }

    public function setActions($value)
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            JeproshopTools::displayError(JText::_('COM_JEPROSHOP_ACTIONS_VALUE_MUST_BE_A_TRAVERSABLE_ARRAY_MESSAGE'));
        }
        $this->_actions = $value;
        return $this;
    }

    public function getDateFrom(){
        if (!isset($this->_date_from)) {
            $this->_date_from = date('Y-m-d', strtotime('-31 days'));
        }
        return $this->_date_from;
    }

    public function getActions(){
        if (!isset($this->_actions)) {
            $this->_actions = array();
        }
        return $this->_actions;
    }

    public function getCompareDateFrom()
    {
        return $this->_compare_date_from;
    }

    public function getDateFormat(){
        if (!isset($this->_date_format)) {
            $this->_date_format = self::CALENDAR_DEFAULT_DATE_FORMAT;
        }
        return $this->_date_format;
    }

    public function setRTL($value) {
        $this->_rtl = (bool)$value;
        return $this;
    }

    public function getDateTo()
    {
        if (!isset($this->_date_to)) {
            $this->_date_to = date('Y-m-d');
        }

        return $this->_date_to;
    }

    public function isRTL(){
        if (!isset($this->_rtl)) {
            $this->_rtl = JeproshopContext::getContext()->language->is_rtl;
        }
        return $this->_rtl;
    }

    public function generate(){
        $context = JeproshopContext::getContext();
        $document = JFactory::getDocument();
        JHtml::_('jquery.framework');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');

        /*$admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', '', $admin_webpath); */
        $theme = ((JeproshopTools::isLoadedObject($context->employee, 'employee_id')
            && $context->employee->theme) ? $context->employee->theme : 'default');

        $themeDirectory =  dirname(dirname(dirname(__DIR__))) . 'themes' . DIRECTORY_SEPARATOR;

        if (!file_exists($themeDirectory . $theme)) {
            $theme = 'default';
        }

        $scriptFileDirectory = JURI::base() . 'components' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
        //$document->addScript($scriptFileDirectory . 'date-range-picker.js');
        $document->addScript($scriptFileDirectory . 'calendar.js');


        /*$this->tpl = $this->createTemplate($this->base_tpl);
        $this->tpl->assign(array(
            'date_format'       => ,
            'date_from'         => ,
            'date_to'           => ,
            'compare_date_from' =>,
            'compare_date_to'   => $this->getCompareDateTo(),
            'actions'           =>
            'compare_actions'   => $this->getCompareActions(),
            'compare_option'    => ,
            'is_rtl'            =>
        ));

        $html .= parent::generate(); */
        return $this->render();
    }

    private function render(){
        ob_start();
        //include (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $this->template . '.php');
        include (__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'calendar.php');
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }

}