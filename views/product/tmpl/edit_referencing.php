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
<div class="form-box-wrapper"  id="product-referencing" >
    <div id="step-referencing" >
        <div class="panel" >
            <div class="panel-title"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_EDIT_REFERENCING_TITLE'); ?></div>
            <div class="panel-content">
                <div class="control-group" >
                    <div class="control-label">
                        <?php echo $this->productMultiShopCheckbox('meta_title', 'default', true); ?>
                        <label title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_META_TITLE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_TITLE_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <?php echo $this->helper->multiLanguageInputField('meta_title', 'referencing', 'text', FALSE, $this->product->meta_title); ?>
                        <p class="preference-description small"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_META_TITLE_DESCRIPTION'); ?></p>
                    </div>
                </div>
                <div class="control-group" >
                    <div class="control-label">
                        <?php echo $this->productMultiShopCheckbox('meta_description', 'default', true); ?>
                        <label title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_META_DESCRIPTION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_DESCRIPTION_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <?php echo $this->helper->multiLanguageInputField('meta_description', 'referencing', 'text', FALSE, $this->product->meta_description); ?>
                        <p class="preference-description small"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_META_DESCRIPTION_DESCRIPTION'); ?></p>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->productMultiShopCheckbox('meta_keywords', 'default', true); ?>
                        <label title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_META_KEYWORDS_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_META_KEYWORDS_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <?php echo $this->helper->multiLanguageInputField('meta_keywords', 'referencing', 'text', FALSE, $this->product->meta_keywords); ?>
                        <p class="preference-description small"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_META_KEYWORDS_DESCRIPTION'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label" >
                        <?php echo $this->ProductMultiShopCheckbox('link_rewritable', 'default', true); ?>
                        <label title="<?php echo JText::_('COM_JEPROSHOP_PRODUCT_FRIENDLY_URL_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_FRIENDLY_URL_LABEL'); ?></label>
                    </div>
                    <div class="controls">
                        <?php echo $this->helper->multiLanguageInputField('link_rewrite', 'referencing', 'text', FALSE, $this->product->link_rewrite); ?>
                        <p class="clear preference-description small" style="padding: 10px 0 0 0">
                            <a style="cursor:pointer" class="button"  id="jform_generate_friendly_link" ><?php echo JText::_('COM_JEPROSHOP_GENERATE_LABEL'); ?></a>&nbsp;
                            <?php echo JText::_('COM_JEPROSHOP_FRIENDLY_URL_FROM_THE_PRODUCT_NAME_LABEL') . '<br /><br />' . JText::_('COM_JEPROSHOP_PRODUCT_LINK_WILL_LOOK_LIKE_THIS_LABEL'); ?>
                            <?php echo htmlentities($this->current_shop_url); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
