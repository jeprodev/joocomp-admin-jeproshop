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

?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=tax'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo $this->renderLocalisationSubMenu('tax'); ?>
        <div class="separation"></div>
        <div class="panel" >
            <div class="panel-title" ><?php
                if(isset($this->tax)) {
                    echo JText::_('COM_JEPROSHOP_EDIT_TAX_LABEL');
                }else{
                    echo JText::_('COM_JEPROSHOP_ADD_TAX_LABEL');
                }?>
            </div>
            <div class="panel-content well" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_name" title="<?php echo JText::_('COM_JEPROSHOP_TAX_NAME_TO_DISPLAY_IN_CARTS_AND_ON_INVOICES_TITLE_DESC') . ' - ' . JText::_('COM_JEPROSHOP_INVALID_CHARACTERS') .' <>;=#{}'; ?>" ><?php echo JText::_('COM_JEPROSHOP_TAX_NAME_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('name', 'jform', true, $this->tax->name, null, ''); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_tax_rate" title="<?php echo JText::_('COM_JEPROSHOP_FORMAT_XX_XX_OR_XX_XXX_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_TAX_RATE_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" id="jform_tax_rate" name="jform[tax_rate]" maxlength="6" required="required" value="<?php echo $this->tax->rate; ?>"/></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_published" title="<?php echo JText::_('COM_JEPROSHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PUBLISHED_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('published', 'edit', $this->tax->published); ?></div>
                </div>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
