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

class JeproshopViewLegacy extends JViewLegacy{
    public $context = null;

    public $side_bar;

    protected $pagination;
    
    protected function addSideBar($active){
        $dashboard = $catalog = $orders = $customers = $price = $shipping = $localisation = $setting = $administration = $stats = false;

        switch($active){
            case 'catalog' : $catalog = true; break;
            case 'order' : $orders = true; break;
            case 'customers' : $customers = true; break;
            case 'price' : $price = true; break;
            case 'shipping' : $shipping = true; break;
            case 'localisation' : $localisation = true; break;
            case 'settings' : $setting = true; break;
            case 'administration' : $administration = true; break;
            case 'stats' : $stats = true; break;
            default :
                $dashboard = true; break;
        }
        
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_DASHBOARD_LABEL'), 'index.php?option=com_jeproshop', $dashboard);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_CATALOG_LABEL'), 'index.php?option=com_jeproshop&task=catalog', $catalog);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_ORDERS_LABEL'), 'index.php?option=com_jeproshop&task=orders', $orders);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_CUSTOMERS_LABEL'), 'index.php?option=com_jeproshop&task=customers', $customers);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_PRICE_RULES_LABEL'), 'index.php?option=com_jeproshop&task=price_rules', $price);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_SHIPPING_LABEL'), 'index.php?option=com_jeproshop&task=shipping', $shipping);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_LOCALIZATION_LABEL'), 'index.php?option=com_jeproshop&task=localization', $localisation);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_SETTINGS_LABEL'), 'index.php?option=com_jeproshop&task=settings', $setting);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_ADMINISTRATION_LABEL'), 'index.php?option=com_jeproshop&task=administration', $administration);
        JHtmlSidebar::addEntry(JText::_('COM_JEPROSHOP_STATS_LABEL'), 'index.php?option=com_jeproshop&task=stats', $stats);

        $document = JFactory::getDocument();
        
        $themesDir = JeproshopContext::getContext()->shop->theme_directory;
        $themesDir = ($themesDir ? $themesDir : 'default');
        $document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $themesDir . '/css/jeproshop.css');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('jquery.framework');

        $this->side_bar = JHtmlSideBar::render();
    }

    public function setCatalogSubMenu($active){

        $product = $category = $tracking = $attribute = $feature = $manufacturer = $supplier = $tag = $attachment = "";

        switch($active){
            case 'category' : $category = "btn-success"; break;
            case 'tracking' : $tracking = "btn-success"; break;
            case 'attribute' : $attribute = "btn-success"; break;
            case 'feature' : $feature = "btn-success"; break;
            case 'manufacturer' : $manufacturer = "btn-success"; break;
            case 'supplier' : $supplier = "btn-success"; break;
            case 'tag' : $tag = "btn-success"; break;
            case 'attachment' : $attachment = "btn-success"; break;
            default : $product = "btn-success"; break;
        }

        $html = '<div class="box_wrapper jeproshop_sub_menu_wrapper">' .
            '<fieldset class="btn-group">' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=product') . '" class="btn jeproshop_sub_menu ' . $product . '" ><i class="icon-product" ></i>' . JText::_('COM_JEPROSHOP_PRODUCTS_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=category') . '" class="btn jeproshop_sub_menu ' . $category . '" ><i class="icon-category" ></i>' . JText::_('COM_JEPROSHOP_CATEGORIES_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=tracking') . '" class="btn jeproshop_sub_menu ' . $tracking . '" ><i class="icon-monitoring" ></i>' . JText::_('COM_JEPROSHOP_TRACKING_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=attribute') . '" class="btn jeproshop_sub_menu ' . $attribute . '" ><i class="icon-attribute" ></i>' . JText::_('COM_JEPROSHOP_ATTRIBUTES_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=feature') . '" class="btn jeproshop_sub_menu ' . $feature . '" ><i class="icon-feature" ></i>' . JText::_('COM_JEPROSHOP_FEATURES_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=manufacturer') . '" class="btn jeproshop_sub_menu ' . $manufacturer . '" ><i class="icon-manufacturer" ></i>' . JText::_('COM_JEPROSHOP_MANUFACTURERS_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=supplier') . '" class="btn jeproshop_sub_menu ' . $supplier . '" ><i class="icon-supplier" ></i>' . JText::_('COM_JEPROSHOP_SUPPLIERS_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=tag') . '" class="btn jeproshop_sub_menu ' . $tag . '" ><i class="icon-tag" ></i>' . JText::_('COM_JEPROSHOP_TAGS_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=attachment') . '" class="btn jeproshop_sub_menu ' . $attachment . '" ><i class="icon-attachment" ></i>' . JText::_('COM_JEPROSHOP_ATTACHMENTS_LABEL') . '</a>' .
            '</fieldset>' .
            '</div>';
        return $html;
    }
}