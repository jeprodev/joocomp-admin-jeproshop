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

        $html = '<div class="box_wrapper jeproshop_sub_menu_wrapper" >' .
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

    public function setOrdersSubMenu($active){
        $order = $invoices = $returns = $delivery = $refund = $status = $messages = "";

        switch($active){
            case 'invoices' : $invoices = "btn-success"; break;
            case 'returns' : $returns = "btn-success"; break;
            case 'delivery' : $delivery = "btn-success"; break;
            case 'refund' : $refund = "btn-success"; break;
            case 'status' : $status = "btn-success"; break;
            case 'messages' : $messages = "btn-success"; break;
            default : $order = "btn-success"; break;
        }

        $html = '<div class="box-wrapper jeproshop_sub_menu_wrapper" >' .
            '<fieldset class="btn-group">' .
                '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=order') . '" class="btn jeproshop_sub_menu ' . $order . '" ><i class="icon-order" ></i> ' . JText::_('COM_JEPROSHOP_ORDERS_LABEL'). '</a>'  .
                '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=order&render=invoices') . '" class="btn jeproshop_sub_menu ' . $invoices . '" ><i class="icon-bill" ></i> ' .  JText::_('COM_JEPROSHOP_INVOICES_LABEL'). '</a>' .
                '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=order&render=returns') . '" class="btn jeproshop_sub_menu ' . $returns . '" ><i class="icon-returns" ></i> ' .  JText::_('COM_JEPROSHOP_RETURNS_LABEL'). '</a>' .
                '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=order&render=delivery') . '" class="btn jeproshop_sub_menu ' . $delivery . '" ><i class="icon-delivery" ></i> ' . JText::_('COM_JEPROSHOP_DELIVERY_LABEL'). '</a>' .
                '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=order&render=refund'). '" class="btn jeproshop_sub_menu ' . $refund . '" ><i class="icon-refund" ></i> ' .  JText::_('COM_JEPROSHOP_REFUNDS_LABEL'). '</a>' .
                '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=order&render=status') . '" class="btn jeproshop_sub_menu ' . $status . '" ><i class="icon-order-status" ></i> ' .  JText::_('COM_JEPROSHOP_STATUS_LABEL'). '</a>' .
                '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=order&render=messages') . '" class="btn jeproshop_sub_menu ' . $messages . '" ><i class="icon-messages" ></i> ' .  JText::_('COM_JEPROSHOP_MESSAGES_LABEL'). '</a>' .
            '</fieldset>' .
        '</div>';
        return $html;
    }


    public function renderCustomerSubMenu($current = 'customer'){
        $html = '<div class="box_wrapper jeproshop_sub_menu_wrapper">' .
            '<fieldset class="btn-group" >' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=customer') . '" class="btn jeproshop_sub_menu ' . (($current == 'customer' ) ? 'btn-success' : '') . '" ><i class="icon-customer" ></i> ' . ucfirst(JText::_('COM_JEPROSHOP_CUSTOMERS_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=address') . '" class="btn jeproshop_sub_menu ' . (($current == 'address' ) ? 'btn-success' : '') . '" ><i class="icon-address" ></i> '. ucfirst(JText::_('COM_JEPROSHOP_ADDRESSES_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=group') . '" class="btn jeproshop_sub_menu ' . (($current == 'group' ) ? 'btn-success' : '') . '" ><i class="icon-group" ></i> ' . ucfirst(JText::_('COM_JEPROSHOP_GROUPS_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=cart') . '" class="btn jeproshop_sub_menu ' . (($current == 'cart' ) ? 'btn-success' : '') . '" ><i class="icon-cart" ></i> ' . ucfirst(JText::_('COM_JEPROSHOP_SHOPPING_CARTS_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=customer&task=threads') . '" class="btn jeproshop_sub_menu ' . (($current == 'threads' ) ? 'btn-success' : '') . '" ><i class="icon-thread" ></i> ' .  ucfirst(JText::_('COM_JEPROSHOP_CUSTOMER_THREADS_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=contact') . '" class="btn jeproshop_sub_menu ' . (($current == 'contact' ) ? 'btn-success' : '') . '" ><i class="icon-contact" ></i> ' . ucfirst(JText::_('COM_JEPROSHOP_CONTACTS_LABEL')) . '</a>' .
            '</fieldset></div>';
        return $html;
    }

    public function renderShippingSubMenu($active){

    }

    public function renderLocalisationSubMenu($current = 'country'){
        $html = '<fieldset class="btn-group" >' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=country') . '" class="btn jeproshop_sub_menu' . (($current == 'country') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_COUNTRIES_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=country&task=zone') . '" class="btn jeproshop_sub_menu' . (($current == 'zones') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_ZONES_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=country&task=states') . '" class="btn jeproshop_sub_menu' . (($current == 'states') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_STATES_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_languages') . '" class="btn jeproshop_sub_menu' . (($current == 'languages') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_LANGUAGES_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=currency') . '" class="btn jeproshop_sub_menu' . (($current == 'currency') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_CURRENCIES_LABEL')) . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=tax') . '" class="btn jeproshop_sub_menu' . (($current == 'tax') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_TAXES_LABEL')) . '</a>' .
        //$script .= '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=tax&task=rules') . '" class="btn jeproshop_sub_menu' . (($current == 'rules') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_TAX_RULES_LABEL')) . '</a>';
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=tax&task=rule_group') . '" class="btn jeproshop_sub_menu' . (($current == 'rule_group') ? ' btn-success' : '') . '" >' . ucfirst(JText::_('COM_JEPROSHOP_TAX_RULES_GROUP_LABEL')) . '</a>' .
            '</fieldset>';

        return $html;
    }

    public function renderSettingSubMenu($current = 'general'){
        $html = '<div class="form_box_wrapper jeproshop_sub_menu_wrapper">' .
            '<fieldset class="btn-group">' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=setting&task=general') . '" class="btn jeproshop_sub_menu' . (($current == '' || $current == 'general') ? ' btn-success' : '') . '" ><i class="icon-gears" ></i> '. JText::_('COM_JEPROSHOP_GENERAL_LABEL') . '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=setting&task=order') . '" class="btn jeproshop_sub_menu' . (($current == 'order' ) ? ' btn-success' : '') . '" ><i class="icon-gears" ></i> ' . JText::_('COM_JEPROSHOP_ORDERS_LABEL') . '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=setting&task=product') . '" class="btn jeproshop_sub_menu' . (($current == 'product' ) ? ' btn-success' : '') . '" ><i class="icon-gears" ></i> ' . JText::_('COM_JEPROSHOP_PRODUCTS_LABEL') . '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=setting&task=customer') . '" class="btn jeproshop_sub_menu' . (($current == 'customer' ) ? ' btn-success' : '') . '" ><i class="icon-gears" ></i> ' . JText::_('COM_JEPROSHOP_CUSTOMERS_LABEL') . '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=theme') . '" class="btn jeproshop_sub_menu' . (($current == 'theme' ) ? ' btn-success' : '') . '" ><i class="icon-themes' . (($current == '' ) ? ' btn-success' : '') . '" ></i> ' . JText::_('COM_JEPROSHOP_THEMES_LABEL') . '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=image') . '" class="btn jeproshop_sub_menu' . (($current == 'image' ) ? ' btn-success' : '') . '" ><i class="icon-image" ></i> ' . JText::_('COM_JEPROSHOP_IMAGES_LABEL') . '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=shop') . '" class="btn jeproshop_sub_menu' . (($current == 'shop' ) ? ' btn-success' : '') . '" ><i class="icon-shop" ></i> ' . JText::_('COM_JEPROSHOP_SHOP_STORE_LABEL') . '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=setting&task=search') . '" class="btn jeproshop_sub_menu' . (($current == 'search' ) ? ' btn-success' : '') . '" ><i class="icon-search" ></i> ' . JText::_('COM_JEPROSHOP_SEARCH_LABEL'). '</a>' .
            	'<a href="' . JRoute::_('index.php?option=com_jeproshop&view=setting&task=geolocation') . '" class="btn jeproshop_sub_menu' . (($current == 'geolocation' ) ? ' btn-success' : '') . '" ><i class="icon-globe" ></i> ' . JText::_('COM_JEPROSHOP_GEOLOCATION_LABEL') . '</a>' .
            '</fieldset>' .
        '</div>';

        return $html;
    }

    public function renderAdministrationSubMenu($current){
        $shop = $shopGroup = false;
        switch($current){
            case 'shop_group' : $shopGroup = true; break;
            default : $shop = true; break;
        }

        $html = '<div class="form_box_wrapper jeproshop_sub_menu_wrapper" >' .
            '<fieldset class="btn-group" >' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=shop') . '" class="btn jeproshop_sub_menu ' . ($shop ? 'btn-success' : '') . '" >' . JText::_('COM_JEPROSHOP_SHOP_LABEL') . '</a>' .
            '<a href="' . JRoute::_('index.php?option=com_jeproshop&view=shop&tab=group') . '" class="btn jeproshop_sub_menu ' . ($shopGroup ? 'btn-success' : '') . '" >' . JText::_('COM_JEPROSHOP_SHOP_GROUP_LABEL') . '</a>' .
            '</fieldset>' .
            '</div>';

        return $html;
    }
}