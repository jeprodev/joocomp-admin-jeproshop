/**
 * Created by jeproQxT on 16/08/2017.
 */
(function($){
    $.fn.JeproTree = function(opts){
        var defaults = {
            wrapper : undefined,
            default_label : 'Home',
            layout : 'associate_categories'
        };

        var options = jQuery.extend(defaults, opts);
        
        var treeObject = this;
        return treeObject.each(function () {
            initializeTree();
        });

        function initializeTree() {
            initialize();
            if(options.layout === 'associated_categories'){
                initializeAssociatedCategoryTree();
            }
        }

        function initialize() {
            var treeWrapper = jQuery('#' + options.wrapper);
            treeWrapper.parent().css('margin-left', 0);
            treeWrapper.parent().css('padding', 0);
            var treeName = treeWrapper.parent().find('ul.tree input').first().attr('name');
            treeWrapper.find('label.tree-toggler, .icon-folder-close, .icon-folder-open').unbind('click');
            treeWrapper.find('label.tree-toggler, .icon-folder-close, .icon-folder-open').each(function(index, elt){
                elt = jQuery(elt);
                elt.on('click', function(){
                    if(elt.parent().children('ul.tree').is(':visible')){console.log(jQuery(this));
                        elt.parent().children('.icon-folder-open').removeClass('icon-folder-open').addClass('icon-folder-close');
                        treeObject.trigger('collapse');
                        elt.parent().parent().children('ul.tree').toggle(300);
                    }else{
                        elt.parent().children('.icon-folder-close').removeClass('icon-folder-close').addClass('icon-folder-open');
                        var loadTree = (typeof(options.wrapper) != 'undefined') && (elt.parent().closest('.tree-folder').find('ul.tree .tree-toggler').first().html() === '');
                        if(loadTree){
                            var categoryId = elt.parent().children('ul.tree input').first().val();
                            var inputType = elt.parent().children('ul.tree input').first().attr('type');
                            var useCheckBox = 0;
                            if(inputType == 'checkbox'){ useCheckBox = 1; }
                            var categoryUrl = 'index.php?option=com_jeproshop&view=category&task=tree&use_ajax=1';
                            jQuery.ajax({
                                type : "GET",
                                url : categoryUrl,
                                async : true,
                                dataType : "json",
                                data : {
                                    category_id : categoryId,
                                    name : treeName,
                                    tree_id : options.wrapper,
                                    use_check_box : useCheckBox,
                                    token: options.token
                                },
                                success : function(result){},
                                fail : function (result) {

                                }
                            });
                        }else{
                            treeObject.trigger('expand');
                            elt.parent().parent().children('ul.tree').toggle(300);
                        }
                    }
                });
            });

            treeWrapper.find('li').unbind('click');
            treeWrapper.find('li').each(function (index, elt) {
                elt = jQuery(elt);
                elt.on('click', function() {
                    jQuery('.tree-selected').removeClass('tree-selected');
                    jQuery('li input:checked').parent().addClass('tree-selector');
                });
            });

            if(typeof(options.wrapper) !== 'undefined'){
                var defaultCategory = jQuery('#jform_default_category_id');
                if(typeof(defaultCategory) != 'undefined'){
                    treeWrapper.find(':input[type=checkbox]').unbind('click');
                    treeWrapper.find(':input[type=checkbox]').each(function (index, elt){
                        elt = jQuery(elt);
                        if(elt.prop('checked')){
                            addDefaultCategory(elt);
                        }else{
                            defaultCategory.find('option[value=' + elt.val() + ']').remove();
                            if(defaultCategory.find('option').length == 0){
                                defaultCategory.closest('control-group').hide();
                                jQuery('#jform_no_default_category').show();
                            }
                        }
                    });
                }
            }
        }
        
        function initializeAssociatedCategoryTree() {
            var treeWrapper = jQuery('#' + options.wrapper);
            treeWrapper.find(':input[type=radio]').each(function (index, elt){
                elt = jQuery(elt);
                elt.on('click', function(){
                    location.href = location.href.replace(/&category_id=[0-9]*/, '') + '&category_id=' + jQuery(this).val();
                })
            });

            treeWrapper.find(':input[type=checkbox]').each(function (index, elt){
                elt = jQuery(elt);
                elt.on('click', function(){
                    var defaultCategory = jQuery('#jform_default_category_id');
                    if(elt.prop('checked')){
                        var dataToAppend = ((elt.val() != 1) ? elt.parent().find('label').html() : options.default_label);
                        defaultCategory.append((jQuery('<option>', {value : elt.val()})).text(dataToAppend));
                        //defaultCategory.html(defaultCategory.html() + '<option value="' + elt.val() + '" >' + dataToAppend + '</option>');
                        //defaultCategory.selectmenu('refresh', true);
                        /*var selectOptions = document.getElementById('jform_default_category_id'); //.attr('options');
                        selectOptions[selectOptions.length] = new Option(elt.val(), dataToAppend, true, true);
                        selectOptions.appendChild(new Option(elt.val(), dataToAppend)); console.log(dataToAppend); */

                        if(defaultCategory.find('option').length > 0){
                            defaultCategory.closest('control-group').show();
                            jQuery('#jform_no_default_category').hide();
                        }
                    }else{
                        defaultCategory.find('option[value="' + parseInt(elt.val()) + '"]').remove();
                        if(defaultCategory.find('option').length == 0){
                            defaultCategory.closest('control-group').hide();
                            jQuery('#jform_no_default_category').show();
                        }
                    }

                })
            });
        }

        function collapseTree(elt, speed){
            elt.find('label.tree-toggler').each(function(index, item){
                item = jQuery(item);
                item.parent().children('.icon-folder-open').removeClass('icon-folder-open').addClass('icon-folder-close');
                item.parent().parent().children('ul.tree').hide(speed);
            });
        }

        function collapseAllTree(speed){
            var treeWrapper = jQuery('#' + options.wrapper);
            treeWrapper.find("label.tree-toggler").each(function() {
                    $(this).parent().children(".icon-folder-open").removeClass("icon-folder-open").addClass("icon-folder-close");
                    $(this).parent().parent().children("ul.tree").hide(speed);
                }
            );
        }
    }
})(jQuery);