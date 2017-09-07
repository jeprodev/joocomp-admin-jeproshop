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

class com_jeproshopInstallerScript{
    /**
     * method to install the component
     *
     * @param $parent
     * @return void
     */
    function install($parent){
        $parent->getParent()->setRedirectUrl('index.php?option=com_jeproshop');
    }

    /**
     * method to be processed after uninstall the component
     *
     * @param $parent
     * @return void
     */
    function uninstall($parent){
        echo '<p>' . JText::_('COM_JEPROSHOP_UNINSTALL_TEXT_MESSAGE') .'</p>';
    }


    /**
     * method triggered after the component is updated.
     *
     * @param $parent
     * @return void
     */
    function update($parent){
        echo '<p>' . JText::sprintf('COM_JEPROSHOP_UPDATE_TEXT_MESSAGE', $parent->get('manifest')->version) . '</p>';
    }

    /**
     * method to perform before install/update/uninstall of the component
     * @param $type
     * @param $parent
     * @return void
     */
    function preflight($type, $parent){}

    /**
     * method to install the component
     * @param $type
     * @param $parent
     * @return void
     */
    function postflight($type, $parent){
        if($type == 'update'){
        }else if($type == 'install') {
            $this->createDefaultShopData();
        }
    }

    public function createDefaultShopData(){
        $db = JFactory::getDBO();
        $config = JFactory::getConfig();
        $defaultLangId = JFactory::getLanguage()->get('lang_id');
        $settingsXmlFile = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'settings.xml';

        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_setting') . " WHERE 0";
        $db->setQuery($query);
        //$db->query();
        //$this->createCurrencies();
        //$this->createCountries();
        if(file_exists($settingsXmlFile)) {
            $settingsXml = simplexml_load_file($settingsXmlFile);
            $query = "INSERT INTO " . $db->quoteName('#__jeproshop_setting') . "(" . $db->quoteName('setting_id') . ", " . $db->quoteName('name') . ", ";
            $query .= $db->quoteName('value') . ", " . $db->quoteName('setting_group') . ", " . $db->quoteName('date_add');
            $query .= ", " . $db->quoteName('date_upd');
            $queryValues = "";
            $index = 1;
            foreach($settingsXml as $item){
                $queryValues .= " (" . $index . ", " . $db->quote($item['name']) . ", " ;
                if($item['name'] == 'default_lang'){
                    $query .= (int)$defaultLangId;
                }else {
                    $query .= $db->quote($item['value']);
                }
                $queryValues .= ", " . $db->quote($item['group']) . ", " . $db->quote(date('Y-m-d H:i:s')) . ", " . $db->quote(date('Y-m-d H:i:s')) . "), ";
                $index++;
            }
            $query .= ") VALUES " . $queryValues ;
            //$db->setQuery(rtrim($query, ', '));
            //$db->query();

            /** setting default image type */

            $settingRedirection = 'index.php?option=com_jeproshop&view=setting';
            //JFactory::getApplication()->redirect($settingRedirection);

        }
    }

    private function createCurrencies(){
        $currenciesXmlFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'currencies.xml';

        if(file_exists($currenciesXmlFilePath)){
            $currenciesXml = simplexml_load_file($currenciesXmlFilePath);
            $db = JFactory::getDBO();

            $query = "INSERT INTO " . $db->quoteName('#__jeproshop_currency') . " (" . $db->quoteName('name') . ", " . $db->quoteName('iso_code') . ", ";
            $query .= $db->quoteName('iso_code_num') . ", " . $db->quoteName('sign') . ", " . $db->quoteName('blank') . ", " . $db->quoteName('format');
            $query .= ", " . $db->quoteName('decimals') . ", " . $db->quoteName('conversion_rate') . ", " . $db->quoteName('deleted') . ", " ;
            $query .= $db->quoteName('published') .") VALUES (";
            $sample = '<ul>';
            foreach($currenciesXml as $currency){
                $sample .= '<li>' . $currency['name'];
                $sample .= '</li>';
                $query .= $db->quote($currency['name']) . ", " . $db->quote($currency['iso_code']) . ", " . $db->quote($currency['iso_code_nuù']) ;
                $query .= ", " . $db->quote($currency['sign']) . ", " . (int)$currency['blank'] . ", " . (int)$currency['format'] . ", ";
                $query .= (int)$currency['decimals'] . ", " . (float)$currency['conversion_rate'] . ", " . (int)$currency['deleted'] . ", ";
                $query .= (int)$currency['published'] . "), (";
            }
            $query = rtrim($query, ", (");
            $db->setQuery($query);
            $db->query();
        }
    }

    private function createCountries(){
        $countriesText = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'countries.xml';
        $addressFormatText = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'address_format.xml'; //todo
        
        if(file_exists($countriesText)){
            $countriesXml = simplexml_load_file($countriesText);
            $langId = JeproshopLanguageModelLanguage::getLanguageByIETFCode($countriesXml['lang'])->lang_id;
            $db = JFactory::getDBO();

            foreach ($countriesXml as $zone){
                $insertZoneQuery = "INSERT INTO " .$db->quoteName('#__jeproshop_zone') . "(" . $db->quoteName('name') ;
                $insertZoneQuery .= ") VALUES (" . $db->quote($zone['name']) . ") ";

                $db->setQuery($insertZoneQuery);
                $db->query();
                $zoneId = $db->insertid();

                foreach ($zone->children() as $country){
                    $insertCountryQuery = "INSERT INTO " . $db->quoteName('#__jeproshop_country') . " (" . $db->quoteName('zone_id') . ", ";
                    $insertCountryQuery .= $db->quoteName('iso_code') . ", " . $db->quoteName('call_prefix') . ", " . $db->quoteName('published');
                    $insertCountryQuery .= ", " . $db->quoteName('contains_states') . ", " . $db->quoteName('need_identification_number') . ", ";
                    $insertCountryQuery .= $db->quoteName('need_zip_code') . ", " . $db->quoteName('zip_code_format') . ", " . $db->quoteName('display_tax_label');
                    $insertCountryQuery .= ") VALUES (" . (int)$zoneId . ", " . $db->quote($country['iso_code']) . ", " . (int)$country['call_prefix'] ;
                    $insertCountryQuery .= ", " . (int)$country['published'] . ", " . (int)$country['contains_states'] . ", " . (int)$country['need_identification_number'];
                    $insertCountryQuery .= ", " . (int)$country['need_zip_code'] . ", " . $db->quote($country['zip_code_format']) . ", ";
                    $insertCountryQuery .= (int)$country['display_tax_label'] . ") ";

                    $db->setQuery($insertCountryQuery);
                    $db->query();
                    $countryId = $db->insertid();

                    $insertCountryQuery = "INSERT INTO " . $db->quoteName('#__jeproshop_country_lang') . " (" . $db->quoteName('country_id');
                    $insertCountryQuery .= ", " . $db->quoteName('lang_id') . ", " . $db->quoteName('name') . ") VALUES (" . $countryId;
                    $insertCountryQuery .= ", " . (int)$langId . ", " . $db->quote($country['name']) . ") ";

                    $db->setQuery($insertCountryQuery);
                    $db->query();

                    if($country['contains_states'] == 1){
                        foreach ($country->children() as $state){
                            $insertStateQuery = "INSERT INTO " . $db->quoteName('#__jeproshop_state') . " (" . $db->quoteName('country_id');
                            $insertStateQuery .= ", " . $db->quoteName('zone_id') . ", " . $db->quoteName('name') . ", " . $db->quoteName('iso_code');
                            $insertStateQuery .= ", " . $db->quoteName('tax_behavior') . ", " . $db->quoteName('published') . ") VALUES (";
                            $insertStateQuery .= (int)$countryId . ", " . (int)$zoneId . ", " . $db->quote($state['name']) . ", ";
                            $insertStateQuery .= $db->quote($state['iso_code']) . ", " . (int)$state['tax_behavior'] . ", " . (int)$state['published'];
                            $insertStateQuery .= ") ";

                            $db->setQuery($insertStateQuery);
                            $db->query();
                        }
                    }
                }
            }
        }
    }
}