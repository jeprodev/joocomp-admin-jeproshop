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
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=manufacturer'); ?>" method="post" name="adminForm" id="adminForm" >
    <?php if(!empty($this->side_bar)){ ?>
                <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
            <?php } ?>
            <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
                <?php echo  $this->setCatalogSubMenu('manufacturer'); ?>
                <div class="separation" ></div>
                <div class="panel" >
                    <div class="panel-content well" >
            <table class="table table-striped" id="supplier-list">
                <thead>
                <tr>
                    <th width="1%" class="nowrap center" >#</th>
                    <th width="1%" class="nowrap center" ><?php echo JHtml::_('grid.checkall'); ?></th>
                    <th class="nowrap center" width="5%" ><?php echo JText::_('COM_JEPROSHOP_LOGO_LABEL'); ?></th>
                    <th class="nowrap" width="65%" ><?php echo JText::_('COM_JEPROSHOP_MANUFACTURER_NAME_LABEL'); ?></th>
                    <th class="nowrap center" width="5%" ><?php echo JText::_('COM_JEPROSHOP_ADDRESSES_LABEL'); ?></th>
                    <th class="nowrap center" width="14%" ><?php echo JText::_('COM_JEPROSHOP_MANUFACTURER_VALUES_COUNT_LABEL'); ?></th>
                    <th class="nowrap center" width="10%" ><?php echo JText::_('COM_JEPROSHOP_STATUS_LABEL'); ?></th>
                    <th class="nowrap " width="8%" ><span class="pull-right" ><?php echo JText::_('COM_JEPROSHOP_ACTIONS_LABEL'); ?></span></th>
                </tr>
                </thead>
                <tbody>
                <?php if(isset($this->manufacturers) && count($this->manufacturers)){
                    foreach($this->manufacturers as $index => $manufacturer){
                        $manufacturer_link = JRoute::_('index.php?option=com_jeproshop&view=manufacturer&task=edit&manufacturer_id=' . (int)$manufacturer->manufacturer_id);
                        $delete_manufacturer_link = JRoute::_('index.php?option=com_jeproshop&view=manufacturer&task=delete&manufacturer_id=' . (int)$manufacturer->manufacturer_id); ?>
                        <tr class="row_<?php echo $index % 2; ?>" >
                            <td class="nowrap center" ><?php echo $index + 1; ?></td>
                            <td class="nowrap center" ><?php echo JHtml::_('grid.id', $index, $manufacturer->manufacturer_id); ?></td>
                            <td class="nowrap" ></td>
                            <td class="nowrap" ><?php echo ucfirst($manufacturer->name); ?></td>
                            <td class="nowrap center" ><?php echo count($manufacturer->addresses); ?></td>
                            <td class="nowrap center" ><?php echo $manufacturer->products; ?></td>
                            <td class="nowrap center" ><i class="icon-<?php echo $manufacturer->published ? '' : 'un'; ?>publish" ></i></td>
                            <td class="nowrap" >
                                <div class="btn-group-action" >
                                    <div class="btn-group pull-right" >
                                        <a href="<?php echo $manufacturer_link; ?>" class="btn btn-micro" ><i class="icon-edit" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_EDIT_LABEL'); ?></a>
                                        <button class="btn btn-micro dropdown_toggle" data-toggle="dropdown" ><i class="caret"></i> </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="#" title="<?php echo JText::_('COM_JEPROSHOP_VIEW_LABEL'); ?>" onclick="if(confirm('<?php echo JText::_('COM_JEPROSHOP_COPY_IMAGES_TOO_LABEL'); ?>')) document.location ='<?php echo $copyImageTooLink; ?>'; else document.location = '<?php echo $duplicate_product_link; ?>'; return false; ">
                                                    <i class="icon-copy" ></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_VIEW_LABEL'); ?>
                                                </a>
                                            </li><li class="divider" ></li>
                                            <li><a href="<?php echo $delete_manufacturer_link; ?>" onclick="if(confirm('<?php echo JText::_('COM_JEPROSHOP_PRODUCT_DELETE_LABEL') . $manufacturer->name; ?>')){ return true; }else{ event.stopPropagation(); event.preventDefault(); };" title="<?php echo ucfirst(JText::_('COM_JEPROSHOP_DELETE_LABEL')); ?>" class="delete"><i class="icon-trash" ></i>&nbsp;<?php echo ucfirst(JText::_('COM_JEPROSHOP_DELETE_LABEL')); ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php }
                }else{ ?>
                    <tr>
                        <td colspan="8" ><div class="alert warning" ><?php echo JText::_('COM_JEPROSHOP_NOT_MATCHING_MESSAGE'); ?></div></td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot><tr><td colspan="8" ><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
            </table>
                        </div>
        </div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
        
    