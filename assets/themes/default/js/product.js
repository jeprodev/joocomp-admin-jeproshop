/**
 * Created by jeproQxT on 26/07/2017.
 */
(function ($) {
    $.fn.JeproProduct = function (opts) {
        var defaults = {
            product_id: 0,
            product_price: 0,
            product_prices: [],
            product_images : [],
            product_reference: '',
            product_price_without_reduction: 0,
            no_tax : 0,
            eco_tax_tax_rate : 0,
            eco_tax_tax_excluded : 0,
            price_display_precision : 4,
            currencies : [],
            taxes : [],
            all_customers_label : '',
            no_customers_label :'',
            delete_price_rule_message : '',
            product_token :  '',
            customer_token : '',
            store_used_groups : {}
        };
            /*global_quantity : 0,
            quantity_available : 0,
            default_eco_tax : 0,
            customization_fields: [],
            combinations_images: [],
            product_has_attributes : false,
            allow_buy_when_out_of_stock : 0,
            attributes_combinations : [],
            attribute_anchor_separator : ' - ',
            original_url : '',
            combinations_hash_set : undefined,
            selected_combination : [],
            product_available_for_order : 0,
            stock_management : 0,
            specific_currency : undefined,
            does_not_exist_message : "",
            does_not_exist_no_more_message : '',
            does_not_exist_no_more_but_message : '',
            available_now_value_message : '',
            available_later_value_message : '',
            display_discount_price : 0,
            serial_scroll_number_images_displayed : 3,
            product_file_default_html : '',
            uploading_in_progress : '',
            product_token : '',
            task : 'edit',
            max_quantity_to_allow_display_of_last_quantity : '',
            quantities_display_allowed : '',
            combinations_from_controller : [],
            product_price_tax_excluded: 0,
            product_price_tax_included : 0,
            tax_rate : 0,
            group_reduction : 0,

            product_unit_price_ratio: 0,
            no_tax_for_this_product: 0,
            customer_group_without_tax : 0,
            up_to_txt : '',
            selector : '.ajax-block-product',
            boxSize : { x: 250, y: 250 },
            jqzoom_enabled : false,
            product_base_price_tax_excluded : ''
        }; */

        var options = jQuery.extend(defaults, opts);

        var productObject = this;
        return productObject.each(function(){
            if(options.product_id) {
                initialize();
            }
        });

        function initialize(){
            jQuery('.datepicker').datepicker({
                prevText: '', nextText: '', dateFormat: 'yy-mm-dd',
                ampm : false, amNames : ['AM', 'A'], pmNames : ['PM', 'P'],
                timeFormat : 'hh:mm:ss tt', timeSuffix : ''
                //timeOnlyTitle :

            });
            initializePrice();
            //initializeDeclination();
            //initializeImages();
        }

        function initializePrice(){
            var priceBoxes = jQuery(".price-box");
            priceBoxes.each(function() {
                var elt = jQuery('#' + this.id);
                elt.on('keyup', function(){ elt.val(document.getElementById(this.id).value.replace(/,/, '.')); });
                elt.on('change', function(){ elt.val(document.getElementById(this.id).value.replace(/,/, '.')); });
            });

            calculatePriceTaxIncluded();
            unitPriceWithTax('unit');

            var wholeSalePriceWrapper = jQuery('#jform_wholesale_price');
            var priceTaxExcluded = jQuery('#jform_price_tax_excluded');
            var realPriceTaxExcluded = jQuery('#jform_real_price_tax_excluded');
            var priceTaxIncluded = jQuery('#jform_price_tax_included');
            var priceType = jQuery('#jform_price_type');
            var taxRulesGroupWrapper = jQuery('#jform_tax_rules_group_id');
            var ecoTaxWrapper = jQuery('#jform_ecotax');
            var unitPriceWrapper = jQuery('#jform_unit_price');
            var unityWrapper = jQuery('#jform_unity');

            priceTaxExcluded.on('keyup', function(evt){
                priceType.val('TE');
                if(JeproTools.isArrowKey(evt)){ return; }
                realPriceTaxExcluded.val(priceTaxExcluded.val());
                calculatePriceTaxIncluded();
            });

            priceTaxExcluded.on('change', function () {
                realPriceTaxExcluded.val(priceTaxExcluded.val());
            });

            taxRulesGroupWrapper.on('change', function(){ console.log(getTaxes());
                calculatePrice();
                unitPriceWithTax('unit');
            });

            priceTaxIncluded.on('keyup', function(evt){
                priceType.val('TI');
                if(JeproTools.isArrowKey(evt)){ return; }
                calculatePrice();
            });

            priceTaxIncluded.on('change', function(evt){
                if(JeproTools.isArrowKey(evt)){ return; }
            });

            unitPriceWrapper.on('keyup', function (evt) {
                if(JeproTools.isArrowKey(evt)){ return; }
                unitPriceWrapper.val(document.getElementById('jform_unit_price').value.replace(/,/g, '.'));
                unitPriceWithTax('unit');
            });

            unityWrapper.on('keyup', function(evt){
                if(JeproTools.isArrowKey(evt)){ return; }
                unitySecond();
            });
            unityWrapper.change(function(){ unitySecond(); });


            ecoTaxWrapper.on('keyup', function (evt) {
                priceType.val('TI');
                if(JeproTools.isArrowKey(evt)){ return; }
                calculatePriceTaxExcluded();

                if(parseInt(ecoTaxWrapper.val()) > priceTaxExcluded.val()){
                    ecoTaxWrapper.val(priceTaxExcluded.val());
                }

                if(isNaN(ecoTaxWrapper.val())){ ecoTaxWrapper.val(0); }
            });

            var savePriceButton = jQuery("#jform_save_price");
            savePriceButton.on('click', function(evt){ 
                evt.stopPropagation();
                var wholeSalePrice = jQuery('#jform_wholesale_price').val();
                wholeSalePrice = (typeof wholeSalePrice != 'undefined') ? parseFloat(wholeSalePrice) : 0;
                var price = jQuery('#jform_price_tax_excluded').val();
                price = (typeof price != 'undefined') ? parseFloat(price) : 0;
                var unitPriceRatio = jQuery('#jform_unit_price').val();
                unitPriceRatio = (typeof unitPriceRatio !== 'undefined') ? parseFloat(unitPriceRatio) : 0;
                var onSale = jQuery('#jform_on_sale').is(':checked') ? 1 : 0;
                var taxRulesGroupId = jQuery('#jform_tax_rules_group_id').find(':selected').val();
                var ecoTax = jQuery('#jform_ecotax').val();
                ecoTax = (typeof ecoTax !== 'undefined') ? parseFloat(ecoTax) : 0;
                var unity = jQuery('#jform_unity').val();
                unity = (typeof unity != 'undefined') ? unity : '';

                //var data = 
                var url = 'index.php?option=com_jeproshop&view=product&task=update&use_ajax=1&tab=price&' + options.product_token + '=1';
                jQuery.ajax({
                    type : "POST",
                    url : url,
                    async : true,
                    dataType : "json",
                    data : {
                        product_id : options.product_id,
                        whole_sale_price : wholeSalePrice,
                        price : price, 
                        unit_price_ratio : unitPriceRatio ,
                        on_sale : onSale, 
                        tax_rules_group_id : taxRulesGroupId,
                        eco_tax : ecoTax,
                        unity : unity
                    },
                    success:function(result){
                        Joomla.renderMessages({"success" : [result.messages]});
                    },
                    fail:function (result) {
                        Joomla.renderMessages({"error" : [result.messages]});
                    }
                });
            });

            var showSpecificPriceForm = jQuery('#jform_show_specific_price');
            var hideSpecificPriceForm = jQuery('#jform_hide_specific_price');
            var specificPriceForm = jQuery('#jform_add_specific_price_form');
            var specificPriceCurrency = jQuery('#jform_specific_price_currency_0');
            showSpecificPriceForm.on('click', function (evt) {
                evt.stopPropagation();
                evt.stopImmediatePropagation();
                specificPriceForm.delay(200).fadeIn();
                showSpecificPriceForm.hide();
                hideSpecificPriceForm.show();
            });

            hideSpecificPriceForm.on('click', function (evt) {
                evt.stopPropagation();
                evt.stopImmediatePropagation();
                specificPriceForm.hide();
                showSpecificPriceForm.show();
                hideSpecificPriceForm.hide();
            });

            specificPriceCurrency.on('change', function () {
                changeSpecificPriceCurrency(0);
            });

            var saveSpecificPriceButton = jQuery('#jform_save_specific_price');
            saveSpecificPriceButton.on('click', function(evt){
                evt.stopImmediatePropagation();
                evt.stopPropagation();
                var currencyId = jQuery('#jform_specific_price_currency_id').find(':selected').val();
                currencyId = (typeof currencyId !== 'undefined') ? parseInt(currencyId) : 0;

                var countryId = jQuery('#jform_specific_price_country_id').find(':selected').val();
                countryId = (typeof countryId !== 'undefined') ? parseInt(countryId) : 0;

                var customerId = jQuery('#jform_specific_price_customer_id').find(':selected').val();
                customerId = (typeof customerId !== 'undefined') ? parseInt(customerId) : 0;

                var groupId = jQuery('#jform_specific_price_group_id').find(':selected').val();
                groupId = (typeof groupId !== 'undefined') ? parseInt(groupId) : 0;

                var attributeId = jQuery('#jform_specific_price_product_attribute_id').find(':selected').val();
                attributeId = (typeof attributeId !== 'undefined') ? parseInt(attributeId) : 0;

                var availableFrom = jQuery('#jform_specific_price_from').val();

                var availableTo = jQuery('#jform_specific_price_to').val();

                var startingAt = jQuery('#jform_specific_price_from_quantity').val();
                startingAt = (typeof startingAt !== 'undefined') ? parseInt(startingAt) : 1;

                var price = jQuery('#jform_specific_price_price').val();
                price = (typeof price !== 'undefined') ? parseFloat(price) : 0;

                var reduction = jQuery('#jform_specific_price_reduction').val();
                reduction = (typeof reduction !== 'undefined') ? parseFloat(reduction) : 0;

                var reductionType = jQuery('#jform_specific_price_reduction_type').find(':selected').val();


                var specificUrlPath = 'index.php?option=com_jeproshop&view=product&task=save&use_ajax=1&tab=specific&' + options.product_token + '=1';
                jQuery.ajax({
                    type : "POST",
                    url : specificUrlPath,
                    async : true,
                    dataType : "json",
                    data : {
                        product_id : options.product_id,
                        currency_id : currencyId,
                        country_id : countryId,
                        group_id : groupId,
                        customer_id : customerId,
                        from : availableFrom,
                        to : availableTo,
                        starting_at : startingAt,
                        price : price,
                        reduction : reduction,
                        reduction_type : reductionType,
                        attribute_id : attributeId
                    },
                    success : function(result){
                        console.log(result);
                    }
                });
            });
  /*
            var reductionType = jQuery('#jform_specific_price_reduction_type');
            reductionType.change(function(){
                var reductionTax = jQuery('#jform_specific_price_reduction_tax');
                if(reductionType.find(':selected').val() == 'percentage'){
                    reductionTax.hide();
                }else{ reductionTax.show();  }
            });

            var productAttributeId = jQuery('#jform_product_attribute_id');
            productAttributeId.change(function(){
                jQuery('#jform_specific_price_current_price_without_tax').html(options.product_prices[productAttributeId.find(':selected').val()]);
            });



            var leaveBasePrice = jQuery('#jform_leave_base_price');
            leaveBasePrice.on('click', function(){ 
                var price = jQuery('#jform_specific_price_price');
                if(leaveBasePrice.is(':checked')){
                    price.attr('disabled', 'disabled');
                }else{
                    price.prop('disabled', false);
                }
            }); */
        }

        function calculatePriceTaxIncluded(){
            var realPriceTaxExcluded = parseFloat(document.getElementById('jform_real_price_tax_excluded').value.replace(/,/g, '.'));
            var newPrice = addTaxes(realPriceTaxExcluded);
            var priceTaxIncluded = jQuery('#jform_price_tax_included');
            var taxIncludedValue = (isNaN(newPrice) == true || newPrice < 0) ? '0.00' : JeproTools.roundPrice(newPrice, options.price_display_precision);
            priceTaxIncluded.val(taxIncludedValue);

            var finalPrice = jQuery('#jform_final_price');
            finalPrice.innerHTML = (isNaN(newPrice) == true || newPrice < 0) ? '0.00' :
                JeproTools.roundPrice(newPrice, options.price_display_precision).toFixed(options.price_display_precision);
            var finalPriceWithoutTax = jQuery('#jform_final_price_without_tax');
            finalPriceWithoutTax.innerHTML = (isNaN(realPriceTaxExcluded) == true || realPriceTaxExcluded < 0) ? '0.00' :
                (JeproTools.roundPrice(realPriceTaxExcluded, options.price_display_precision)).toFixed(options.price_display_precision);
            calculateReduction(); 

            if (isNaN(parseFloat(priceTaxIncluded.val()))){
                priceTaxIncluded.val('');
                finalPrice.html('');
            }else{
                priceTaxIncluded.val((parseFloat(priceTaxIncluded.val()) + getEcoTaxTaxIncluded()).toFixed(options.price_display_precision));
                finalPrice.html(parseFloat(priceTaxIncluded.val()).toFixed(options.price_display_precision));
            }
        }

        function calculatePriceTaxExcluded(){
            var ecoTaxTaxExcl =  jQuery('#jform_ecotax').val() / (1 + options.eco_tax_tax_rate);
            var priceTaxIncluded = parseFloat(document.getElementById('jform_price_tax_included').value.replace(/,/g, '.'));
            var newPrice = removeTaxes(JeproTools.roundPrice(priceTaxIncluded - getEcoTaxTaxIncluded(), options.price_display_precision));
            var priceTaxExcluded = jQuery('#jform_price_tax_excluded');
            var realPriceTaxExcluded = jQuery('#jform_real_price_tax_excluded');
            priceTaxExcluded.val((isNaN(newPrice) == true || newPrice < 0) ? '' : JeproTools.roundPrice(newPrice, options.price_display_precision).toFixed(options.price_display_precision));

            realPriceTaxExcluded.val((isNaN(newPrice) == true || newPrice < 0) ? 0 : JeproTools.roundPrice(newPrice, 9));
            jQuery('#jform_final_price').html((isNaN(newPrice) == true || newPrice < 0) ? '' :
                JeproTools.roundPrice(priceTaxIncluded, options.price_display_precision).toFixed(options.price_display_precision));
            jQuery('#jform_final_price_without_tax').html((isNaN(newPrice) == true || newPrice < 0) ? '0.00' : (JeproTools.roundPrice(newPrice, options.price_display_precision)).toFixed(options.price_display_precision));
            calculateReduction();
        }

        function calculateReduction(){
            if (parseFloat(jQuery('#jform_reduction_price').val()) > 0) {
                reductionPrice();
            }else if (parseFloat(jQuery('#jform_reduction_percent').val()) > 0) {
                reductionPercent();
            }
        }

        function reductionPrice(){
            var price = jQuery('#jform_price_tax_included');
            var priceWithOutTaxes = jQuery('#jform_price_tax_excluded');
            var newPrice = jQuery('#jform_final_price');
            var newPriceWithOutTax = jQuery('#jform_final_price_without_tax');
            var currentPrice = price.val();
            var reductionPrice = jQuery('#jform_reduction_price');
            var reductionPercent = jQuery('#jform_reduction_percent');

            reductionPercent.val(0);
            if (isInReductionPeriod()){
                //var reductionPrice = jQuery('reduction_price');
                if (parseFloat(currentPrice) <= parseFloat(reductionPrice.val()))
                    reductionPrice.val(currentPrice);
                if (parseFloat(reductionPrice.val()) < 0 || isNaN(parseFloat(currentPrice)))
                    reductionPrice.val(0);
                currentPrice = currentPrice - reductionPrice.val();
            }

            newPrice.innerHTML = (JeproTools.roundPrice(parseFloat(currentPrice),options.price_display_precision) + getEcoTaxTaxIncluded()).toFixed(options.price_display_precision);
            var reductionPriceWithOutTaxes = JeproTools.roundPrice(removeTaxes(reductionPrice.val()), options.price_display_precision);
            newPriceWithOutTax.innerHTML = JeproTools.roundPrice(priceWithOutTaxes.val() - reductionPriceWithOutTaxes, options.price_display_precision).toFixed(options.price_display_precision);
        }

        function reductionPercent(){
            var priceTaxIncluded    = jQuery('#jform_price_tax_included');
            var newPrice = jQuery('#jform_final_price');
            var newPriceWithOutTax = jQuery('#jform_final_price_without_tax');
            var currentPrice = priceTaxIncluded.val();
            var reductionPrice = jQuery('#jform_reduction_price');
            var reductionPercent = jQuery('#jform_reduction_percent');

            reductionPrice.val(0);
            if (isInReductionPeriod()){
                if (parseFloat(reductionPercent.val()) >= 100)
                    reductionPercent.val(100);
                if (parseFloat(reductionPercent.val()) < 0)
                    reductionPercent.val(0);
                currentPrice = priceTaxIncluded.val() * (1 - (reductionPercent.val() / 100));
            }

            newPrice.innerHTML = (JeproTools.roundPrice(parseFloat(currentPrice), options.price_display_precision) + getEcoTaxTaxIncluded()).toFixed(options.price_display_precision);
            newPriceWithOutTax.innerHTML = JeproTools.roundPrice(parseFloat(removeTaxes(JeproTools.roundPrice(currentPrice, options.price_display_precision))), options.price_display_precision).toFixed(options.price_display_precision);
        }

        function isInReductionPeriod(){
            var start  = jQuery('#jform_reduction_from').val();
            var end    = jQuery('#jform_reduction_to').val();

            if (start == end && start != "" && start != "0000-00-00 00:00:00") return true;

            var startDate  = new Date(start.replace(/-/g,'/'));
            var endDate  = new Date(end.replace(/-/g,'/'));
            var today  = new Date();

            return (startDate <= today && endDate >= today);
        }
 /*
        function decimalTruncate(source, decimals){
            if (typeof(decimals) == 'undefined'){ decimals = options.price_display_precision; }
            source = source.toString();
            var pos = source.indexOf('.');
            return parseFloat(source.substr(0, pos + decimals + 1));
        }
*/
        function unitPriceWithTax(type) {
            var priceWithTax = parseFloat(document.getElementById('jform_' + type+ '_price').value.replace(/,/g, '.'));
            var newPrice = addTaxes(priceWithTax);
            jQuery('#jform_' + type + '_price_with_tax').html((isNaN(newPrice) == true || newPrice < 0) ? '0.00' : JeproTools.roundPrice(newPrice, options.price_display_precision).toFixed(options.price_display_precision));
        }

        function unitySecond(){
            var unity = jQuery('#jform_unity')
            jQuery('#jform_unity_second').html(unity.val());
            if (unity.get(0).value.length > 0)
            {
                jQuery('#unity_third').html(unity.val());
                jQuery('#tr_unit_impact').show();
            }
            else
                jQuery('#tr_unit_impact').hide();
        }

        function changeSpecificPriceCurrency(index){
            var currencyWrapper = jQuery('#jform_specific_price_currency_' + index);
            var currencyId = currencyWrapper.val();
            var reductionType = jQuery("#jform_specific_price_reduction_type");
            /*if (currencyId > 0)
               reductionType.find('option[value="amount"]').text(jQuery('#jform_specific_price_currency_' + index + ' option[value= ' + currencyId + ']').text());
            else if (typeof currencyName !== 'undefined') {
                reductionType.find('option[value="amount"]').text(currencyName);
            }*/

            if (options.currencies[currencyId].format == 2 || options.currencies[currencyId].format == 4)
            {
                jQuery('#jform_specific_price_currency_sign_pre_' + index).html('');
                jQuery('#jform_specific_price_currency_sign_post_' + index).html(' ' + options.currencies[currencyId].sign);
            }
            else if (options.currencies[currencyId].format == 1 || options.currencies[currencyId].format == 3)
            {
                jQuery('#jform_specific_price_currency_sign_post_' + index).html('');
                jQuery('#jform_specific_price_currency_sign_pre_' + index).html(options.currencies[currencyId].sign + ' ');
            }
        }
  /*
        function calculateImpactPriceTaxIncluded(){
            var priceTE = parseFloat(document.getElementById("#jform_attribute_real_price_tax_excluded").value.replace(/,/g, '.'));
            var newPrice = addTaxes(priceTE);
            var attributePriceTaxIncluded = jQuery("#jform_attribute_price_tax_included");
            attributePriceTaxIncluded.val((isNaN(newPrice) == true || newPrice < 0) ? '' : JeproTools.roundPrice(newPrice, options.price_display_precision).toFixed(options.price_display_precision));
            var attributePriceImpact = jQuery("#jform_attribute_price_impact");
            var finalPrice = jQuery("#jform_final_price");
            var total = JeproTools.roundPrice((parseFloat(attributePriceTaxIncluded.val()) * parseInt(attributePriceImpact.val()) + parseFloat(finalPrice.html())), options.price_display_precision);
            var attributeNewTotalPrice = jQuery("#jform_attribute_new_total_price");
            if (isNaN(total) || total < 0)
                attributeNewTotalPrice.html('0.00');
            else
                attributeNewTotalPrice.html(total);
        }

        function calculateImpactPriceTaxExcluded(){
            var priceTI = parseFloat(document.getElementById("#jform_attribute_price_tax_included").value.replace(/,/g, '.'));
            priceTI = (isNaN(priceTI)) ? 0 : JeproTools.roundPrice(priceTI);
            var newPrice = removeTaxes(JeproTools.roundPrice(priceTI, options.price_display_precision));
            jQuery('#jform_attribute_price').val((isNaN(newPrice) == true || newPrice < 0) ? '' : JeproTools.roundPrice(newPrice, options.price_display_precision).toFixed(options.price_display_precision));
            var attributeRealPriceTaxExcluded = jQuery("#jform_attribute_real_price_tax_excluded");
            attributeRealPriceTaxExcluded.val((isNaN(newPrice) == true || newPrice < 0) ? 0 : JeproTools.roundPrice(newPrice, 9));
            var attributePriceTaxIncluded = jQuery("#jform_attribute_price_tax_included");
            var finalPrice = jQuery("#jform_final_price");
            var total = JeproTools.roundPrice((parseFloat(attributePriceTaxIncluded.val()) * parseInt(jQuery('#jform_attribute_price_impact').val()) + parseFloat(finalPrice.html())), options.price_display_precision);
            var attributeNewTotalPrice = jQuery("#jform_attribute_new_total_price");
            if (isNaN(total) || total < 0)
                attributeNewTotalPrice.html('0.00');
            else
                attributeNewTotalPrice.html(total);
        }
*/
        function removeTaxes(price){
            var taxes = getTaxes();
            var priceWithOutTaxes = price;
            if(taxes !== undefined) {
                if (taxes.computation_method == 0) {
                    //for(i in taxes.rates) {
                    priceWithOutTaxes /= (1 + taxes.rates[0] / 100);
                    //break;
                    //}
                } else if (taxes.computation_method == 1) {
                    var rate = 0, i;
                    for (i in taxes.rates) {
                        rate += taxes.rates[i];
                    }
                    priceWithOutTaxes /= (1 + rate / 100);
                } else if (taxes.computation_method == 2) {
                    for (i in taxes.rates) {
                        priceWithOutTaxes /= (1 + taxes.rates[i] / 100);
                    }
                }
            }
            return priceWithOutTaxes;
        }

        function getEcoTaxTaxIncluded(){
            return JeproTools.roundPrice(options.eco_tax_tax_excluded * (1 + options.eco_tax_tax_rate), 2);
        }
/*
        function getEcotaxTaxExcluded(){
            return options.eco_tax_tax_excluded;
        }

        function formatPrice(price){
            var fixedToSix = (Math.round(price * 1000000) / 1000000);
            return (Math.round(fixedToSix) == fixedToSix + 0.000001 ? fixedToSix + 0.000001 : fixedToSix);
        }
*/
        function calculatePrice() {
            var priceType = jQuery('#jform_price_type').val();
            if (priceType == 'TE'){
                calculatePriceTaxIncluded();
            }else {
                calculatePriceTaxExcluded();
            }
        }
/*
        function removeRelatedProduct(){
            jQuery('#jform_related_product_name').html(options.no_related_product);
            jQuery('#jform_product_redirected_id').val(0);
            jQuery('#jform_related_product_remove').hide();
            jQuery('#jform_related_product_auto_complete_input').parent().fadeIn();
        }
 */


        function getTaxes(){
            if(options.no_tax){ return options.taxes[0]; }
            var selectedTax = jQuery('#jform_tax_rules_group_id');
            options.tax_id = selectedTax.find(':selected').val();
            return options.taxes[options.tax_id];
        }

        function addTaxes(price){
            var taxes = getTaxes();
            var priceWithTaxes = price;
            var rateIndex = 0;
            if(taxes !== undefined) {
                if (taxes.computation_method === undefined) {
                    taxes.computation_method = 0;
                }
                if (taxes.computation_method === 0) {
                    for (rateIndex in taxes.rates) {
                        priceWithTaxes *= (1 + taxes.rates[rateIndex] / 100);
                        break;
                    }
                } else if (taxes.computation_method === 1) {
                    var rate = 0;
                    for (rateIndex in taxes.rates) {
                        rate += taxes.rates[rateIndex];
                    }
                    priceWithTaxes *= (1 + taxes.rates[rateIndex] / 100);
                } else if (taxes.computation_method === 2) {
                    for (rateIndex in taxes.rates) {
                        priceWithTaxes *= (1 + taxes.rates[rateIndex] / 100);
                    }
                }
            }
            return priceWithTaxes;
        }
/*
        function initializeDeclination() {
            var attributeGroup = jQuery("#jform_attribute_group");
            if(attributeGroup) {
                attributeGroup.on('change', function () {
                    var attributes = jQuery("#jform_attribute");
                    var number = attributeGroup.options.length ? attributeGroup.find(':selected').val() : 0;

                    if(!number){
                        attributes.options.length = 0;
                        attributes.options[0] = new Option('---', 0);
                        return;
                    }

                    attributes.options.length = 0;
                    if(typeof list !== undefined){
                        for(var i = 0; i < list.length; i += 2){
                            attributes.options[i/2] = new Option(list[i+1], list[i]);
                        }
                    }
                    
                });
                
                jQuery("#jform_add_attribute_btn").on('click', function (){ addAttribute(); });
                jQuery("#jform_delete_attribute_btn").on('click', function (){ deleteAttribute(); });
                
                var attributeWholesalePrice = jQuery("#jform_attribute_wholesale_price");
                attributeWholesalePrice.on('keyup', function () {
                    if(JeproTools.isArrowKey(event)){ event.stopPropagation(); return; }
                    attributeWholesalePrice.val(document.getElementById("jform_attribute_wholesale").value.replace(/,/g, '.'));
                });

                jQuery("#jform_attribute_price_impact").on('change', function () {
                    checkImpact();
                    calculateImpactPriceTaxIncluded();
                });

                var attributePrice = jQuery("#jform_attribute_price");
                attributePrice.on('keyup', function () {
                    var attributeRealPriceTaxExcluded = jQuery("#jform_attribute_real_price_tax_excluded");
                    attributeRealPriceTaxExcluded.val(document.getElementById("#jform_attribute_real_price_tax_excluded").value.replace(/,/g, '.'));
                    if(JeproTools.isArrowKey(event)){ event.stopPropagation(); return;  }
                    attributePrice.val(document.getElementById("#jform_attribute_price").value.replace(/,/g, '.'));
                    calculateImpactPriceTaxIncluded();
                });
                
                var attributePriceTaxIncluded = jQuery("#jform_attribute_price_tax_included");
                attributePriceTaxIncluded.on('keyup', function () {
                    if(JeproTools.isArrowKey(event)){ event.stopPropagation(); return;  }
                    attributePriceTaxIncluded.val(document.getElementById("#jform_attribute_price_tax_included").value.replace(/,/g, '.'));
                    calculateImpactPriceTaxExcluded();
                });
                
                jQuery("#jform_attribute_weight_impact").on('change', function () {
                    checkWeightImpact();
                });

                var attributeWeightPrice = jQuery("#jform_attribute_weight_price");
                attributeWeightPrice.on('keyup', function(){
                    if(JeproTools.isArrowKey(event)){ event.stopPropagation(); return;  }
                    attributeWeightPrice.val(document.getElementById("#jform_attribute_weight_price").value.replace(/,/g, '.'));
                });

                jQuery("#jform_attribute_unit_impact").on('change', function(){ checkUnitImpact(); });

                var attributeUnityPrice = jQuery("#jform_attribute_unity_price");
                attributeUnityPrice.on('keyup', function(){
                    if(JeproTools.isArrowKey(event)){ event.stopPropagation(); return;  }
                    attributeUnityPrice.val(document.getElementById("#jform_attribute_unity_price").value.replace(/,/g, '.'));
                });

                var attributeEcotaxPrice = jQuery("#jform_attribute_ecotax_price");
                attributeEcotaxPrice.on('keyup', function(){
                    if(JeproTools.isArrowKey(event)){ event.stopPropagation(); return;  }
                    attributeEcotaxPrice.val(document.getElementById("#jform_attribute_ecotax_price").value.replace(/,/g, '.'));
                });

            }
        }
        
        function populateAttributes() {
            
        }

        function deleteAttribute(){
            jQuery("#jform_product_attribute_list").find(":selected").each(function(){
                delete options.store_used_groups[jQuery(this).attr('groupid')];
                jQuery(this).remove();
            });
        }
        
        function addAttribute() {
            var attributeGroup = jQuery("#jform_attribute_group");
            var selectedAttributeGroup = attributeGroup.find(":selected");
            if (selectedAttributeGroup.val() == 0)
                return jAlert(msg_combination_1);

            var selectedAttribute = jQuery("#jform_attribute").find(":selected");
            if (selectedAttribute.val() == 0)
                return jAlert(msg_combination_2);

            if (selectedAttributeGroup.val() in options.store_used_groups)
                return jAlert(msg_combination_3);

            options.store_used_groups[selectedAttributeGroup.val()] = true;
            jQuery('<option></option>').attr('value', selectedAttribute.val())
                .attr('groupid', selectedAttributeGroup.val())
                .text(selectedAttributeGroup.text() + ' : ' + selectedAttribute.text())
                .appendTo("#jform_product_attribute_list");
        }
        
        function initializeImages() {
            var assoc;
            var originalOrder = false;
            var reOrder = "";

            options.product_images.forEach(function(element){
                imageLine(element.image_id, element.path, element.position, element.icon_checked, element.assoc, element.legend);
            });

            jQuery("#jform_product_image_table").tableDnD({
                dragHandle: 'dragHandle', onDragClass: 'myDragClass',
                onDragStart: function(table, row){
                    originalOrder = jQuery.tableDnD.serialize();
                    reOrder = ':even';
                    if(table.tBodies[0].rows[1] && jQuery("#" + table.tBodies[0].rows[1].id).hasClass('alt_class')){
                        reOrder = ':odd';
                    }
                    jQuery(table).find("#" + row.id).parent('tr').addClass('myDragClass');
                },
                onDrop : function(table, row){
                    if(originalOrder != jQuery.tableDnD.serialize()){
                        var current = jQuery(row).attr("id");
                        var stop = false;
                        var imageUp ="{";
                        jQuery("#jform_images_list").find("tr").each(function(i){
                            jQuery("td_" + jQuery(this).attr("id")).html('<div class="dragGroup" ><div class="positions" > + (i+1) + </div></div>');
                            if(!stop || (i+1) == 2){
                                imageUp += '"' + jQuery(this).attr("id") + '" : ' + (i+1) + ',';
                            }
                        });
                        imageUp = imageUp.slice(0, -1);
                        imageUp += "}";
                        updateImagePosition(imageUp);
                    }
                }
            });
        }

        function imageLine(id, path, position, cover, shops, legend){
            var imageType = jQuery("#jform_image_type").html();
            imageType = imageType.replace(/image_id/g, id);
            imageType = imageType.replace(/(\/) ?[a-z]{0,2}-default/g, function(jQuery0, jQuery1){ return jQuery1 ? jQuery1 + path : jQuery0; });
            imageType = imageType.replace(/image_path/g, path);
            imageType = imageType.replace(/\.jpg"\s/g,'.jpg?time=' + new Date().getTime() + '" ');
            imageType = imageType.replace(/image_position/g, position);
            imageType = imageType.replace(/legend/g, legend);
            imageType = imageType.replace(/icon-check-empty/g, cover);
            imageType = imageType.replace(/<tbody>/gi, "");
            imageType = imageType.replace(/<\/tbody>/gi, "");

            if(shops != false){
                jQuery.each(shops, function(key, value){
                    if(value == 1){
                        imageType = imageType.replace('id="' + key + ' ' + id + '"', 'id="' + key + '' + id + '" checked=checked');
                    }
                });
            }
            jQuery("#jform_images_list").append(imageType);
        }

        /**
         *
         * /
        function updateImagePosition(json){
            jQuery.ajax();
            JeproTools.doAdminAjax({
                "task" : "updateImagePosition", "json" : json,
                "ajax": 1
            });
        } */


    }
})(jQuery);