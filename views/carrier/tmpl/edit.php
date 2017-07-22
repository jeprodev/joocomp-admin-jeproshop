<?php
/**
 * Created by PhpStorm.
 * User: jeproQxT
 * Date: 20/07/2017
 * Time: 21:44
 */

?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=carrier'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal" >
    <?php if(!empty($this->side_bar)){ ?>
        <div id="j-sidebar-container" class="span2" ><?php echo $this->side_bar; ?></div>
    <?php } ?>
    <div id="j-main-container" <?php if(!empty($this->side_bar)){ echo 'class="span10"'; }?> >
        <?php echo $this->renderCustomerSubMenu('customer'); ?>
        <div class="separation"></div>
        <div class="panel" >
            <div class="panel-content" >
                <div id="logo-wrapper" class="pull-left">
                    <div id="carrier_logo_block" class="panel">
                        <div class="panel-title">
                            <?php echo JText::_('COM_JEPROSHOP_LOGO_LABEL'); ?>
                            <div class="panel-title-action">
                                <a id="carrier_logo_remove" class="btn btn-default" <?php if(!$this->carrier_logo){ ?>style="display:none"<?php } ?> href="jeproCarrier.removeCarrierLogo();" >
                                    <i class="icon-trash"></i>
                                </a>
                            </div>
                        </div>
                        <img id="carrier_logo_img" src="<?php if($this->carrier_logo){ echo $this->carrier_logo; }else{ echo '../img/admin/carrier-default.jpg';  } ?>" class="img-thumbnail" alt=""/>
                    </div>
                </div>
                <div id="info-wrapper" class="pull-left">
                    <?php
                    echo JHtml::_('bootstrap.startTabSet', 'carrier_form', array('active' =>'information'));
                    echo JHtml::_('bootstrap.addTab', 'carrier_form', 'information', JText::_('COM_JEPROSHOP_INFORMATION_LABEL')) . $this->loadTemplate('information') . JHtml::_('bootstrap.endTab');
                    if(JeproshopShopModelShop::isFeaturePublished()){
                        echo JHtml::_('bootstrap.addTab', 'carrier_form', 'multi_store', JText::_('COM_JEPROSHOP_STORES_LABEL')) . $this->loadTemplate('stores') . JHtml::_('bootstrap.endTab');
                    }
                    echo JHtml::_('bootstrap.addTab', 'carrier_form', 'cost', JText::_('COM_JEPROSHOP_COST_LABEL')) . $this->loadTemplate('cost') . JHtml::_('bootstrap.endTab');
                    echo JHtml::_('bootstrap.addTab', 'carrier_form', 'size', JText::_('COM_JEPROSHOP_SIZE_LABEL')) . $this->loadTemplate('size') . JHtml::_('bootstrap.endTab');
                    echo JHtml::_('bootstrap.addTab', 'carrier_form', 'resume', JText::_('COM_JEPROSHOP_RESUME_LABEL')) . $this->loadTemplate('resume') . JHtml::_('bootstrap.endTab');
                    echo JHtml::_('bootstrap.endTabSet');
                    ?>
                </div>
            </div>
        </div>
    </div>

</form>
