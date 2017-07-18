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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=manufacturer'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('manufacturer'); ?>
        <div class="separation" ></div>
        <div class="panel" >
            <div class="panel-content well" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_name" title="<?php echo JText::_('COM_JEPROSHOP_MANUFACTURER_NAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_NAME_LABEL'); ?></label> </div>
                    <div class="controls" ><input type="text" name="jform[name]" id="jform_name" value="<?php echo $this->manufacturer->name; ?>" required="required" /></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_MANUFACTURER_SHORT_DESCRIPTION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_SHORT_DESCRIPTION_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageTextAreaField('short_description', 'jform', false, $this->manufacturer->short_description); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_description" title="<?php echo JText::_('COM_JEPROSHOP_MANUFACTURER_DESCRIPTION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_DESCRIPTION_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageTextAreaField('description', 'jform', false, $this->manufacturer->description); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_logo" title="<?php echo JText::_('COM_JEPROSHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_LOGO_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->inputFileUploader('logo', ''); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_meta_title" title="<?php echo JText::_('COM_JEPROSHOP_META_TITLE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_TITLE_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('meta_title', 'jform', false, $this->manufacturer->meta_title); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_meta_description" title="<?php echo JText::_('COM_JEPROSHOP_META_DESCRIPTION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_DESCRIPTION_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('meta_description', 'jform', false, $this->manufacturer->meta_description); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_meta_keywords" title="<?php echo JText::_('COM_JEPROSHOP_META_KEYWORDS_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_KEYWORDS_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('meta_keywords', 'jform', false, $this->manufacturer->meta_keywords); ?></div>
                </div>
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_published" title="<?php echo JText::_('COM_JEPROSHOP_PUBLISHED_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PUBLISHED_LABEL'); ?></label> </div>
                    <div class="controls" ><?php echo $this->helper->radioButton('published', 'edit', $this->manufacturer->published); ?></div>
                </div>
                <?php if(JeproshopShopModelShop::isFeaturePublished()){ ?>
                    <div class="control-group" >
                        <div class="control-label" ><label for="jform_" title="<?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_LABEL'); ?></label> </div>
                        <div class="controls" ><?php echo $this->shop_tree; ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
