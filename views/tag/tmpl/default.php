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

?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=tag'); ?>" method="post" name="adminForm" id="adminForm" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo  $this->setCatalogSubMenu('tag'); ?>
        <div class="separation" ></div>
        <div class="panel" >
            <div class="panel-content well" >
                <table class="table table-striped" id="tag-list">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center" >#</th>
                        <th width="1%" class="nowrap center" ><?php echo JHtml::_('grid.checkall'); ?></th>
                        <th class="nowrap" width="5%" ><?php echo JText::_('COM_JEPROSHOP_LANG_NAME_LABEL'); ?></th>
                        <th class="nowrap" width="60%" ><?php echo JText::_('COM_JEPROSHOP_TAG_NAME_LABEL'); ?></th>
                        <th class="nowrap center" width="8%" ><?php echo JText::_('COM_JEPROSHOP_MANUFACTURER_VALUES_COUNT_LABEL'); ?></th>
                        <th class="nowrap" width="8%" ><span class="pull-right" ><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></span></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(isset($this->tags) && count($this->tags)){
                        foreach($this->tags as $index => $tag){
                            $tag_link = JRoute::_('index.php?option=com_jeproshop&view=tag&task=edit&tag_id=' . $tag->tag_id);
                            $delete_tag_link = JRoute::_('index.php?option=com_jeproshop&view=tag&task=delete&tag_id=' . $tag->tag_id);
                            ?>
                            <tr>
                                <td class="nowrap" ><?php echo $index + 1; ?> </td>
                                <td class="nowrap" ><?php echo JHtml::_('grid.id', $index, $tag->tag_id); ?></td>
                                <td class="nowrap" ><?php echo $tag->lang_name; ?></td>
                                <td class="nowrap" ><?php echo $tag->name; ?></td>
                                <td class="nowrap center" ><?php echo $tag->products; ?></td>
                                <td class="nowrap" >
                        <span class="pull-right" >
                            <div class="btn-group-action" >
                                <div class="btn-group pull-right" >
                                    <a href="<?php echo $tag_link; ?>" class="btn btn-micro" ><i class="icon-edit" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_EDIT_LABEL'); ?></a>
                                    <button class="btn btn-micro dropdown_toggle" data-toggle="dropdown" ><i class="caret"></i> </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="<?php echo $delete_tag_link; ?>"
                                               onclick="if(confirm('<?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL') . $tag->name; ?>')){ return true; }else{ event.stopPropagation(); event.preventDefault(); };"
                                               title="<?php echo (JText::_('COM_JEPROSHOP_DELETE_LABEL')); ?>" class="delete"><i class="icon-trash" ></i>&nbsp;<?php echo ucfirst(JText::_('COM_JEPROSHOP_DELETE_LABEL')); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </span>
                                </td>
                            </tr>
                        <?php }
                    }else{ ?>
                        <tr>
                            <td colspan="6" ><div class="alert warning" ><?php echo JText::_('COM_JEPROSHOP_NOT_MATCHING_MESSAGE'); ?></div></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                    <tfoot><tr><td colspan="7" ><?php if(isset($this->pagination)){ echo $this->pagination->getListFooter(); } ?></td></tr></tfoot>
                </table>
            </div>
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>
