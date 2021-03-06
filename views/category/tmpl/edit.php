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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=category'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('category'); ?>
        <div class="separation" ></div>
        <div class="panel">
            <div class="panel-content" >
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_NAME_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CATEGORY_NAME_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('name', 'jform', 'text', true, $this->context->controller->category->name); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_PUBLISHED_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CATEGORY_PUBLISHED_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->radioButton('published', 'edit', $this->context->controller->category->published); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_PARENT_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CATEGORY_PARENT_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->categories_tree; ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_DESCRIPTION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CATEGORY_DESCRIPTION_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('description', 'jform', 'textarea', $this->context->controller->category->description); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_IMAGE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CATEGORY_IMAGE_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->imageFileChooser(); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_META_TITLE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_TITLE_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('meta_title', 'jform', 'text', true, $this->context->controller->category->meta_title); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_META_DESCRIPTION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_DESCRIPTION_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('meta_description', 'jform', 'text',  true, $this->context->controller->category->meta_description); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_META_KEYWORDS_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_KEYWORDS_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('meta_keywords', 'jform', true, $this->context->controller->category->meta_keywords); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_LINK_REWRITE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_LINK_REWRITE_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('link_rewrite', 'jform', 'text', true, $this->context->controller->category->link_rewrite); ?></div>
                </div>
                <?php if(JeproshopSettingModelSetting::getValue('multishop_feature_active')){ ?>
                    <div class="control-group" >
                        <div class="control-label" ><label title="<?php echo JText::_('COM_JEPROSHOP_IS_ROOT_CATEGORY_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_IS_ROOT_CATEGORY_LABEL'); ?></label> </div>
                        <div class="controls" ><?php echo $this->helper->radioButton('is_root_category', 'edit', $this->context->controller->category->is_root_category); ?></div>
                    </div>
                <?php } ?>
                <?php if(JeproshopShopModelShop::isFeaturePublished()){ ?>
                    <div class="control-group" >
                        <div class="control-label" ><label for="jform_associated_shop" title="<?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_ASSOCIATED_SHOP_LABEL'); ?></div>
                        <div class="controls" ><?php echo $this->associated_shop; ?></div>
                    </div>
                <?php } ?>
                <div class="control-group">
                    <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_CATEGORY_ALLOWED_GROUP_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_CATEGORY_ALLOWED_GROUP_LABEL'); ?></label></div>
                    <div class="controls" >
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th class="nowrap center" width="1%"><?php echo JHtml::_('grid.checkall'); ?></th>
                                <th class="nowrap center" width="1%"><?php echo JText::_('COM_JEPROSHOP_ID_LABEL'); ?></th>
                                <th class="nowrap " ><?php echo JText::_('COM_JEPROSHOP_GROUP_NAME_LABEL'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(empty($this->groups)){ ?>
                                <tr><td colspan="3" ><div class="alert alert-no-items" ><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div></td><tr>
                            <?php } else {
                                foreach($this->groups as $index => $group){
                                    $group_link = JRoute::_('index.php?option=com_jeproshop&view=group&task=edit&group_id=' . $group->group_id .'&' . JeproshopTools::getGroupToken() .'=1');
                                    ?>
                                    <tr class="row_<?php echo $index % 2; ?>" >
                                        <td class="nowrap center" width="1%" ><?php echo JHtml::_('grid.id', $index, $group->group_id); ?></td>
                                        <td class="nowrap center" width="1%" ><?php echo $group->group_id; ?></td>
                                        <td class="nowrap " width="40%" ><?php echo $group->name; ?></td>
                                    </tr>
                                <?php }
                            } ?>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <div class="alert alert-info">
                            <h4><?php $allowed_groups = isset($this->allowed_groups) ? count($this->allowed_groups) : 0;
                                echo JText::_('COM_JEPROSHOP_YOU_NOW_HAVE_MESSAGE') . ' ' . ((int)($allowed_groups) + 3) . ' ' . JText::_('COM_JEPROSHOP_ALLOWED_GROUP_MESSAGE'); ?></h4><br />
                            <p>
                                <?php echo $this->unidentified_group_information .'<br />'. $this->guest_group_information .'<br />'. $this->default_group_information .'<br />';
                                if(isset($this->allowed_groups)) {
                                    foreach ($this->allowed_groups as $ag) { ?>
                                        <b><?php echo $ag->name; ?></b> - <?php echo $ag->description; ?><br/>
                                    <?php }
                                }?>
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <input type="hidden" name="product_id" value="<?php echo $this->category->category_id; ?>" />
        <input type="hidden" name="return" value="<?php echo $app->input->get('return'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
