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

        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_setting_test') . " WHERE 0";
        $db->setQuery($query);
        $db->query();

        if(file_exists($settingsXmlFile)) {
            $settingsXml = simplexml_load_file($settingsXmlFile);
            $query = "INSERT INTO " . $db->quoteName('#__jeproshop_setting_test') . "(" . $db->quoteName('setting_id') . ", " . $db->quoteName('name') . ", ";
            $query .= $db->quoteName('value') . ", " . $db->quoteName('setting_group') . ", " . $db->quoteName('date_add');
            $query .= ", " . $db->quoteName('date_upd');
            $queryValues = "";
            $index = 1;
            foreach($settingsXml as $item){
                $queryValues .= " (" . $index . ", " . $db->quote($item['name']) . ", " . $db->quote($item['value']) . ", " . $db->quote($item['group']);
                $queryValues .= ", " . $db->quote(date('Y-m-d H:i:s')) . ", " . $db->quote(date('Y-m-d H:i:s')) . "), ";
                $index++;
            }
            $query .= ") VALUES " . $queryValues ;
            $db->setQuery(rtrim($query, ', '));
            //$db->query();

            $settingRedirection = 'index.php?option=com_jeproshop&view=setting';
            JFactory::getApplication()->redirect($settingRedirection);

        }
    }
}