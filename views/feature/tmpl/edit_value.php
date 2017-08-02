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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=feature'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('feature'); ?>
        <div class="separation" ></div>
        <div class="panel" >
            <div class="panel-content well" >
                <div class="control-group" >
                    <div class="control-label" ><label for="jform_feature"  title="<?php echo JText::_('COM_JEPROSHOP_FEATURE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_FEATURE_LABEL'); ?></label></div>
                    <div class="controls" >
                        <select id="jform_feature_id" name="jform[feature_id]" required="required" >
                            <?php foreach($this->features as $feature){ ?>
                                <option value="<?php echo $feature->feature_id; ?>" <?php if($this->feature_value->feature_id == $feature->feature_id){ ?> selected="selected" <?php } ?> ><?php echo ucfirst($feature->name); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="control-group" > 
                    <div class="control-label" ><label for="jform_feature_value"  title="<?php echo JText::_('COM_JEPROSHOP_FEATURE_VALUE_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_FEATURE_VALUE_LABEL'); ?></label></div>
                    <div class="controls" ><?php echo $this->helper->multiLanguageInputField('feature_value', 'jform', 'text', true, $this->feature_value->value); ?></div>
                </div>
            </div>
        </div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="feature_value_id" value="<?php echo $this->feature_value->feature_value_id; ?>" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>