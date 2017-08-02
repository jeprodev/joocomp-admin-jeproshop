/**
 * Created by jeproQxT on 01/08/2017.
 */
(function ($) {
    $.fn.JeproOrder = function (opts) {
        var defaults = {};
        
        var options =  jQuery.extend(defaults, opts);
        
        var orderObject = this;
        
        return orderObject.each(function () {
            initializeOrder();
        });
        
        function initializeOrder() {
            
        }
    }
    
})(jQuery);