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

JHtml::_('jquery.framework');
JHtml::_('jquery.ui');

$app = JFactory::getApplication();
$document = JFactory::getDocument();
$themeDir = isset(JeproshopContext::getContext()->shop->theme->directory) ? JeproshopContext::getContext()->shop->theme->directory : 'default';
$document->addScript(JURI::base(). 'components/com_jeproshop/assets/javascript/jquery/plugins/jquery.tablednd.js');
$document->addScript(JURI::base(). 'components/com_jeproshop/assets/javascript/jquery/ui/jquery.ui.datepicker.min.js');
$document->addScript(JURI::base(). 'components/com_jeproshop/assets/themes/' . $themeDir. '/js/tools.js');
$document->addScript(JURI::base(). 'components/com_jeproshop/assets/themes/' . $themeDir. '/js/product.js');


$script = 'jQuery(document).ready(function() {' ;
$script .= 'var taxesArray = [], productImages = [], assoc; ';
if($this->product->product_id) {
    foreach ($this->taxesRatesByGroup as $taxByGroup) {
        $script .= 'taxesArray[' . $taxByGroup['tax_rules_group_id'] . '] = ' . json_encode($taxByGroup) . '; ';
    }

    foreach ($this->images as $image) {
        $script .= ' assoc = "{"; ';
        if ($this->shops != null) {
            foreach ($this->shops as $shop) {
                $script .= ' assoc += "' . $shop->shop_id . '" : ' . (($image->isAssociatedToShop($shop->shop_id)) ? 1 : 0) . ', ';
            }
        }

        $script .= ' if(assoc != "{"){ assoc = assoc.slice(0, -1) +  "}"; }else{ assoc = false; } ';
        $script .= 'var imageParams = {"icon_checked" : ' . (($image->cover == 1) ? '\'icon-check-sign\'' : '\'icon-check-empty\'') . ', ';
        $script .= '"image_id" : parseInt(' . $image->image_id . '), "path" : "' . $this->context->controller->getProductImageLink("", $this->product->product_id . '_' . $image->image_id, "default_cart") . '", "position" : parseInt(';
        $script .= $image->position . ') , assoc, "legend" : "' . $image->legend[$this->context->language->lang_id] . '" };';

        $script .= 'productImages.push(imageParams);  ';
    }
}

$currencies =  array();
foreach(JeproshopCurrencyModelCurrency::getStaticCurrencies() as $currency){
    $currencyData = array();
    $currencyData['currency_id'] = $currency->currency_id;
    $currencyData['name'] = $currency->name;
    $currencyData['sign'] = $currency->sign;
    $currencyData['format'] = $currency->format;
    $currencies[$currency->currency_id] = $currencyData;
}

$script  .= ' jQuery("#jform_product_edit_form").JeproProduct({ ' .
    'product_id : parseInt(' . $this->product->product_id . '), ' .
    'no_tax : '  . (isset($this->tax_exclude_tax_option) ? 1 : 0) . ', ' .
    'eco_tax_tax_rate : '  . (isset($this->ecotaxTaxRate) ? ($this->ecotaxTaxRate / 100) : 0.00). ', ' .
    'eco_tax_tax_excluded : parseFloat(' . (isset($this->ecotax_tax_excluded) ? $this->ecotax_tax_excluded : 0.00) . '), ' .
    'price_display_precision : parseInt(' .  (int)JeproshopSettingModelSetting::getValue('price_display_precision') . '), ' .
    'currencies : ' . json_encode($currencies) . ', ' .
    'taxes : taxesArray, ' .
    'all_customers_label  : "' . JText::_('COM_JEPROSHOP_ALL_CUSTOMERS_LABEL') . '", ' .
    'no_customers_label : "' . JText::_('COM_JEPROSHOP_NO_CUSTOMERS_LABEL') . '", ' .
    'delete_price_rule_message : "' . JText::_('COM_JEPROSHOP_DO_YOU_REALLY_WANT_TO_REMOVE_THIS_PRICE_RULE_MESSAGE') . '", ' .
    'product_images : productImages,' .
    'product_token : "' . JeproshopTools::getProductToken() . '", ' .
    'customer_token : "' . JeproshopTools::getCustomerToken() . '" ' .
    '}) }); ';
$document->addScriptDeclaration($script);
?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=product'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container"  <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('product'); ?>
        <div class="separation" ></div>
        <div id="jform_product_edit_form" style="width: 100%;">
            <?php
            echo JHtml::_('bootstrap.startTabSet', 'product_form', array('active' =>'information'));
            echo JHtml::_('bootstrap.addTab', 'product_form', 'information', JText::_('COM_JEPROSHOP_INFORMATION_LABEL')) . $this->loadTemplate('information') . JHtml::_('bootstrap.endTab');
            if($this->product->product_id) {
                echo JHtml::_('bootstrap.addTab', 'product_form', 'price', JText::_('COM_JEPROSHOP_PRICE_LABEL')) . $this->loadTemplate('price') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'seo', JText::_('COM_JEPROSHOP_SEO_LABEL')) . $this->loadTemplate('referencing') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'associations', JText::_('COM_JEPROSHOP_ASSOCIATION_LABEL')) . $this->loadTemplate('association') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'declinations', JText::_('COM_JEPROSHOP_DECLINATIONS_LABEL')) . $this->loadTemplate('declination') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'quantities', JText::_('COM_JEPROSHOP_QUANTITIES_LABEL')) . $this->loadTemplate('quantities') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'images', JText::_('COM_JEPROSHOP_IMAGES_LABEL')) . $this->loadTemplate('images') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'features', JText::_('COM_JEPROSHOP_CHARACTERISTICS_LABEL')) . $this->loadTemplate('features') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'customization', JText::_('COM_JEPROSHOP_PERSONALIZATION_LABEL')) . $this->loadTemplate('customization') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'join_files', JText::_('COM_JEPROSHOP_JOIN_FILE_LABEL')) . $this->loadTemplate('join_files') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'shipping', JText::_('COM_JEPROSHOP_SHIPPING_LABEL')) . $this->loadTemplate('shipping') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'supplier', JText::_('COM_JEPROSHOP_SUPPLIER_LABEL')) . $this->loadTemplate('supplier') . JHtml::_('bootstrap.endTab');
                echo JHtml::_('bootstrap.addTab', 'product_form', 'developer', JText::_('COM_JEPROSHOP_DEVELOPER_LABEL')) . $this->loadTemplate('developer') . JHtml::_('bootstrap.endTab');
            }
            echo JHtml::_('bootstrap.endTabSet');
            ?>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="product_id" value="<?php echo $this->product->product_id; ?>" />
        <input type="hidden" name="return" value="<?php echo $app->input->get('return'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>