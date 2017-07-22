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

class JeproshopCurrencyViewCurrency extends  JeproshopViewLegacy {
    public $currency;

    public $helper;

    public function renderDetails($tpl = NULL){
        $currencyModel = new JeproshopCurrencyModelCurrency();
        $currencies = $currencyModel->getCurrenciesList();
        $zones = JeproshopZoneModelZone::getZones();
        $this->assignRef('currencies', $currencies);

        $this->addToolBar();
        parent::display($tpl);
    }

    public function renderEditForm($tpl = null){
        $this->helper = new JeproshopHelper();
        $this->addToolBar();
        parent::display($tpl);
    }

    private function addToolBar(){
        $task = JFactory::getApplication()->input->get('task');
        switch($task){
            case 'add':
                JToolbarHelper::title(JText::_('COM_JEPROSHOP_ADD_CURRENCY_TITLE'), 'currency-jeproshop');
                JToolbarHelper::apply('add');
                break;
            case 'edit':
                JToolbarHelper::title(JText::_('COM_JEPROSHOP_EDIT_CURRENCY_TITLE'), 'currency-jeproshop');
                JToolbarHelper::apply('update', JText::_('COM_JEPROSHOP_UPDATE_LABEL'));
                break;
            default:
                JToolbarHelper::title(JText::_('COM_JEPROSHOP_CURRENCIES_LIST_TITLE'), 'currency-jeproshop');
                JToolbarHelper::addNew('add');
                break;
        }
        $this->addSideBar('localisation');
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

        $currencyId = (int)$app->input->get('currency_id');
        if ($currencyId && JeproshopTools::isUnsignedInt($currencyId)) {
            if (!$this->currency) {
                $this->currency = new JeproshopCurrencyModelCurrency($currencyId);
            }
            if (JeproshopTools::isLoadedObject($this->currency, 'currency_id'))
                return $this->currency;
            // throw exception
            JError::raiseError(500, 'The currency cannot be loaded (or not found)');
            return false;
        } elseif ($opt) {
            if (!$this->currency)
                $this->currency = new JeproshopCurrencyModelCurrency();
            return $this->currency;
        } else {
            $this->context->controller->has_errors = true;
            JError::displayError(500, 'The currency cannot be loaded (the identifier is missing or invalid)');
            return false;
        }
    }
}