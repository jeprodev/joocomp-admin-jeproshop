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
$app = JFactory::getApplication();
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