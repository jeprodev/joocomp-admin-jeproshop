/**
 * Created by jeproQxT on 26/07/2017.
 */
var JeproTools;
JeproTools = {
    options: {
        highDPI: undefined,
        responsiveFlag: false,
        quickView: undefined,
        pageName: undefined,
        request: undefined,

        round_mode : 2,
        price_display_precision : 2
    },
    isArrowKey: function (keyEvent) {
        var uniCode = keyEvent.keyCode ? keyEvent.keyCode : keyEvent.charCode;
        return !!(uniCode >= 37 && uniCode <= 40);
    },
    renderMessage : function(message){

    },
    formatCurrency : function(price, currencyFormat, currencySign, currencyBlank){
        // if you modified this function, don't forget to modify the PHP function displayPrice (in the Tools.php class)
        var blank = '';
        price = parseFloat(price.toFixed(10));
        price = JeproTools.roundPrice(price, JeproTools.options.price_display_precision);
        if (currencyBlank > 0) {
            blank = ' ';
        }

        if (currencyFormat == 1) {
            return currencySign + blank + JeproTools.formatNumber(price, JeproTools.options.price_display_precision, ',', '.');
        }
        if (currencyFormat == 2) {
            return (JeproTools.formatNumber(price, JeproTools.options.price_display_precision, ' ', ',') + blank + currencySign);
        }

        if (currencyFormat == 3) {
            return (currencySign + blank + JeproTools.formatNumber(price, JeproTools.options.price_display_precision, '.', ','));
        }

        if (currencyFormat == 4) {
            return (JeproTools.formatNumber(price, JeproTools.options.price_display_precision, ',', '.') + blank + currencySign);
        }

        if (currencyFormat == 5) {
            return (currencySign + blank + JeproTools.formatNumber(price, JeproTools.options.price_display_precision, '\'', '.'));
        }
        return price;
    },
    formatNumber : function(value, numberOfDecimal, thousandSeparator, virgule){
        value = value.toFixed(numberOfDecimal);
        var valString = value+'';
        var tmp = valString.split('.');
        var absValString = (tmp.length === 2) ? tmp[0] : valString;
        var decimalString = ('0.' + (tmp.length === 2 ? tmp[1] : 0)).substr(2);
        var nb = absValString.length;

        for (var i = 1 ; i < 4; i++) {
            if (value >= Math.pow(10, (3 * i))) {
                absValString = absValString.substring(0, nb - (3 * i)) + thousandSeparator + absValString.substring(nb - (3 * i));
            }
        }
        if (parseInt(numberOfDecimal) === 0) {
            return absValString;
        }
        return absValString + virgule + (decimalString > 0 ? decimalString : '00');
    },
    roundPrice : function(value, places){
        if (typeof(JeproTools.options.round_mode) === 'undefined') {
            JeproTools.options.round_mode = 2;
        }
        if (typeof(places) === 'undefined'){ places = 2; }

        var method = JeproTools.options.round_mode;

        if (method === 0) {
            return JeproTools.ceilValue(value, places);
        }else if (method === 1) {
            return JeproTools.floorValue(value, places);
        }else if (method === 2) {
            return JeproTools.roundHalfUp(value, places);
        }else if (method == 3 || method == 4 || method == 5){
            // From PHP Math.c
            var precisionPlaces = 14 - Math.floor(JeproTools.roundLog10(Math.abs(value)));
            var f1 = Math.pow(10, Math.abs(places));

            if (precisionPlaces > places && precisionPlaces - places < 15){
                var f2 = Math.pow(10, Math.abs(precisionPlaces));
                var tmpValue;
                if (precisionPlaces >= 0) {
                    tmpValue = value * f2;
                }else {
                    tmpValue = value / f2;
                }
                tmpValue = JeproTools.roundHelper(tmpValue, JeproTools.options.round_mode);

                /* now correctly move the decimal point */
                f2 = Math.pow(10, Math.abs(places - precisionPlaces));
                /* because places < precision_places */
                tmpValue /= f2;
            }else{
                /* adjust the value */
                if (places >= 0) {
                    tmpValue = value * f1;
                }else {
                    tmpValue = value / f1;
                }
                if (Math.abs(tmpValue) >= 1e15) {
                    return value;
                }
            }

            tmpValue = JeproTools.roundHelper(tmpValue, JeproTools.options.round_mode);
            if (places > 0) {
                tmpValue = tmpValue / f1;
            }else {
                tmpValue = tmpValue * f1;
            }

            return tmpValue;
        }
    },
    ceilValue : function(value, precision){
        if (typeof(precision) === 'undefined') {
            precision = 0;
        }
        var precisionFactor = precision === 0 ? 1 : Math.pow(10, precision);
        var tmp = value * precisionFactor;
        var tmp2 = tmp.toString();
        if (tmp2[tmp2.length - 1] === 0) {
            return value;
        }
        return Math.ceil(value * precisionFactor) / precisionFactor;
    },

    floorValue : function(value, precision){
        if (typeof(precision) === 'undefined') {
            precision = 0;
        }
        var precisionFactor = precision === 0 ? 1 : Math.pow(10, precision);
        var tmp = value * precisionFactor;
        var tmp2 = tmp.toString();
        if (tmp2[tmp2.length - 1] === 0) {
            return value;
        }
        return Math.floor(value * precisionFactor) / precisionFactor;
    },

    roundHalfUp : function(value, precision){
        var mul = Math.pow(10, precision);
        var val = value * mul;

        var nextDigit = Math.floor(val * 10) - 10 * Math.floor(val);
        if (nextDigit >= 5)
            val = Math.ceil(val);
        else
            val = Math.floor(val);

        return val / mul;
    },

    roundHelper : function(value, mode){
        // From PHP Math.c
        var tmpValue;
        if (value >= 0.0){
            tmpValue = Math.floor(value + 0.5);
            if ((mode == 3 && value == (-0.5 + tmpValue)) ||
                (mode == 4 && value == (0.5 + 2 * Math.floor(tmpValue / 2.0))) ||
                (mode == 5 && value == (0.5 + 2 * Math.floor(tmpValue / 2.0) - 1.0)))
                tmpValue -= 1.0;
        }else{
            tmpValue = Math.ceil(value - 0.5);
            if ((mode == 3 && value == (0.5 + tmpValue)) ||
                (mode == 4 && value == (-0.5 + 2 * Math.ceil(tmpValue / 2.0))) ||
                (mode == 5 && value == (-0.5 + 2 * Math.ceil(tmpValue / 2.0) + 1.0)))
                tmpValue += 1.0;
        }

        return tmpValue;
    },

    roundLog10 : function(value){
        return Math.log(value) / Math.LN10;
    }

};