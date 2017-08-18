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
$nodeFolderTemplate = $this->getNodeFolderTemplate();
$itemNodeTemplate = $this->getItemNodeTemplate();
?>
<?php foreach ($data as $item) {
    if (array_key_exists('children', $item) && !empty($item->children)) {
        if ($nodeFolderTemplate == 'tree_node_folder') {
            ?>
            <li class="tree-folder" >
                <span class="tree-folder-name"  ><i class="icon-folder-close" ></i> <label class="tree-toggler" ><?php echo $item->name; ?> bgsgfth</label></span>
                <ul class="tree" ><?php echo $this->renderNodes($item->children); ?></ul>
            </li>
        <?php } else if($nodeFolderTemplate == 'tree_node_folder_checkbox'){ ?>
            <li class="tree-folder" >
                <p class="checkbox tree-folder-name <?php if(isset($item->disabled) && $item->disabled == true){ ?> tree-folder-name-disabled <?php } ?>" >
                    <?php if($item->category_id != $this->getRootCategoryId()){ ?>
                        <input type="checkbox" name="<?php echo $this->getInputName(); ?>" value="<?php echo $item->category_id; ?>"
                            <?php if(isset($item->disablee) && $item->disabled == true){ ?> disabled="disabled" <?php } ?> />
                        <i class="icon-folder-close pull-left" ></i>
                        <label class="tree-toggler" >
                            <?php echo $item->name;
                            if(isset($item->selected_children) && count($item->selected_children) > 0){}
                            ?>
                        </label>
                    <?php } ?>
                </p>
                <ul class="tree" ><?php echo $this->renderNodes($item->children); ?></ul>
            </li>
        <?php }else if($nodeFolderTemplate == 'tree_node_folder_radio'){ ?>
            <li class="tree-folder" >
                <span class="tree-folder-name <?php if(isset($item->disabled) && $item->disabled == true){ ?> tree-folder-name-disabled <?php } ?>" >
                                <?php if($item->category_id != $this->getRootCategoryId()){ ?>
                                    <input type="radio" name="<?php echo $this->getInputName(); ?>" value="<?php echo $item->category_id; ?>"
                                        <?php if(isset($item->disabled) && $item->disabled == true){ ?> disabled="disabled" <?php }  ?> />
                                <?php } ?>
                                <i class="icon-folder-close" ></i>
                                <label class="tree-toggler" ><?php echo $item->name; ?></label>
                            </span>
                <ul class="tree" ><?php echo $this->renderNodes($item->children); ?></ul>
            </li>
        <?php }
    }else {
        if($itemNodeTemplate == 'tree_node_item'){ ?>
            <li class="tree-item" ><label class="tree-item-name" ><i class="tree-dot" ></i> <?php echo $item->name; ?> </label> </li>
        <?php }else if($itemNodeTemplate == 'tree_node_item_checkbox_shops'){ ?>
            <li class="tree-item <?php if(isset($item->disabled) && $item->disabled == true){ ?> tree-item-disabled <?php } ?>" >
                <label class="tree-item-name" >
                    <input type="checkbox" name="<?php echo (isset($wrapper) ? $wrapper : 'jform') . '[check_box_shop_associated_' . $table . '_' . $item->shop_id . ']'; ?>"
                        <?php if(isset($item->disabled) && $item->disabled == true){ ?> disabled="disabled" <?php } ?> value="<?php echo $item->shop_id; ?>" />
                    <i class="tree-dot" ></i> <?php echo $item->name; ?>
                </label>
            </li>
        <?php }else if($itemNodeTemplate == 'tree_note_item_radio'){ ?>
            <li class="tree-item <?php if(isset($item->disabled) && $item->disabled){ ?> tree-item-disabled <?php } ?>" >
                <label class="tree-item-name" ><input type="radio" name="<?php echo $this->getInputName(); ?>" value="<?php echo $item->category_id; ?>"
                        <?php if(isset($item->disabled) && $item->disabled == true){ ?>  disabled="disabled" <?php } ?> >
                    <i class="tree-dot"></i><?php echo $item->name; ?></label>
            </li>
        <?php }else if($itemNodeTemplate == 'tree_node_item_checkbox'){ ?>
            <li class="tree-item <?php if(isset($item->disabled) && $item->disabled == true){ ?> tree-item-disable <?php  } ?>" >
                <p class="checkbox" >
                    <input type="checkbox" name="<?php echo $this->getInputName(); ?>" value="<?php echo $item->category_id; ?>"
                            <?php if(isset($item->disabled) && $item->disabled == true){ ?> disabled="disabled" <?php } ?> />
                    <label class="tree-toggler tree-item-name <?php if(isset($item->disabled) && $item->disabled == true){ ?> tree-item-name-disable <?php } ?>" ><?php echo $item->name; ?> </label>
                </p>
            </li>
        <?php }
        ?>
    <?php }
}?>
