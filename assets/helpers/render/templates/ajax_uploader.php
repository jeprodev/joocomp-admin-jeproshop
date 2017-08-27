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


$files = $this->getFiles();
?>
<div>
    <?php if(isset($files) && count($files) > 0){
        foreach($files as $file){
            if(isset($file->image) && $file->type == 'image'){ ?>
                <div>
                    <?php echo $file->image;
                    if(isset($file->size)){ ?><p><?php echo JText::_('COM_JEPROSHOP_FILE_SIZE_LABEL') . ' ' . $file->size . 'kb';  ?> </p><?php }
                    if(isset($file->delete_url)){ ?>
                        <p>
                            <a class="btn btn-default" href="<?php echo $file->delete_url; ?>" >
                                <i class="icon-trash"></i> <?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL'); ?>
                            </a>
                        </p>
                    <?php } ?>
                </div>
            <?php }
        }
    } ?>
</div>
