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

if (isset($files) && count($files) > 0){
    $showThumbnails = false;
    foreach($files as $file){
        if(isset($file->image) && $file->type == 'image'){
            $showThumbnails = true;
        }
    }
    if($showThumbnails){ ?>
<div class="control-group" >
    <div class="controls" id="jform_<?php echo $this->getId(); ?>_images_thumbnails" >
        <?php
        foreach($files as $file){
            if(isset($file->image) && $file->type == 'image'){ ?>
                <div >
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
        <?php  }
        }
        ?>
    </div>
</div>
<?php    }
}
if(isset($maxFiles) && count($files) >= $maxFiles){ ?>
    <div class="control-group" >
        <div class="controls alert alert-warning" >
    <?php echo JText::_('COM_JEPROSHOP_YOU_HAVE_REACHED_THE_LIMIT_OF_LABEL') . ' ' . $maxFiles . ' ' . JText::_('COM_JEPROSHOP_FILES_TO_BE_UPLOADED_PLEASE_REMOVE_FILES_TO_CONTINUE_UPLOADING_LABEL'); ?>
        </div>
    </div>
<?php }else{ $multiple = $this->isMultiple(); ?>
    <div class="control-group hidden" >
        <div class="controls" >
            <input type="file" id="jform_<?php echo $this->getId(); ?>"  name="<?php echo  $this->getId() . '[]'; ?>"
                <?php if(isset($multiple) && $multiple){ ?> multiple="multiple" <?php } ?> class="hidden"
            />
        </div>
    </div>
    <div class="control-group" >
        <div class="control-label">
            <label for="jform_<?php echo $this->getId(); ?>_name" title="<?php echo JText::_('COM_JEPROSHOP_FORMAT_LABEL') . ': JPG, GIF, PNG. ' . JText::_('COM_JEPROSHOP_FILE_SIZE_LABEL') . ': ' . $this->max_image_size . ' ' . JText::_('COM_JEPROSHOP_MB_MAX_LABEL');  ?>" >
                <?php if(isset($this->image_id)){ echo JText::_('COM_JEPROSHOP_THIS_PRODUCT_IMAGE_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_ADD_NEW_IMAGE_TO_THIS_PRODUCT_LABEL'); } ?>
            </label>
        </div>
        <div class="controls">
            <div class="input-append input-prepend" >
                <button class="btn btn-default" ><i class="icon-file" ></i> </button>
                <input id="jform_<?php echo $this->getId(); ?>_name" type="text" name="filename" readonly />
                <a class="btn btn-default" id="jform_<?php echo $this->getId(); ?>_select_button"  >
                    <i class="icon-folder-open" ></i>
                    <?php if(isset($multiple) && $multiple){ echo JText::_('COM_JEPROSHOP_ADD_FILES_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_ADD_FILE_LABEL'); } ?>
                </a>
                <?php if((!isset($multiple) && $multiple) && isset($file) && count($files) == 1 && isset($files[0]->download_url)){ ?>
                    <a href="<?php echo $files[0]->download_url; ?>" class="btn btn-default" ><i class="icon-cloud-download" ></i>
                        <?php if(isset($size)){ echo JTxt::_('COM_JEPROSHOP_DOWNLOAD_CURRENT_FILE_LABEL') . ' ' . $size . 'kb '; }else{ echo JText::_('COM_JEPROSHOP_DOWNLOAD_CURRENT_FILE_LABEL'); } ?>
                    </a>
                <?php } ?>
            </div>
            <!--div-- >
                <a class="ladda-button btn btn-default" data-style="expand-right" id="#jform_<?php echo $this->getId(); ?>_upload_button" >
                    <span class="ladda-label" ><i class="icon-close" ></i> <?php if(isset($multiple) && $multiple){ echo JText::_('COM_JEPROSHOP_UPLOAD_FILES_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_UPLOAD_FILE_LABEL'); }?></span>
                </a>
            </div-->
        </div>
    </div>
<?php }