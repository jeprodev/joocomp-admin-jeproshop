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

        /*$admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $admin_webpath = preg_replace('/^'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', '', $admin_webpath); */
        $theme = ((JeproshopTools::isLoadedObject($context->employee, 'employee_id')
            && $context->employee->theme) ? $context->employee->theme : 'default');

        $themeDirectory =  dirname(dirname(dirname(__DIR__))) . 'themes' . DIRECTORY_SEPARATOR;

        if (!file_exists($themeDirectory . $theme)) {
            $theme = 'default';
        }

        $scriptFileDirectory = JURI::base() . 'components' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
        $document->addScript($scriptFileDirectory . 'date-range-picker.js');
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
        $html = '';

        $compareDateFrom =  $this->getCompareDateFrom();
        $compareDateTo =  $this->getCompareDateTo();
        $actions = $this->getActions();

        if($this->tpl == 'calendar'){
            $html .= '<div id="jeproshop-date-picker" class="row row-padding-top hide" >' .
                '<div class="col-lg-12" >' .
                    '<div class="date-range-picker-days">' .
                        '<div class="row">';
            if($this->isRTL()) {
                $html .= '<div class="col-sm-6 col-lg-4" >';
				$html .= '<div class="end-date-picker" data-date="' . $this->getDateTo() . '" data-date-format="' . $this->getDateFormat() . '" ></div >';
                $html .= '</div><div class="col-sm-6 col-lg-4" ><div class="start-date-picker" data-date="' . $this->getDateFrom();
                $html .= '" data-date-format="' . $this->getDateFormat() . '" ></div></div>';
            }else{
				$html .= '<div class="col-sm-6 col-lg-4"><div class="start-date-picker" data-date="' .  $this->getDateFrom() . '" data-date-format="';
                $html .= $this->getDateFormat() . '"></div></div><div class="col-sm-6 col-lg-4"><div class="end-date-picker" data-date="';
                $html .= $this->getDateTo() . '" data-date-format="' . $this->getDateFormat()  . '"></div></div>';
			}

            $html .= '<div class="col-xs-12 col-sm-6 col-lg-4 pull-right" > <div id="date-picker-form" class="form-inline">';
			$html .= '<div id="date-range" class="form-date-group"> <div  class="form-date-heading" >';
            $html .= '<span class="title">' . JText::_('COM_JEPROSHOP_DATE_RANGE_LABEL') . '</span>';
			if(isset($actions) && count($actions) > 0){
				if(count($actions) > 1) {
                    $html .= '<button class="btn btn-default btn-xs pull-right dropdown-toggle" data-toggle="dropdown" type="button">';
                    $html .= JText::_('COM_JEPROSHOP_CUSTOM_LABEL') . '<i class="icon-angle-down"></i></button><ul class="dropdown-menu"> ';
                    foreach ($actions as $action) {
                        $html .= '<li><a ';
                        if (isset($action->href)) {
                            $html .= 'href="' . $action->href . '" ';
                        }
                        if (isset($action->class)) {
                            $html .= 'class="' . $action->class . '" ';
                        }
                        $html .= '>';
                        if (isset($action->icon)) {
                            $html .= '<i class="' . $action->icon . '"></i>';
                        }
                        $html .= $action->label . '</a></li>';
                    }

                    $html .= '</ul>';
                }else{
					$html .= '<a ';
                    if(isset($actions[0]->href)){ $html .= ' href="' . $actions[0]->href . '" '; }
                    $html .= 'class="btn btn-default btn-xs pull-right ' ;
                    if(isset($actions[0]->class)){ $html .= $actions[0]->class; }
                    $html .= '" > ' . (isset($actions[0]->icon) ? '<i class="' . $actions[0]->icon . '"></i> ' : '') . $actions[0]->label . '</a>';
				}
			}
			
            $html .= '</div><div class="form-date-body" ><label>' . JText::_('COM_JEPROSHOP_FROM_LABEL') . '</label>';
            $html .= '<input class="date-input group-control" id="jeproshop-date-start" placeholder="Start" type="text" name="date_from" value="';
            $html .= $this->getDateFrom() . '" data-date-format="' . $this->getDateFormat() . '" tabindex="1" /><label>' . JText::_('COM_JEPROSHOP_TO_LABEL');
            $html .= '</label><input class="date-input group-control" id="jeproshop-date-end" placeholder="End" type="text" name="date_to" value="';
            $html .= $this->getDateTo() . '" data-date-format="' . $this->getDateFormat() . '" tabindex="2" /></div></div><div id="date-compare" class="form-date-group" >';
            $html .= '<div class="form-date-heading" ><span class="checkbox-title" ><label ><input type="checkbox" id="date-picker-compare" name="datepicker_compare" ';
            if(isset($compareDateFrom) && isset($compareDateTo)){ $html .= ' checked="checked" '; }
            $html .= ' tabindex="3"> ' . JText::_('COM_JEPROSHOP_COMPARE_TO_LABEL') . '</label></span>';
            $html .= '<select id="compare-options" class="group-control fixed-width-lg pull-right" name="compare_date_option" ';
            $html .= ((is_null($compareDateFrom) || is_null($compareDateTo)) ? ' disabled="disabled" ' : '') . '> ';
            $html .= '<option value="1" ' . (($this->getCompareOption() == 1) ? ' selected="selected" ' : '') . ' label="';
            $html .= JText::_('COM_JEPROSHOP_PREVIOUS_PERIOD_LABEL') . '">' . JText::_('COM_JEPROSHOP_PREVIOUS_PERIOD_LABEL');
            $html .= '</option><option value="2" ' . (($this->getCompareOption() == 2) ? ' selected="selected" ' : '') . ' label="';
            $html .= JText::_('COM_JEPROSHOP_PREVIOUS_YEAR_LABEL') . '"> ' . JText::_('COM_JEPROSHOP_PREVIOUS_YEAR_LABEL') . '</option>';
			$html .= '<option value="3" ' . (($this->getCompareOption() == 3) ? ' selected="selected" ' : '') . ' label="';
            $html .= JText::_('COM_JEPROSHOP_CUSTOM_LABEL') . '">' . JText::_('COM_JEPROSHOP_CUSTOM_LABEL') . '</option></select></div>';
            $html .= '<div class="form-date-body" id="form-date-body-compare" ' . ((is_null($compareDateFrom) || is_null($compareDateTo)) ? ' style="display: none;"' : '') ;
            $html .= ' ><label>' . JText::_('COM_JEPROSHOP_FROM_LABEL') . '</label><input id="jeproshop-date-start-compare" class="date-input group-control" type="text" placeholder="Start" ';
            $html .= 'name="compare_date_from" value="' . $compareDateFrom . '" data-date-format="' . $this->getDateFormat() . '" tabindex="4" /><label>';
            $html .= JText::_('COM_JEPROSHOP_TO_LABEL') . '</label><input id="jeproshop-date-end-compare" class="date-input form-control" type="text" placeholder="End" name="compare_date_to" value="';
            $html .= $compareDateTo . '" data-date-format="' .  $this->getDateFormat() . '" tabindex="5" /> </div></div>';
            $html .= '<div class="form-date-actions" ><button class="btn btn-link" type="button" id="date-picker-cancel" tabindex="7">';
            $html .= '<i class="icon-remove"></i>' . JText::_('COM_JEPROSHOP_CANCEL_LABEL') ; // d='Admin.Actions'}
            $html .= '</button>';
			$html .= '<button class="btn btn-default pull-right" type="submit" name="submitDateRange" tabindex="6" ><i class="icon-ok text-success" ></i>';
            $html .= JText::_('COM_JEPROSHOP_APPLY_LABEL') ; // d='Admin.Actions'}
			$html .= '</button></div></div></div></div></div></div></div>';

            $script = 'var translatedDates = {
                days: ["' . JText::_('COM_JEPROSHOP_SUNDAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MONDAY_LABEL'). '", "'
                . JText::_('COM_JEPROSHOP_TUESDAY_LABEL') .'", "' . JText::_('COM_JEPROSHOP_WEDNESDAY_LABEL') . '", "'
                . JText::_('COM_JEPROSHOP_THURSDAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FRIDAY_LABEL') . '", "'
                . JText::_('COM_JEPROSHOP_SATURDAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SUNDAY_LABEL') . '"],
		        daysShort: ["' . JText::_('COM_JEPROSHOP_SUN_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MON_LABEL') . '", "'
                . JText::_('COM_JEPROSHOP_TUE_LABEL') .'", "' . JText::_('COM_JEPROSHOP_WED_LABEL') . '", "'
                . JText::_('COM_JEPROSHOP_THU_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FRI_LABEL') . '", "' .  JText::_('COM_JEPROSHOP_SAT_LABEL') .
                '", "' . JText::_('COM_JEPROSHOP_SUN_LABEL') . '"], daysMin: ["' . JText::_('COM_JEPROSHOP_SU_LABEL') . '", "'
                . JText::_('COM_JEPROSHOP_MO_LABEL') . '", "' . JText::_('COM_JEPROSHOP_TU_LABEL') . '", "' . JText::_('COM_JEPROSHOP_WE_LABEL') .
                '", "' . JText::_('COM_JEPROSHOP_TH_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FR_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SA_LABEL') .
                '", "' . JText::_('COM_JEPROSHOP_SU_LABEL') . '"], months: ["' . JText::_('COM_JEPROSHOP_JANUARY_LABEL') . '", "' .
                JText::_('COM_JEPROSHOP_FEBRUARY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MARCH_LABEL') . '", "' . JText::_('COM_JEPROSHOP_APRIL_LABEL') .
                '", "' . JText::_('COM_JEPROSHOP_MAY_LABEL') . '", "' . JText::_('COM_JEPROSHOP_JUNE_LABEL') . '", "' . JText::_('COM_JEPROSHOP_JULY_LABEL').
                '", "' . JText::_('COM_JEPROSHOP_AUGUST_LABEL') . '", "' . JText::_('COM_JEPROSHOP_SEPTEMBER_LABEL') . '", "' .
                JText::_('COM_JEPROSHOP_OCTOBER_LABEL') . '", "' . JText::_('COM_JEPROSHOP_NOVEMBER_LABEL') . '", "' . JText::_('COM_JEPROSHOP_DECEMBER_LABEL') .
                '"], monthsShort: ["' . JText::_('COM_JEPROSHOP_JAN_LABEL') . '", "' . JText::_('COM_JEPROSHOP_FEB_LABEL') . '", "' .
                JText::_('COM_JEPROSHOP_MAR_LABEL') . '", "' . JText::_('COM_JEPROSHOP_APR_LABEL') . '", "' . JText::_('COM_JEPROSHOP_MAY_LABEL') . '", "' .
                JText::_('COM_JEPROSHOP_JUN_LABEL') . '", "' . JText::_('COM_JEPROSHOP_JUL_LABEL') . '", "' . JText::_('COM_JEPROSHOP_AUG_LABEL') . '", "' .
                JText::_('COM_JEPROSHOP_SEP_LABEL') . '", "' . JText::_('COM_JEPROSHOP_OCT_LABEL') . '", "' . JText::_('COM_JEPROSHOP_NOV_LABEL') . '", "' .
                JText::_('COM_JEPROSHOP_DEC_LABEL') . '"] }; ';
            //JFactory::getDocument()->addScriptDeclaration($script);
            $script .= 'jQuery(document).ready(function(){
                    jQuery("#jeproshop-date-picker").JeproCalendar({
                        wrapper : "jeproshop-date-picker",
                        start_date_class : "start-date-picker",
                        end_date_class : "end-date-picker",
                        start_date : "jeproshop-date-start",
                        end_date : "jeproshop-date-end",
                        date_input : "date-input",
                        translated_dates: translatedDates
                    });
                });';
            JFactory::getDocument()->addScriptDeclaration($script);
        }
        return $html;
    }

}