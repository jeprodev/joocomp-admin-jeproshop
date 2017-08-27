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
<div class="form-box-wrapper panel"  id="product-images" >
    <div class="panel-title" ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_EDIT_IMAGES_TITLE'); ?> <span class="badge" id="jform_count_image" ><?php echo count($this->images); ?></span></div>
    <div class="panel-content">
        <?php echo $this->image_uploader; ?>
        <div class="control-group" >
            <div class="control-label"><label title="<?php echo JText::_('COM_JEPROSHOP_INVALID_CHARACTERS_LABEL'); ?>"><?php echo JText::_('COM_JEPROSHOP_IMAGE_LEGEND_LABEL'); ?></label></div>
            <div class="controls">
                <?php echo $this->helper->multiLanguageInputField('legend', 'images', 'text', true, $this->product->name); ?>
            </div>
        </div>
        <div class="control-group" >
            <div class="controls">
                <table class="table tableDnD" id="jform_product_image_table" >
                    <thead>
                    <tr>
                        <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_IMAGE_LABEL'); ?></th>
                        <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_CAPTION_LABEL'); ?></th>
                        <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_POSITION_LABEL'); ?></th>
                        <?php if($this->shops){
                            foreach($this->shops as $shop){ ?>
                                <th class="nowrap" ><?php echo $shop->shop_name; ?></th>
                            <?php }
                        } ?>
                        <th class="nowrap center" ><?php echo JText::_('COM_JEPROSHOP_COVER_LABEL'); ?></th><th></th>
                    </tr>
                    </thead>
                    <tbody id="jform_images_list"></tbody>
                </table>
                
                <div class="separation" ></div>
                <div class="panel-footer" >
                    <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=product'); ?>" class="btn btn-default" ><i class="icon-cancel" ></i> <?php echo JText::_('COM_JEPROSHOP_CANCEL_LABEL'); ?></a>
                    <button type="submit" name="save_image" class="btn btn-default pull-right" ><i class="process-icon-save" ></i> <?php echo JText::_('COM_JEPROSHOP_SAVE_LABEL'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

