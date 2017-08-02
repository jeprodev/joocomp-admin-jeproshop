/**
 * Created by jeproQxT on 01/08/2017.
 */
(function ($) {
    $.fn.JeproAddress = function (opts) {
        var defaults = {
            address_id : 0,
            customer : {
                customer_id : 0,
                email : "",
                token : ""
            },
            country : {
                zone_id : 0,
                country_id : 0,
                token : ''
            }
        };
        
        var options = $.extend(defaults, opts);
        
        var addressObj = this;
        
        return addressObj.each(function() {
            initialize();
        });
        
        function initialize() {
            updateCustomerInfo();
            updateCountriesOnZoneChange();
        }

        function updateCountriesOnZoneChange(){
            var zoneSelector = jQuery("#jform_zone");
            zoneSelector.on('change', function(){
                var urlTarget = "index.php?option=com_jeproshop&view=country&task=search&tab=countries&use_ajax=1";
                var countryData = {};
                countryData.zone_id = parseInt(zoneSelector.find(':selected').val());

                jQuery.ajax({
                    type : "POST",
                    url : urlTarget,
                    data : countryData,
                    dataType : "json",
                    async : true,
                    success : function (msg) {
                        if (msg) {
                            var countriesSelector = jQuery("#jform_country");
                            countriesSelector.children().each(function(elt){
                                countriesSelector.find("option[value='" + elt + "']").remove();
                                countriesSelector.find("option[value='" + elt + "']").text("");
                                //console.log(elt);
                            });
                            /*/jQuery("#jform_country option").remove();
                            countriesSelector.html("");
                            countriesSelector.text("");
                            console.log(countriesSelector.children());
                            /*msg.countries.forEach(function (elt){
                                countriesSelector.append(jQuery("<option></option>").attr("value", elt.country_id).text(elt.name));
                            }); */
                            c//onsole.log('evolutin');
                        }
                    },
                    error : function (msg) {
                        console.log(msg);
                    }
                })
            });
        }
        
        function updateCustomerInfo(){
            if(options.customer.customer_id === undefined || options.customer.customer_id === 0) {
                var customerEmailWrapper = jQuery("#jform_email");
                customerEmailWrapper.live('blur', function(e){
                    var customerEmail = customerEmailWrapper.val();
                    if(customerEmail.length > 5){
                        var customerData = {};
                        customerData.email = customerEmail;
                        customerData.token = options.customer.token;

                        var urlTarget = 'index.php?option=com_jeproshop&view=address&use_ajax=1&task=search&tab=names';

                        jQuery.ajax({
                            type : "POST",
                            url : urlTarget,
                            data : customerData,
                            dataType : "json",
                            async : true,
                            success : function (msg) {
                                if(msg){
                                    var info = msg.info.replace("\\'", "'").split('_');
                                    jQuery("#jform_firstname").val(info[0]);
                                    jQuery("#jform_lastname").val(info[1]);
                                    jQuery("#jform_company").val(info[2]);
                                    customerEmailWrapper.val(info[3]);
                                }
                            },
                            error : function (msg) {
                                console.log(msg);
                            }
                        });
                    }
                });
                
            }
        }



        
    }
})(jQuery);