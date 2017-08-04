/**
 * Created by jeproQxT on 01/08/2017.
 */
(function ($) {
    $.fn.JeproOrder = function (opts) {
        var defaults = {
            layout :'',
            cart :{
                cart_id : 0,
                quantity : [],
                cart_link : '',
                token : ''
            },
            customer : {
                customer_id: 0,
                token : ''
            },
            currencies : [],
            currency :{
                currency_id : 0,
                format : "",
                sign : "",
                blank : "",
                decimals : ""
            },
            default_order_statues : [],
            price_display_precision :2,
            lang_id : '',
            labels : {
                view_cart : "View Cart",
                view_order : "View Order",
                choose: "Choose",
                details: "Details",
                change : "Change",
                duplicate_order : "Duplicate Order",
                products : 'Products',
                product : 'Product',
                use_this_cart : "Use bThis Cart",
                use_label : "Use",
                customization : 'Customization',
                no_products_found : 'No products found',
                no_carrier_can_be_applied_to_this_order : "no carrier can be applied to this order",
                combination : "Combination"
            }
        };
        
        var options =  jQuery.extend(defaults, opts);
        
        var orderObject = this;
        
        return orderObject.each(function () {
            initializeOrder();
        });
        
        function initializeOrder() {
            if(options.layout === 'add' || options.layout === 'edit' || options.layout === 'view'){
                manageOrderEdition();
            }
        }

        function manageOrderEdition(){
            var customerField = jQuery('#jform_customer');
            var paymentModuleName = jQuery('#jform_payment_module_name');

            customerField.typeWatch({
                captureLength: 1,
                highlight: true,
                wait: 100,
                callback: function(){ searchCustomers(); }
            });

            jQuery('#jform_product').typeWatch({
                captureLength: 1,
                highlight: true,
                wait: 100,
                callback: function(){ searchProducts(); }
            });

            var customerNote = jQuery("#jform_note_content");
            var customerNoteSubmit = jQuery('#jform_submit_customer_note');
            customerNote.on("keyup", function(evt){
                if(customerNote.val().length > 0){
                    customerNoteSubmit.removeAttr('disabled');
                }else {
                    customerNoteSubmit.attr('disabled', 'disabled');
                }
            });

            paymentModuleName.change(function() {
                var orderStatusId = options.default_order_statues[this.value];
                if (typeof(orderStatusId) == 'undefined') {
                    orderStatusId = options.default_order_statues['other'];
                }
                jQuery('#jform_order_status_id').val(orderStatusId);
            });

            jQuery("#jform_delivery_address_id").change(function() { updateAddresses(); });
            jQuery("#jform_invoice_address_id").change(function() { updateAddresses(); });

            jQuery('#jform_currency_id').change(function() { updateCurrency(); });
            jQuery('#jform_lang_id').change(function(){ updateLang(); });
            jQuery('#jform_delivery_option').change(function(){ updateDeliveryOption(); });
            jQuery('#jform_carrier_recycled_package').change(function() { updateDeliveryOption(); });
            jQuery('#jform_order_gift').change(function(){ updateDeliveryOption(); });
            jQuery('#jform_gift_message').change(function() { updateDeliveryOption(); });
            jQuery('#jform_shipping_price').change(function() {
                if (jQuery(this).val() != options.shipping_price_selected_carrier) {
                    options.changed_shipping_price = true;
                }
            });
                
            var voucherWrapper = jQuery('#jform_voucher');
            var voucherErrors = jQuery('#jform_vouchers_error');
            var target = 'index.php?option=com_jeproshop&view=cart&task=search&tab=vouchers&use_ajax=1&' + options.cart.token + '=1';
            voucherWrapper.autocomplete(target,
                {
                    minChars: 3,
                    max: 15,
                    width: 250,
                    selectFirst: false,
                    scroll: false,
                    dataType: "json",
                    formatItem: function(data, i, max, value, term) { return value; },
                    parse: function(data) {
                        if (!data.found) {
                            voucherErrors.html(data.messages).show();
                        }else {
                            voucherErrors.hide();
                        }
                        var myTab = [];
                        if(data.vouchers) {
                            for (var i = 0; i < data.vouchers.length; i++) {
                                myTab[myTab.length] = {
                                    data: data.vouchers[i],
                                    value: data.vouchers[i].name + (data.vouchers[i].code.length > 0 ? ' - ' + data.vouchers[i].code : '')
                                };
                            }
                        }
                        return myTab;
                    }
                }
            ).result(function(event, data, formatted) {
                voucherWrapper.val(data.name);
                addCartRule(data.cart_rule_id);
            });
            /*
            if(options.cart.cart_id){
                setupCustomer(options.customer.customer_id);
                useCart(options.cart.cart_id);
            }


            jQuery('.delete-product').live('click', function(e) {
                e.preventDefault();
                var toDelete = jQuery(this).attr('rel').split('_');
                deleteProduct(toDelete[1], toDelete[2], toDelete[3]);
            });

            jQuery('.delete-discount').live('click', function(e) {
                e.preventDefault();
                deleteVoucher(jQuery(this).attr('rel'));
            });

            jQuery('.use-cart').live('click', function(e) {
                e.preventDefault();
                useCart(jQuery(this).attr('rel'));
                return false;
            });


            jQuery('input:radio[name="free_shipping"]').on('change',function() {
                var freeShipping = jQuery('input[name=free_shipping]:checked').val();
                var target = 'index.php?option=com_jeproshop&view=cart&task=update&tab=free_shipping&use_ajax=1&' + options.cart.token + '=1';
                jQuery.ajax({
                    type: "POST",
                    url: target,
                    async: true,
                    dataType: "json",
                    data :{
                        cart_id : parseInt(options.cart.cart_id),
                        customer_id : parseInt(options.customer.customer_id),
                        free_shipping : freeShipping
                    },
                    success: function (res) {
                        displaySummary(res);
                    }
                });
            });

            jQuery('.duplicate-order').live('click', function(e) {
                e.preventDefault();
                duplicateOrder(jQuery(this).attr('rel'));
            });

            jQuery('.cart_quantity').live('change', function(e) {
                e.preventDefault();
                if (jQuery(this).val() != options.cart.quantity[jQuery(this).attr('rel')]) {
                    var product = jQuery(this).attr('rel').split('_');
                    updateQuantity(product[0], product[1], product[2], jQuery(this).val() - options.cart.quantity[jQuery(this).attr('rel')]);
                }
            });

            jQuery('.increase-product-quantity, .decrease-product-quantity').live('click', function(e) {
                e.preventDefault();
                var product = jQuery(this).attr('rel').split('_');
                var sign = '';
                if (jQuery(this).hasClass('decrease_product_quantity'))
                    sign = '-';
                updateQuantity(product[0], product[1], product[2], sign + 1);
            });
            jQuery('#jform_product_id').live('keydown', function(e) {
                jQuery(this).click();
                return true;
            });
            jQuery('#jform_product_id, .product_attribute_id').live('change', function(e) {
                e.preventDefault();
                displayQuantityInStock(this.id);
            });
            jQuery('#jform_product_id, .product_attribute_id').live('keydown', function(e) {
                jQuery(this).change();
                return true;
            });
            jQuery('.product_unit_price').live('change', function(e) {
                e.preventDefault();
                var product = jQuery(this).attr('rel').split('_');
                updateProductPrice(product[0], product[1], jQuery(this).val());
            });

            jQuery('#jform_order_message').live('change', function(e) {
                e.preventDefault();
                jQuery.ajax({
                    type: "POST",
                    url: "index.php?option=com_jeproshop&view=cart&task=update&tab=order_message&use_ajax=1&" + options.cart.token + "=1",
                    async: true,
                    dataType: "json",
                    data: {
                        cart_id: options.cart.cart_id,
                        customer_id : options.customer.customer_id,
                        message: jQuery(this).val()
                    },
                    success: function (res) {
                        displaySummary(res);
                    }
                });
            });
            resetBind();

            customerField.focus();

            jQuery('#jform_add_product').on('click',function() {
                addProductProcess();
            });

            jQuery('#jform_send_email_to_customer').on('click',function() {
                sendMailToCustomer();
                return false;
            });

            jQuery('#jform_products_found').hide();
            jQuery('#jform_carts').hide();
*/
            var customerPart = jQuery('#jform_customer_part');
            customerPart.on('click','button.setup-customer',function(e) {
                e.preventDefault(); console.log("test " + jQuery(this).data('customer'));
                setupCustomer(jQuery(this).data('customer'));
                jQuery(this).removeClass('setup-customer').addClass('change-customer').html('<i class="icon-refresh"></i> ' +  options.labels.change).blur();
                jQuery(this).closest('.customer-card').addClass('selected-customer');
                jQuery('.selected-customer .panel-title').prepend('<i class="icon-ok text-success"></i>');
                jQuery('.customer-card').not('.selected-customer').remove();
                jQuery('#jform_search_form_customer').hide();
            });

            customerPart.on('click','button.change-customer',function(e) {
                e.preventDefault();
                jQuery('#jform_search_form_customer').show();
                jQuery(this).blur();
            });
        }

        function searchCustomers(){
            var content = jQuery('#jform_customer').val();
            var url = 'index.php?option=com_jeproshop&view=customer&use_ajax=1&task=search&tab=customer&is_from=order';
            var customerWrapper = jQuery('#jform_customers');
            jQuery.ajax({
                type: "POST",
                url: url,
                async: true,
                dataType: "json",
                data : {
                    content : content
                },
                success: function (result) {
                    var html = '';console.log(result);
                    jQuery.each(result.customers, function () {
                        html += '<div class="customer-card" ><div class="panel" ><div class="panel-title" >' + this.firstname;
                        html += ' ' + this.lastname + '<span class="pull-right" ># ' + this.customer_id + '</span></div><div class="panel-content" ><span>';
                        html += this.email + '</span><br/><span class="text-muted">' + ((this.birthday != '0000-00-00') ? this.birthday : '');
                        html += '</span><br/><div class="panel-footer"><a href="index.php?option=com_jeproshop&view=customer&task=view&customer_id=';
                        html += this.customer_id + '&' + options.customer.token + '=1" class="btn btn-default"><i class="icon-search"></i> ';
                        html += options.labels.details + '</a><button type="button" data-customer="' + this.customer_id + '" class="setup-customer btn ';
                        html += 'btn-default pull-right" style="margin-right:15px; "><i class="icon-arrow-right"></i> ' + options.labels.choose + '</button></div></div></div></div>';
                    });
                    customerWrapper.html(html);
                    resetBind();
                },
                error : function(msg){
                    //console.log(msg);
                }
            });
        }

        function resetBind(){
            jQuery('.fancybox').fancybox({
                'type': 'iframe',
                'width': '90%',
                'height': '90%'
            });

            jQuery('.fancybox_customer').fancybox({
                'type': 'iframe',
                'width': '90%',
                'height': '90%',
                'afterClose' : function () {
                    searchCustomers();
                }
            });
        }

        function searchProducts(){
            var productsPart = jQuery('#jform_products_part');
            productsPart.show();

            var url = 'index.php?option=com_jeproshop&view=order&use_ajax=1&task=search&tab=products';

            jQuery.ajax({
                type:"POST",
                url: url,
                async: true,
                dataType: "json",
                data : {
                    cart_id :parseInt(options.cart.cart_id),
                    customer_id : parseInt(options.customer.customer_id),
                    currency_id : parseInt(options.currency.currency_id),
                    product_search : jQuery('#jform_product').val()
                },
                success : function(res){
                    var productsFound = '';
                    var attributesHtml = '';
                    var customization_html = '';
                    options.stock = {};

                    if(res.found){
                        if (!options.customization_errors) {
                            jQuery('#jform_products_error').addClass('hide');
                        }else {
                            options.customization_errors = false;
                        }
                        jQuery('#jform_products_found').show();
                        productsFound += '<div class="control-label"><label for="jform_product_id" >' + options.labels.product + '</label></div>';
                        productsFound += '<div class="controls" ><select id="jform_product_id" >';
                        attributesHtml += '<div class="control-label" ><label >' + options.labels.combination + '</label><div class="controls">';
                        jQuery.each(res.products, function() {
                            var productId = this.product_id;
                            productsFound += '<option ' + (this.combinations.length > 0 ? 'rel="'+this.quantity_in_stock +'"' : '');
                            productsFound += ' value="'+ productId +'" >'+this.name + (this.combinations.length == 0 ? ' - '+ this.formatted_price : '') + '</option>';
                            attributesHtml += '<select class="product-attribute-id" id="jform_product_attribute_id_'+ productId +'" style="display:none;" >';

                            options.stock[productId] = [];
                            if (this.customizable == '1'){
                                customization_html += '<div class="bootstrap"><div class="panel"><div class="panel-title">' + options.labels.customization;
                                customization_html += '</div><form id="jform_customization_'+ productId +'" class="customization-id" method="post" ';
                                customization_html += ' enctype="multipart/form-data" action="' + options.cart.cart_link + '" style="display:none;" >';
                                customization_html += '<input type="hidden" name="jform[product_id]" value="' + productId + '" />';
                                customization_html += '<input type="hidden" name="jform[cart_id]" value="' + options.cart.cart_id +'" />';
                                customization_html += '<input type="hidden" name="task" value="update" />';
                                customization_html += '<input type="hidden" name="tab" value="customization_fields" />';
                                customization_html += '<input type="hidden" name="customer_id" value="' + options.customer.customer_id +'" />';
                                customization_html += '<input type="hidden" name="use_ajax" value="1" />';

                                var classCustomizationField;
                                jQuery.each(this.customization_fields, function() {
                                    classCustomizationField = "";
                                    var customizationFieldId = this.customization_field_id;
                                    if (this.required == 1){ classCustomizationField = 'required' }
                                    customization_html += '<div class="control-group"><div class="control-label" ><label class="' + classCustomizationField ;
                                    customization_html += '" for="jform_customization_' + productId  + '_' + customizationFieldId +'">';
                                    customization_html += this.name + '</label></div><div class="controls">';
                                    if (this.type == 0) {
                                        customization_html += '<input class="customization_field" type="file" name="customization_' + productId;
                                        customization_html += '_' + customizationFieldId + '" id="jform_customization_' + productId + '_' + customizationFieldId + '">';
                                    }else if (this.type == 1) {
                                        customization_html += '<input class="customization_field" type="text" name="customization_' + productId + '_' + customizationFieldId;
                                        customization_html += '" id="jform_customization_' + productId + '_' + customizationFieldId + '">';
                                    }
                                    customization_html += '</div></div></div>';
                                });
                                customization_html += '</form></div></div>';
                            }

                            jQuery.each(this.combinations, function() {
                                attributesHtml += '<option rel="'+this.quantity_in_stock+'" '+(this.default_on == 1 ? 'selected="selected"' : '') ;
                                attributesHtml += ' value="'+this.product_attribute_id +'">'+this.attributes +' - '+this.formatted_price+'</option>';
                                options.stock[productId][this.product_attribute_id] = this.quantity_in_stock;
                            });

                            options.stock[this.product_id][0] = this.stock[0];
                            attributesHtml += '</select>';
                        });
                        productsFound += '</select></div>';
                        jQuery('#jform_product_list').html(productsFound);
                        jQuery('#jform_attributes_list').html(attributesHtml);

                        var productIdWrapper = jQuery('#jform_product_id');
                        productIdWrapper.on('click', function () {
                            displayProductAttributes();
                            displayProductCustomizations();
                        });
                        jQuery('link[rel="stylesheet"]').each(function (i, element) {
                            var sheet = jQuery(element).clone();
                            jQuery('#jform_customization_list').contents().find('head').append(sheet);
                        });
                        jQuery('#jform_customization_list').contents().find('body').html(customization_html);
                        displayProductAttributes();
                        displayProductCustomizations();
                        productIdWrapper.change();
                    }else{
                        jQuery('#jform_products_found').hide();
                        var products_err = jQuery('#jform_products_error');
                        products_err.html(options.labels.no_products_found);
                        products_err.removeClass('hide');
                    }
                    resetBind();
                }
            });
        }

        function setupCustomer(customerId){
            jQuery('#jform_carts').show();
            jQuery('#jform_products_part').show();
            jQuery('#jform_vouchers_part').show();
            jQuery('#jform_address_part').show();
            jQuery('#jform_carriers_part').show();
            jQuery('#jform_summary_part').show();
            var newAddressBtn = jQuery('#jform_new_address');
            var addressLink = newAddressBtn.attr('href');
            options.customer.customer_id = customerId;
            options.cart.cart_id = 0;
            newAddressBtn.attr('href', document.getElementById('jform_new_address').href.replace(/customer_id=[0-9]+/, 'customer_id='+customerId));
            jQuery.ajax({
                type:"POST",
                url : 'index.php?option=com_jeproshop&view=cart&task=search&tab=carts&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    customer_id: customerId,
                    cart_id: options.cart.cart_id
                },
                success : function(res){
                    if(res.found){
                        var htmlCarts = '';
                        var htmlOrders = '';
                        var cartLink;
                        var index = 0;
                        jQuery.each(res.carts, function() {
                            cartLink = 'index.php?option=com_jeproshop&view=cart&task=view&cart_id='+ this.cart_id +'&' + options.cart.token + '=1&displaying=lite#';
                            htmlCarts += '<tr class="row_' + (index % 2) + '"><td>' + this.cart_id + '</td> <td>' + this.date_add + '</td><td class="center">'+ this.total_price+'</td> <td class="pull-right">';
                            htmlCarts += '<a title="' + options.labels.view_cart + '" class="fancybox btn btn-default" href="' + cartLink + '"><i class="icon-search"></i>&nbsp;';
                            htmlCarts += options.labels.details + '</a> &nbsp;<a href="#" title="' + options.labels.use_this_cart + '" class="use-cart btn btn-default" rel="';
                            htmlCarts += this.cart_id +'"><i class="icon-arrow-right"></i>&nbsp;' + options.labels.use_label + '</a></td></tr>';
                            index++;
                        });

                        index = 0;
                        jQuery.each(res.orders, function() {
                            htmlOrders += '<tr class="row_' + (index % 2) + '"><td>'+this.order_id +'</td>';
                            htmlOrders += '<td>'+this.date_add+'</td><td class="center" >'+(this.nb_products ? this.nb_products : '0');
                            htmlOrders += '</td><td class="center" >'+this.total_paid_real + '</td><td>' + this.payment+'</td><td class="center">';
                            htmlOrders += this.current_status + '</td><td class="text-right"><a href="index.php?option=com_jeproshop&view=order&order_id=';
                            htmlOrders += this.order_id + '&task=view&displaying=lite" title="' + options.labels.view_order;
                            htmlOrders += '" class="fancybox btn btn-default"><i class="icon-search"></i>&nbsp;' + options.labels.details +'</a>';
                            htmlOrders += '&nbsp;<a href="#" title="' + options.labels.duplicate_order + '" class="duplicate-order btn btn-default" rel="';
                            htmlOrders += this.order_id +'"><i class="icon-arrow-right"></i>&nbsp;' + options.labels.use_label + '</a></td></tr>';
                            index++;
                        });
                        jQuery('#jform_non_ordered_carts table tbody').html(htmlCarts);
                        jQuery('#jform_last_orders table tbody').html(htmlOrders);
                    }

                    if (res.cart_id){
                        options.cart.cart_id = res.cart_id;
                        jQuery('#jform_cart_id').val(options.cart.cart_id);
                    }
                    displaySummary(res);
                    resetBind();
                }, fail:function (res) {
                    console.log(res);
                }
            });
        }

        function updateAddresses(){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=update&tab=addresses&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    customer_id : options.customer.customer_id,
                    cart_id : options.cart.cart_id,
                    delivery_address_id: parseInt(jQuery('#jform_delivery_address_id').find(':selected').val()),
                    invoice_address_id: parseInt(jQuery('#jform_invoice_address_id').find(':selected').val())
                },
                success : function(res){
                    updateDeliveryOption();
                }
            });
        }

        function updateDeliveryOption(){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=update&tab=delivery_option&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    delivery_option: jQuery('#jform_delivery_option').find(':selected').val(),
                    gift: jQuery('#jform_order_gift').is(':checked') ? 1 : 0,
                    gift_message: jQuery('#jform_gift_message').val(),
                    recyclable: jQuery('#jform_carrier_recycled_package').is(':checked')?1:0,
                    customer_id : options.customer.customer_id,
                    cart_id : options.cart.cart_id
                },
                success : function(res){
                    displaySummary(res);
                }
            });
        }

        function displaySummary(jsonSummary){
            options.currency.format = jsonSummary.currency.format;
            options.currency.sign = jsonSummary.currency.sign;
            options.currency.blank = jsonSummary.currency.blank;
            options.priceDisplayPrecision = jsonSummary.currency.decimals ? 2 : 0;

            updateCartProducts(jsonSummary.summary.products, jsonSummary.summary.gift_products, jsonSummary.cart.delivery_address_id);
            updateCartVouchers(jsonSummary.summary.discounts);
            updateAddressesList(jsonSummary.addresses, jsonSummary.cart.delivery_address_id, jsonSummary.cart.invoice_address_id);

            var carriersBlock = jQuery("#jform_carriers_part");
            var summaryBlock = jQuery("#jform_summary_part");

            if (!jsonSummary.summary.products.length || !jsonSummary.addresses.length || !jsonSummary.delivery_option_list) {
                carriersBlock.hide();
                summaryBlock.hide();
            }else {
                carriersBlock.show();
                summaryBlock.show();
            }

            updateDeliveryOptionList(jsonSummary.delivery_option_list);

            if (jsonSummary.cart.gift == 1) {
                jQuery('#jform_order_gift').attr('checked', true);
            }else {
                jQuery('#jform_carrier_gift').removeAttr('checked');
            }

            if (jsonSummary.cart.recyclable == 1) {
                jQuery('#jform_carrier_recycled_package').attr('checked', true);
            }else {
                jQuery('#jform_carrier_recycled_package').removeAttr('checked');
            }

            if (jsonSummary.free_shipping == 1) {
                jQuery('#jform_free_shipping_on').attr('checked', true);
            }else {
                jQuery('#jform_free_shipping_off').attr('checked', true);
            }

            jQuery('#jform_gift_message').html(jsonSummary.cart.gift_message);
            if (!options.changed_shipping_price) {
                jQuery('#jform_shipping_price').html('<b>' + JeproTools.formatCurrency(parseFloat(jsonSummary.summary.total_shipping),
                        options.currency.format, options.currency.sign, options.currency.blank) + '</b>');
            }
            options.shipping_price_selected_carrier = jsonSummary.summary.total_shipping;

            jQuery('#jform_total_vouchers').html(JeproTools.formatCurrency(parseFloat(jsonSummary.summary.total_discounts_tax_exc), options.currency.format, options.currency.sign, options.currency.blank));
            jQuery('#jform_total_shipping').html(JeproTools.formatCurrency(parseFloat(jsonSummary.summary.total_shipping_tax_exc), options.currency.format, options.currency.sign, options.currency.blank));
            jQuery('#jform_total_taxes').html(JeproTools.formatCurrency(parseFloat(jsonSummary.summary.total_tax), options.currency.format, options.currency.sign, options.currency.blank));
            jQuery('#jform_total_without_taxes').html(JeproTools.formatCurrency(parseFloat(jsonSummary.summary.total_price_without_tax), options.currency.format, options.currency.sign, options.currency.blank));
            jQuery('#jform_total_with_taxes').html(JeproTools.formatCurrency(parseFloat(jsonSummary.summary.total_price), options.currency.format, options.currency.sign, options.currency.blank));
            jQuery('#jform_total_products').html(JeproTools.formatCurrency(parseFloat(jsonSummary.summary.total_products), options.currency.format, options.currency.sign, options.currency.blank));
            options.currency.currency_id = jsonSummary.cart.currency_id;
            var currencySelector = jQuery('#jform_currency_id');
            currencySelector.find(':selected').removeAttr('selected');
            currencySelector.find('[value="'+ options.currency.currency_id +'"]').attr('selected', true);
            options.lang_id = jsonSummary.cart.lang_id;
            jQuery('#jform_lang_id option').removeAttr('selected');
            jQuery('#jform_lang_id').find('[value="'+ options.lang_id +'"]').attr('selected', true);
            jQuery('#jform_send_email_to_customer').attr('rel', jsonSummary.order_link);
            jQuery('#jform_go_order_process').attr('href', jsonSummary.order_link);
            jQuery('#jform_order_message').val(jsonSummary.order_message);
            resetBind();
        }

        function updateCartProducts(products, gifts, deliveryAddressId){
            var cartContent = '';
            jQuery.each(products, function() {
                var productId = Number(this.product_id);
                var productAttributeId = Number(this.product_attribute_id);
                options.cart.quantity[Number(this.product_id)+'_'+Number(this.product_attribute_id)+'_'+Number(this.customization_id)] = this.cart_quantity;
                cartContent += '<tr><td><img src="'+this.image_link+'" title="'+this.name+'" /></td><td>'+this.name+'<br />'+this.attributes_small + '</td>';
                cartContent += '<td>'+this.reference+'</td><td><input type="text" rel="'+this.product_id +'_'+this.product_attribute_id +'" class="product_unit_price" value="';
                cartContent += this.numeric_price + '" /></td><td>';
                cartContent += (!this.customization_id ? '<div class="input-append"><button class="btn btn-default increase_quantity_product" rel="'+this.product_id +'_'+this.product_attribute_id +'_'+(this.customization_id ? this.customization_id : 0)+'" ><i class="icon-caret-up"></i> <a href="#" class="btn btn-default decrease_quantity_product" rel="'+this.product_id +'_'+this.product_attribute_id +'_'+(this.customization_id ? this.customization_id : 0)+'"><i class="icon-caret-down"></i></a></button>' : '');
                cartContent += (!this.customization_id ? '<input type="text" rel="'+this.product_id +'_'+this.product_attribute_id+'_'+(this.customization_id ? this.customization_id : 0)+'" class="cart_quantity" value="' + this.cart_quantity+'" />' : '');
                cartContent += (!this.customization_id ? '<div class="input-group-btn"><a href="#" class="delete_product btn btn-default" rel="delete_'+this.product_id +'_'+this.product_attribute_id +'_'+(this.customization_id ? this.customization_id : 0)+'" ><i class="icon-remove text-danger"></i></a></div></div>' : '');
                cartContent += '</td><td>' + JeproTools.formatCurrency(this.numeric_total, options.currency.format, options.currency.sign, options.currency.blank) + '</td></tr>';

                if (this.customization_id && this.customization_id != 0){
                    jQuery.each(this.customized_data[this.product_id][this.product_attribute_id][deliveryAddressId], function() {
                        var customizedDesc = '';
                        var customizationId = 0;
                        if (this.datas[1].length){
                            jQuery.each(this.datas[1],function() {
                                customizedDesc += this.name + ': ' + this.value + '<br />';
                                customizationId = this.customization_id;
                            });
                        }
                        if (this.datas[0] && this.datas[0].length)
                        {
                            jQuery.each(this.datas[0],function() {
                                customizedDesc += this.name + ': <img src="' + options.picture_dir + this.value + '_small" /><br />';
                                options.customization_id = this.customization_id;
                            });
                        }
                        cartContent += '<tr><td></td><td>'+customizedDesc+'</td><td></td><td></td><td>';
                        cartContent += '<div class="input-group fixed-width-md"><a href="#" class="btn btn-default increase_product_quantity" rel="';
                        cartContent += productId +'_'+ productAttributeId +'_'+ customizationId +'" ><i class="icon-caret-up"></i></a><br />';
                        cartContent += '<a href="#" class="btn btn-default decrease-product-quantity" rel="'+ productId +'_'+ productAttributeId;
                        cartContent += '_' + customizationId +'"><i class="icon-caret-down"></i></a></div><input type="text" rel="'+ productId;
                        cartContent += '_' + productAttributeId +'_'+ customization_id +'" class="cart_quantity" value="'+this.quantity+'" />';
                        cartContent += '<div class="input-group-btn"><a href="#" class="delete_product btn btn-default" rel="delete_'+ productId;
                        cartContent += '_'+ productAttributeId +'_' + customizationId+'" ><i class="icon-remove"></i></a>';
                        cartContent += '</div></div></td><td></td></tr>';
                    });
                }
            });

            jQuery.each(gifts, function() {
                cartContent += '<tr><td><img src="'+this.image_link+'" title="'+this.name+'" /></td><td>'+this.name+'<br />'+this.attributes_small+'</td><td>'+this.reference+'</td>';
                cartContent += '<td>' + options.labels.gift + '</td><td>'+this.cart_quantity+'</td><td>' + options.labels.gift + '</td></tr>';
            });
            jQuery('#jform_customer_cart tbody').html(cartContent);
        }

        function updateCartVouchers(vouchers){
            var vouchersHtml = '';
            if (typeof(vouchers) == 'object') {
                jQuery.each(vouchers, function () {
                    vouchersHtml += '<tr><td>' + this.name + '</td><td>' + this.description + '</td><td>' + this.real_value + '</td>';
                    vouchersHtml += '<td class="text-right"><a href="#" class="btn btn-default delete_discount" rel="' + this.discount_id + '">';
                    vouchersHtml += '<i class="icon-remove text-danger"></i>&nbsp;' + options.labels.delete + '</a></td></tr>';
                });
            }
            jQuery('#jform_voucher_list tbody').html(jQuery.trim(vouchersHtml));
            if (jQuery('#jform_voucher_list tbody').html().length == 0) {
                jQuery('#jform_voucher_list').hide();
            }else {
                jQuery('#jform_voucher_list').show();
            }
        }

        function addCartRule(cartRuleId){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=add&tab=voucher&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    cart_rule_id : cartRuleId,
                    cart_id : options.cart.cart_id,
                    customer_id : options.customer.customer_id
                },
                success : function(res){
                    displaySummary(res);
                    jQuery('#jform_voucher').val('');
                    var errors = '';
                    if (res.errors.length > 0){
                        jQuery.each(res.errors, function() {
                            errors += this+'<br/>';
                        });
                        jQuery('#jform_vouchers_error').html(errors).show();
                    }else {
                        jQuery('#jform_vouchers_error').hide();
                    }
                }
            });
        }

        function useCart(newCartId){
            options.cart.cart_id = newCartId;
            var cartIdWrapper = jQuery('#jform_cart_id');
            cartIdWrapper.val(options.cart.cart_id);
            cartIdWrapper.val(options.cart.cart_id);

            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=summary&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    ajax: "1",
                    cart_id: options.cart.cart_id,
                    customer_id: options.customer.customer_id
                },
                success : function(res){
                    displaySummary(res);
                }
            });
        }

        function customizationProductListener(){
            //refresh form customization
            searchProducts();
            addProductProcess();
        }

        function addProduct(){
            var productId = jQuery('#jform_product_id').find(':selected').val();
            $('#jform_products_found #jform_customization_list').contents().find('#jform_customization_'+ productId).submit();

            addProductProcess();
        }

        function duplicateOrder(orderId){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=duplicate&tab=order&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    order_id : orderId,
                    customer_id : options.customer.customer_id
                },
                success : function(res){
                    jQuery('#jform_cart_id').val(res.cart.cart_id);
                    displaySummary(res);
                }
            });
        }

        function updateProductPrice(productId, productAttributeId, newPrice){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=update&tab=product_price&use_ajax=1' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    ajax: "1",
                    cart_id : options.cart.cart_id,
                    product_id : productId,
                    product_attribute_id : productAttributeId,
                    customer_id: options.customer.customer_id,
                    price: Number(newPrice.replace(",",".")).toFixed(4).toString()
                },
                success : function(res){
                    displaySummary(res);
                }
            });
        }

        function getSummary(){
            useCart(options.cart.cart_id);
        }

        function deleteVoucher(cartRuleId){
            var request = 'index.php?option=com_jeproshop&view=cart&task=delete&tab=voucher&use_ajax=1&' + options.cart.token + '=1';
            jQuery.ajax({
                type:"POST",
                url: request ,
                async: true,
                dataType: "json",
                data :{
                    cart_rule_id : parseInt(cartRuleId),
                    cart_id : parseInt(options.cart.cart_id),
                    customer_id : parseInt(options.customer.customer_id)
                },
                success : function(res){
                    displaySummary(res);
                }
            });
        }

        function displayQuantityInStock(id){
            var productId = jQuery('#jform_product_id').val();
            var productAttributeId = 0;
            if (jQuery('#jform_product_attribute_id_' + productId + ' option').length) {
                productAttributeId = jQuery('#jform_product_attribute_id_' + productId).val();
            }

            jQuery('#jform_quantity_in_stock').html(options.stock[productId][productAttributeId]);
        }

        function deleteProduct(productId, productAttributeId, customizationId){
            var request = 'index.php?option=com_jeproshop&view=cart&task=delete&tab=product&cart_id=' + parseInt(options.cart.cart_id);
            request += '&product_id=' + parseInt(productId) + '&product_attribute_id=' + parseInt(productAttributeId) + '&customization_id=';
            request += parseInt(customizationId) + "&customer_id=" + parseInt(options.customer.customer_id) + '&' + options.cart.token + '=1';
            jQuery.ajax({
                type:"POST",
                url:  request,
                async: true,
                dataType: "json",
                success : function(res){
                    displaySummary(res);
                }
            });
        }

        function updateDeliveryOptionList(deliveryOptionList){
            var html = '';
            var carrierForm = jQuery("#jform_carrier_form");
            var deliveryOption = jQuery("#jform_delivery_option");
            var carrierErrorBlock = jQuery("#jform_carriers_error");
            if (deliveryOptionList.length > 0){
                jQuery.each(deliveryOptionList, function() {
                    html += '<option value="'+this.key+'" '+((jQuery("#jform_delivery_option").val() == this.key) ? 'selected="selected"' : '')+'>'+this.name+'</option>';
                });
                carrierForm.show();
                deliveryOption.html(html);
                carrierErrorBlock.hide();
            } else {
                carrierForm.hide();
                carrierErrorBlock.show().html('\'' + options.labels.no_carrier_can_be_applied_to_this_order +'\'');
            }
        }

        function displayProductAttributes(){
            var attributesList = jQuery('#jform_attributes_list');
            var productWrap = jQuery('#jform_product_id');
            if (jQuery('#jform_product_attribute_id_'+ productWrap.find(':selected').val()+' option').length === 0) {
                attributesList.hide();
            }else {
                attributesList.show();
                jQuery('.product-attribute-id').hide();
                jQuery('#jform_product_attribute_id_'+ productWrap.find(':selected').val()).show();
            }
        }

        function updateCartPaymentList(paymentList){
            jQuery('#jform_payment_list').html(paymentList);
        }


        function displayProductCustomizations(){
            var customizationWrap = jQuery('#jform_customization_list');
            var productWrap = jQuery('#jform_product_id');
            if (customizationWrap.contents().find('#jform_customization_'+ productWrap.find(':selected').val()).children().length === 0)
                customizationWrap.hide();
            else{
                customizationWrap.show();
                customizationWrap.contents().find('.customization_id').hide();
                customizationWrap.contents().find('#jform_customization_'+ productWrap.find(':selected').val()).show();
                customizationWrap.css('height', customizationWrap.contents().find('#jform_customization_'+ productWrap.find(':selected').val()).height() + 95+'px');
            }
        }

        function updateQuantity(productId, productAttributeId, customizationId, quantity){
            var request = 'index.php?option=com_jeproshop&view=cart&task=update&tab=quantity&use_ajax=1&';
            request += options.cart.token + '=1';

            jQuery.ajax({
                type:"POST",
                url: request,
                async: true,
                dataType: "json",
                data : {
                    product_id : parseInt(productId),
                    quantity : parseInt(quantity),
                    product_attribute_id : parseInt(productAttributeId),
                    customization_id : parseInt(customizationId),
                    customer_id : parseInt(options.customer.customer_id),
                    cart_id : parseInt(options.cart.cart_id)
                },
                success : function(res){
                    displaySummary(res);
                    var errors = '';
                    var productErrorWrapper = jQuery('#jform_products_error');
                    if (res.errors.length){
                        jQuery.each(res.errors, function() {
                            errors += this + '<br />';
                        });
                        productErrorWrapper.removeClass('hide');
                    }else {
                        productErrorWrapper.addClass('hide');
                    }
                    productErrorWrapper.html(errors);
                }
            });
        }

        function resetShippingPrice(){
            jQuery('#jform_shipping_price').val(options.shipping_price_selected_carrier);
            options.changed_shipping_price = false;
        }

        function addProductProcess(){
            var productId = jQuery('#jform_product_id').find(':selected').val();
            var productErrorWrapper = jQuery('#jform_products_error');
            jQuery('#jform_products_found #jform_customization_list').contents().find('#jform_customization_'+ productId).submit();
            if (options.customization_errors) {
                productErrorWrapper.removeClass('hide');
            }else{
                productErrorWrapper.addClass('hide');
                updateQuantity(productId, jQuery('#jform_product_attribute_id_' + productId).find(':selected').val(), 0, jQuery('#jform_quantity').val());
            }
        }

        function sendMailToCustomer(){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=order&task=send&tab=validate_mail_order&' + options.order.token + '=1',
                async: true,
                dataType: "json",
                data :{
                    cart_id : options.cart.cart_id,
                    customer_id : options.customer.customer_id
                },
                success : function(res){
                    var sendEmailFeedBack = jQuery('#jform_send_email_feedback');
                    if (res.errors) {
                        sendEmailFeedBack.removeClass('hide').removeClass('alert-success').addClass('alert-danger');
                    }else {
                        sendEmailFeedBack.removeClass('hide').removeClass('alert-danger').addClass('alert-success');
                    }
                    sendEmailFeedBack.html(res.result);
                }
            });
        }

        function updateAddressesList(addresses, deliveryAddressId, invoiceAddressId){
            var deliveryAddressesOptions = '';
            var invoiceAddressesOptions = '';
            var invoiceAddressDetail = '';
            var deliveryAddressDetail = '';
            var deliveryAddressEditLink = '';
            var invoiceAddressEditLink = '';

            jQuery.each(addresses, function() {
                if (this.address_id == invoiceAddressId){
                    invoiceAddressDetail = this.formated_address;
                    invoiceAddressEditLink = 'index.php?option=com_jeproshop&view=address&task=edit&address_id=' + this.address_id + '&' + options.address.token + '=1'; //jQuerylink->getAdminLink('AdminAddresses')}&id_address="+this.id_address+"&updateaddress&realedit=1&liteDisplaying=1&submitFormAjax=1#";
                }

                if(this.address_id == deliveryAddressId){
                    deliveryAddressDetail = this.formated_address;
                    deliveryAddressEditLink = 'index.php?option=com_jeproshop&view=address&tas=edit&address_id=' + this.address_id + '&' + options.address.token + '=1'; // {jQuerylink->getAdminLink('AdminAddresses')}&id_address="+this.id_address+"&updateaddress&realedit=1&liteDisplaying=1&submitFormAjax=1#";
                }

                deliveryAddressesOptions += '<option value="'+this.address_id +'" '+(this.address_id == deliveryAddressId ? 'selected="selected"' : '')+'>'+this.alias+'</option>';
                invoiceAddressesOptions += '<option value="'+this.address_id +'" '+(this.address_id == invoiceAddressId ? 'selected="selected"' : '')+'>'+this.alias+'</option>';
            });

            if (addresses.length == 0)
            {
                jQuery('#jform_addresses_err').show().html('\'' + options.labels.you_must_add_at_least_one_address_to_process_the_order + '\'');
                jQuery('#jform_delivery_address, #jform_invoice_address').hide();
            }
            else
            {
                jQuery('#jform_addresses_err').hide();
                jQuery('#jform_delivery_address, #jform_invoice_address').show();
            }

            jQuery('#jform_delivery_address_id').html(deliveryAddressesOptions);
            jQuery('#jform_invoice_address_id').html(invoiceAddressesOptions);
            jQuery('#jform_delivery_address_detail').html(deliveryAddressDetail);
            jQuery('#jform_invoice_address_detail').html(invoiceAddressDetail);
            jQuery('#edit_delivery_address').attr('href', deliveryAddressEditLink);
            jQuery('#jform_edit_invoice_address').attr('href', invoiceAddressEditLink);
        }


        function updateCurrency(){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=update&tab=currency&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    currency_id : parseInt(jQuery('#jform_currency_id').find(':selected').val()),
                    customer_id : options.customer.customer_id,
                    cart_id : options.cart.cart_id
                },
                success : function(res){
                    displaySummary(res);
                }
            });
        }

        function updateLang(){
            jQuery.ajax({
                type:"POST",
                url: 'index.php?option=com_jeproshop&view=cart&task=update&tab=lang&use_ajax=1&' + options.cart.token + '=1',
                async: true,
                dataType: "json",
                data : {
                    lang_id : jQuery('#jform_lang_id').find(':selected').val(),
                    customer_id : options.customer.customer_id,
                    cart_id : options.cart.cart_id
                },
                success : function(res) {
                    displaySummary(res);
                }
            });
        }
    }
    
})(jQuery);