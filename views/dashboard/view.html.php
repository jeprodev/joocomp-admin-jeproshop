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

class JeproshopViewDashboard extends JeproshopViewLegacy{


    public function display($tpl = null){
        $app = JFactory::getApplication();
        $this->context = JeproshopContext::getContext();

        $testStatsDateUpdate = $this->context->cookie->__get('stats_date_update');
        if(!empty($testStatsDateUpdate) && $this->context->cookie->__get('stats_date_update') < strtotime(date('Y-m-d'))){
            switch ($this->context->employee->preselect_date_range){
                case 'day' :
                    $dateFrom = date('Y-m-d');
                    $dateTo = date('Y-m-d');
                    break;
                case 'prev-day':
                    $dateFrom = date('Y-m-d', strtotime('-1 day'));
                    $dateTo = date('Y-m-d', strtotime('-1 day'));
                    break;
                case 'prev-month':
                    $dateFrom = date('Y-m-01', strtotime('-1 month'));
                    $dateTo = date('Y-m-t', strtotime('-1 month'));
                    break;
                case 'year':
                    $dateFrom = date('Y-01-01');
                    $dateTo = date('Y-m-d');
                    break;
                case 'prev-year':
                    $dateFrom = date('Y-m-01', strtotime('-1 year'));
                    $dateTo = date('Y-12-t', strtotime('-1 year'));
                    break;
                case 'month':
                default:
                    $dateFrom = date('Y-m-01');
                    $dateTo = date('Y-m-d');
                    break;
            }

            $this->context->employee->stats_date_from = $dateFrom;
            $this->context->employee->stats_date_to = $dateTo;
            $this->context->employee->update();
            $this->context->cookie->__set('stats_date_update', strtotime(date('Y-m-d')));
            $this->context->cookie->write();
        }

        $calendarHelper = new JeproshopCalendarHelper('calendar');

        $calendarHelper->setDateFrom(JeproshopTools::getValue('date_from', $this->context->employee->stats_date_from));
        $calendarHelper->setDateTo(JeproshopTools::getValue('date_to', $this->context->employee->stats_date_to));

        $statsCompareFrom = $this->context->employee->stats_compare_from;
        $statsCompareTo = $this->context->employee->stats_compare_to;

        if (is_null($statsCompareFrom) || $statsCompareFrom == '0000-00-00') {
            $statsCompareFrom = null;
        }

        if (is_null($statsCompareTo) || $statsCompareTo == '0000-00-00') {
            $statsCompareTo = null;
        }

        $calendarHelper->setCompareDateFrom($statsCompareFrom);
        $calendarHelper->setCompareDateTo($statsCompareTo);
        $calendarHelper->setCompareOption(JeproshopTools::getValue('compare_date_option', $this->context->employee->stats_compare_option));

  /*
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();


        $this->tpl_view_vars = array( */
        $this->context->employee->preselect_date_range = (isset($this->context->employee->preselect_date_range) ? $this->context->employee->preselect_date_range : 'month');
        $this->assignRef('date_from', $this->context->employee->stats_date_from);
        $this->assignRef('date_to', $this->context->employee->stats_date_to);
        $dashboardZoneOne = '';
        $this->assignRef('dashboard_zone_one', $dashboardZoneOne);
        $dashboardZoneTwo = '';
        $this->assignRef('dashboard_zone_two', $dashboardZoneTwo);/*
            'hookDashboardZoneOne' => Hook::exec('dashboardZoneOne', $params),
            'hookDashboardZoneTwo' => Hook::exec('dashboardZoneTwo', $params),
            //'translations' => $translations,
            'action' => '#',
            'warning' => $this->getWarningDomainName(),
            'new_version_url' => Tools::getCurrentUrlProtocolPrefix()._PS_API_DOMAIN_.'/version/check_version.php?v='._PS_VERSION_.'&lang='.$this->context->language->iso_code.'&autoupgrade='.(int)($moduleManager->isInstalled('autoupgrade') && $moduleManager->isEnabled('autoupgrade')).'&hosted_mode='.(int)defined('_PS_HOST_MODE_'),
            'dashboard_use_push' => Configuration::get('PS_DASHBOARD_USE_PUSH'), */
        $calendar = $calendarHelper->generate();
        $this->assignRef('calendar', $calendar);
            /*'PS_DASHBOARD_SIMULATION' => Configuration::get('PS_DASHBOARD_SIMULATION'),
            'datepickerFrom' => Tools::getValue('datepickerFrom', $this->context->employee->stats_date_from),
            'datepickerTo' => Tools::getValue('datepickerTo', $this->context->employee->stats_date_to), */
        $preselectDateRange  = $app->input->get('preselect_date_range', $this->context->employee->preselect_date_range);
        $this->assignRef('preselect_date_range', $preselectDateRange);
        //);*/
        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $document = JFactory::getDocument();
        switch ($this->getLayout()){
            case 'add':
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_ADD_NEW_CATEGORY_TITLE'), 'jeproshop-dashboard');
                JToolBarHelper::apply('save');
                JToolBarHelper::cancel('cancel');

                break;
            default:
                JToolBarHelper::title(JText::_('COM_JEPROSHOP_DASHBOARD_LABEL'), 'jeproshop-dashboard');
                //JToolBarHelper::addNew('add');
                break;
        }
        JHtml::_('bootstrap.framework');
        $this->addSideBar('dashboard');
        $themeDirectory = 'default';
        $document->addStyleSheet('components/com_jeproshop/assets/themes/' . $themeDirectory . '/css/dashboard.css');
    }
    
    
}
