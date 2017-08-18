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

$document = JFactory::getDocument();
$document->addScript('components/com_jeproshop/assets/helpers/render/templates/js/jeprotree.js');

$script = "jQuery(document).ready(function(){" .
    "jQuery('#jform_" . $this->getTreeId() . "').JeproTree({" . 
    "wrapper : 'jform_" . $this->getTreeId() . "', " .
    "default_label : '" . (new JeproshopCategoryModelCategory($this->getRootCategoryId(), JeproshopContext::getContext()->language->lang_id))->name . "', " .
    "layout : 'associated_categories' " .
    "}); " .
    "});";
$document->addScriptDeclaration($script);
?>
<div class="panel" style="width:90% !important;" >
    <?php if(trim($this->getTreeTitle()) != '' || $this->useToolBar()){ ?>
        <div class="panel-sub-title tree-panel-heading-controls clearfix" >
            <?php
            if(trim($this->getTreeTitle() != '')){ echo $this->getTreeTitle(); }
            if($this->useToolBar()){ echo $this->renderToolBar(); }
            ?>
        </div>
    <?php } ?>
    <div class="panel-content" >
        <?php if(isset($data) && count($data) > 0 ) {
             ?>
            <ul id="jform_<?php echo $this->getTreeId(); ?>" class="tree tree-wrapper" >
            <?php echo $this->renderNodes($data); ?>
            </ul>
        <?php }?>
    </div>
</div>