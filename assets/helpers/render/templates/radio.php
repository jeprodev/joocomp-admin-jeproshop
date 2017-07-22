<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
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

if($layout == 'add'){
    $state_published = ' checked="checked"';
    $state_unpublished = '';
}else{
    if($state){
        $state_published = ' checked="checked" ';
        $state_unpublished = '';
    }else{
        $state_published = '';
        $state_unpublished = ' checked="checked"';
    }
}
?>
<fieldset class="btn-group radio" >
    <input type="radio" id="jform_<?php echo $fieldName; ?>_1" name="jform[<?php echo $fieldName; ?>]" value="1" <?php echo $state_published; ?> />
    <label for="jform_<?php echo $fieldName; ?>_1" ><?php echo JText::_('COM_JEPROSHOP_YES_LABEL'); ?></label>
    <input type="radio" id="jform_<?php echo $fieldName; ?>_0" name="jform[<?php echo $fieldName; ?>]" value="0" <?php echo $state_unpublished; ?> />
    <label for="jform_<?php echo $fieldName; ?>_0" ><?php echo JText::_('COM_JEPROSHOP_NO_LABEL'); ?></label>
</fieldset>