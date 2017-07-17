/**
 * Created by jeproQxT on 13/07/2017.
 */

Date.prototype.addDays = function (value) {
    this.setDate(this.getDate() + value); return this;
};

Date.prototype.addMonths = function (value) {
    var date = this.getDate();
    this.setMonth(this.getMonth() + value);
    if(this.getDate() < date){ this.setDate(0); }
    return this;
};

Date.prototype.addWeeks = function(value){ this.addDays(value * 7); return this; };

Date.prototype.addYears = function(value){
    var month = this.getMonth();
    this.setFullYear(this.getFullYear() + value);
    if(month < this.getMonth()){ this.setDate(0); }
    return this;
};

Date.parseDate = function(date, format){
    if (format === undefined)
        format = 'Y-m-d';

    var formatSeparator = format.match(/[.\/\-\s].*?/);
    var formatParts     = format.split(/\W+/);
    var parts           = date.split(formatSeparator);
    var parsedDate      = new Date();

    if (parts.length === formatParts.length) {
        parsedDate.setHours(0);
        parsedDate.setMinutes(0);
        parsedDate.setSeconds(0);
        parsedDate.setMilliseconds(0);

        for (var i=0; i<= formatParts.length; i++) {
            switch(formatParts[i]) {
                case 'dd':
                case 'd':
                case 'j':
                    parsedDate.setDate(parseInt(parts[i], 10)||1);
                    break;

                case 'mm':
                case 'm':
                    parsedDate.setMonth((parseInt(parts[i], 10)||1) - 1);
                    break;

                case 'yy':
                case 'y':
                    parsedDate.setFullYear(2000 + (parseInt(parts[i], 10)||1));
                    break;

                case 'yyyy':
                case 'Y':
                    parsedDate.setFullYear(parseInt(parts[i], 10)||1);
                    break;
            }
        }
    }
    return parsedDate;
};

Date.prototype.subDays = function(value) {
    this.setDate(this.getDate() - value);

    return this;
};

Date.prototype.subMonths = function(value) {
    var date = this.getDate();
    this.setMonth(this.getMonth() - value);

    if (this.getDate() < date) {
        this.setDate(0);
    }

    return this;
};

Date.prototype.subWeeks = function(value) {
    this.subDays(value * 7); return this;
};

Date.prototype.subYears = function(value) {
    var month = this.getMonth();
    this.setFullYear(this.getFullYear() - value);

    if (month < this.getMonth()) {
        this.setDate(0);
    }

    return this;
};

Date.prototype.format = function(format) {
    if (format === undefined)
        return this.toString();

    var formatSeparator = format.match(/[.\/\-\s].*?/);
    var formatParts     = format.split(/\W+/);
    var result          = '';

    for (var i=0; i<=formatParts.length; i++) {
        switch(formatParts[i]) {
            case 'd':
            case 'j':
                result += this.getDate() + formatSeparator;
                break;

            case 'dd':
                result += (this.getDate() < 10 ? '0' : '')+this.getDate() + formatSeparator;
                break;

            case 'm':
                result += (this.getMonth() + 1) + formatSeparator;
                break;

            case 'mm':
                result += (this.getMonth() < 9 ? '0' : '')+(this.getMonth() + 1) + formatSeparator;
                break;

            case 'yy':
            case 'y':
                result += this.getFullYear() + formatSeparator;
                break;

            case 'yyyy':
            case 'Y':
                result += this.getFullYear() + formatSeparator;
                break;
        }
    }

    return result.slice(0, -1);
};

(function($){
    $.fn.JeproCalendar = function (opts) {
        var options;
        var defaults = {
            wrapper : "date-picker",
            start_date_class : 'start-date-picker',
            end_date_class : 'end-date-picker',
            start_date : 'start-date',
            end_date : 'end-date',
            date_input : "date-input",
            translated_dates : []
        };

        options = $.fn.extend(defaults, opts);
        var calendarObj = this;

        return calendarObj.each(function () {
            initializeCalendar();
        });

        function initializeCalendar(){
            var datePickerStart = $('.' + options.start_date_class);
            var datePickerEnd = $('.' + options.end_date_class);
            var startDate = $("#" + options.start_date), endDate = $("#" + options.end_date);
            var dateInput = $('.' + options.date_input);
            datePickerStart.JeproCalendarDatePicker({
                dates : options.translated_dates,
                week_start : 1,
                start : startDate.val(),
                end: endDate.val()
            });

            datePickerStart.on('changeDate', function(ev){
                if(ev.date.valueOf() >= datePickerEnd.date.valueOf()){
                    datePickerEnd.setValue(ev.date.setMonth(ev.date.getMonth()+1));
                }
            }); //.data('daterangepicker');

            datePickerEnd.JeproCalendarDatePicker({
                dates: options.translated_dates,
                week_start : 1,
                start:  startDate.val(),
                end: endDate.val()
            });

            datePickerEnd.on('changeDate', function(ev){
                if (ev.date.valueOf() <= datePickerStart.date.valueOf()){
                    datePickerStart.setValue(ev.date.setMonth(ev.date.getMonth()-1));
                }
            }); //.data('daterangepicker');

            //Set first date picker to month -1 if same month
            var parsedStartDate = Date.parseDate(startDate.val(), startDate.data('date-format'));
            var parsedEndDate = Date.parseDate(endDate.val(), endDate.data('date-format'));

            if (parsedStartDate.getFullYear() == parsedEndDate.getFullYear() && parsedStartDate.getMonth() == parsedEndDate.getMonth())
                setValue(datePickerStart, parsedStartDate.subMonths(1));

            //Events binding
            startDate.focus(function(){
                setCompare(datePickerStart, false);
                setCompare(datePickerEnd, false);
                dateInput.removeClass("input-selected");
                $(this).addClass("input-selected");
            });

            endDate.focus(function() {
                setCompare(datePickerStart, false);
                setCompare(datePickerEnd, false);
                dateInput.removeClass("input-selected");
                $(this).addClass("input-selected");
            });

            var dateStartCompare = $("#" + options.start_date + "-compare");
            var compareOptions = $('#compare-options');
            dateStartCompare.focus(function() {
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
                compareOptions.val(3);
                dateInput.removeClass("input-selected");
                $(this).addClass("input-selected");
            });

            var dateEndCompare = $('#' + options.end_date + "-compare");
            dateEndCompare.focus(function() {
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
                compareOptions.val(3);
                dateInput.removeClass("input-selected");
                $(this).addClass("input-selected");
            });

            var datePickerCancelBtn = $('#date-picker-cancel');
            var dateWrapper = $("#" + options.wrapper);
            datePickerCancelBtn.click(function() {
                dateWrapper.addClass('hidden');
            });

            dateWrapper.show(function() {
                startDate.focus();
                startDate.trigger('change');
            });

            var datePickerCompare = $('#date-picker-compare');
            var formDateBodyCompare = $('#form-date-body-compare');
            datePickerCompare.click(function() {
                if ($(this).attr("checked")) {
                    compareOptions.trigger('change');
                    formDateBodyCompare.show();
                    compareOptions.prop('disabled', false);
                } else {
                    setStartCompare(datePickerStart, null);
                    setEndCompare(datePickerStart, null);
                    setStartCompare(datePickerEnd, null);
                    setEndCompare(datePickerEnd, null);
                    formDateBodyCompare.hide();
                    compareOptions.prop('disabled', true);
                    startDate.focus();
                }
            });

            compareOptions.change(function() {
                if (this.value == 1)
                    setPreviousPeriod();

                if (this.value == 2)
                    setPreviousYear();

                var dateStartCompare = $("#date-start-compare");
                var dateEndCompare = $("#date-end-compare");

                setStartCompare(datePickerStart, dateStartCompare.val());
                setEndCompare(datePickerStart, dateEndCompare.val());
                setStartCompare(datePickerEnd, dateStartCompare.val());
                setEndCompare(datePickerEnd, dateEndCompare.val());
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);

                if (this.value == 3)
                    dateStartCompare.focus();
            });

            if ($('#date-picker-compare').attr("checked")){
                if (dateStartCompare.val().replace(/^\s+|\s+$/g, '').length == 0)
                    compareOptions.trigger('change');

                setStartCompare(datePickerStart, dateStartCompare.val());
                setEndCompare(datePickerStart, dateEndCompare.val());
                setStartCompare(datePickerEnd, dateStartCompare.val());
                setEndCompare(datePickerEnd, dateEndCompare.val());
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
            }

            $('#datepickerExpand').on('click',function() {
                if ($('#datepicker').hasClass('hide')) {
                    $('#datepicker').removeClass('hide');
                    startDate.focus();
                }
                else
                    $('#datepicker').addClass('hide');
            });

            $('.submitDateDay').on('click',function(e){
                e.preventDefault();
                setDayPeriod();
            });
            $('.submitDateMonth').on('click',function(e){
                e.preventDefault();
                setMonthPeriod()
            });
            $('.submitDateYear').on('click',function(e){
                e.preventDefault();
                setYearPeriod();
            });
            $('.submitDateDayPrev').on('click',function(e){
                e.preventDefault();
                setPreviousDayPeriod();
            });
            $('.submitDateMonthPrev').on('click',function(e){
                e.preventDefault();
                setPreviousMonthPeriod();
            });

            $('.submitDateYearPrev').on('click',function(e){
                e.preventDefault();
                setPreviousYearPeriod();
            });
        }


        function setPreviousYearPeriod(){
            var date = new Date();
            date = new Date(date.getFullYear(), 11, 31);
            date = date.subYears(1);
            $("#date-end").val(format(date, $("#date-end").data('date-format')));
            date = new Date(date.getFullYear(), 0, 1);
            $("#date-start").val(format(date, $("#date-start").data('date-format')));
            $('#date-start').trigger('change');

            updatePickerFromInput();
            $('#date-picker-from-info').html($("#date-start").val());
            $('#date-picker-to-info').html($("#date-end").val());
            $('#preselectDateRange').val('prev-year');
            $('button[name="submitDateRange"]').click();
        }

        function setPreviousPeriod(){
            var startDate = $("#" + options.start_date);
            var endDate = $("#" + options.end_date);
            var parsedStartDate = Date.parseDate(startDate.val(), startDate.data('date-format')).subDays(1);
            var parsedEndDate = Date.parseDate(endDate.val(), endDate.data('date-format')).subDays(1);

            var diff = parsedEndDate - parsedStartDate;
            var startDateCompare = new Date(parsedStartDate - diff);
            var dateStartCompare = $("#date-start-compare");
            var dateEndCompare = $("#date-end-compare");


            dateEndCompare.val(parsedStartDate.format(dateEndCompare.data('date-format')));
            dateStartCompare.val(startDateCompare.format(dateStartCompare.data('date-format')));
        }

        function setPreviousYear() {
            var dateStart = $("#" + options.start_date);
            var startDate = Date.parseDate(dateStart.val(), dateStart.data('date-format')).subYears(1);
            var dateEnd = $("#" + options.end_date);
            var endDate = Date.parseDate(dateEnd.val(), dateEnd.data('date-format')).subYears(1);
            $("#date-start-compare").val(startDate.format(dateStart.data('date-format')));
            $("#date-end-compare").val(endDate.format(dateStart.data('date-format')));
        }

        function updatePickerFromInput() {
            var datePickerStart = $('.' + options.start_date_class);
            var datePickerEnd = $('.' + options.end_date_class);
            datePickerStart.setStart($("#date-start").val());
            datePickerStart.setEnd($("#date-end").val());
            datePickerStart.update();
            datePickerEnd.setStart($("#date-start").val());
            datePickerEnd.setEnd($("#date-end").val());
            datePickerEnd.update();

            $('#date-start').trigger('change');

            if ($('#datepicker-compare').attr("checked")) {
                if ($('#compare-options').val() == 1)
                    setPreviousPeriod();

                if ($('#compare-options').val() == 2)
                    setPreviousYear();

                setStartCompare(datePickerStart, $("#date-start-compare").val());
                setEndCompare(datePickerStart, $("#date-end-compare").val());
                setStartCompare(datePickerEnd, $("#date-start-compare").val());
                setEndCompare(datePickerEnd, $("#date-end-compare").val());
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
            }
        }

        function setDayPeriod() {
            var date = new Date();
            $("#date-start").val(format(date, $("#date-start").data('date-format')));
            $("#date-end").val(format(date, $("#date-end").data('date-format')));
            $('#date-start').trigger('change');

            updatePickerFromInput();
            $('#datepicker-from-info').html($("#date-start").val());
            $('#datepicker-to-info').html($("#date-end").val());
            $('#preselectDateRange').val('day');
            $('button[name="submitDateRange"]').click();
        }

        function setPreviousDayPeriod() {
            var date = new Date();
            date = date.subDays(1);
            $("#date-start").val(date.format($("#date-start").data('date-format')));
            $("#date-end").val(date.format($("#date-end").data('date-format')));
            $('#date-start').trigger('change');

            updatePickerFromInput();
            $('#datepicker-from-info').html($("#date-start").val());
            $('#datepicker-to-info').html($("#date-end").val());
            $('#preselectDateRange').val('prev-day');
            $('button[name="submitDateRange"]').click();
        }

        function setMonthPeriod() {
            date = new Date();
            $("#date-end").val(date.format($("#date-end").data('date-format')));
            date = new Date(date.setDate(1));
            $("#date-start").val(date.format($("#date-start").data('date-format')));
            $('#date-start').trigger('change');

            updatePickerFromInput();
            $('#datepicker-from-info').html($("#date-start").val());
            $('#datepicker-to-info').html($("#date-end").val());
            $('#preselectDateRange').val('month');
            $('button[name="submitDateRange"]').click();
        }

        function setPreviousMonthPeriod() {
            date = new Date();
            date = new Date(date.getFullYear(), date.getMonth(), 0);
            $("#date-end").val(date.format($("#date-end").data('date-format')));
            date = new Date(date.setDate(1));
            $("#date-start").val(date.format($("#date-start").data('date-format')));
            $('#date-start').trigger('change');

            updatePickerFromInput();
            $('#date-picker-from-info').html($("#date-start").val());
            $('#datepicker-to-info').html($("#date-end").val());
            $('#preselectDateRange').val('prev-month');
            $('button[name="submitDateRange"]').click();
        }

        function setYearPeriod() {
            date = new Date();
            $("#date-end").val(date.format($("#date-end").data('date-format')));
            date = new Date(date.getFullYear(), 0, 1);
            $("#date-start").val(date.format($("#date-start").data('date-format')));
            $('#date-start').trigger('change');

            updatePickerFromInput();
            $('#datepicker-from-info').html($("#date-start").val());
            $('#datepicker-to-info').html($("#date-end").val());
            $('#preselectDateRange').val('year');
            $('button[name="submitDateRange"]').click();
        }

        function setCompare(elt, value){
            elt.data("compare", value);
            updateRange(elt);
        }
        
        function updateRange(elt){
            var calendarWrapper = $("#" + options.wrapper);
            var end = elt.data("end"), start = elt.data("start");
            var endCompare = elt.data("end-compare"), startCompare = elt.data("start-compare");

            calendarWrapper.find(".day").each(function () {
                var dateValue = parseInt($(this).data('val'), 10);

                if (end && start) {
                    if(dateValue > start && dateValue < end) {
                        $(this).addClass("range");
                    }
                    if(dateValue === start) {
                        $(this).addClass("start-selected");
                    }
                    if(dateValue === end) {
                        $(this).addClass("end-selected");
                    }
                }

                if (endCompare && startCompare) {
                    $(this).removeClass("range-compare").removeClass("start-selected-compare").removeClass("end-selected-compare");

                    if(dateValue > startCompare && dateValue < endCompare) {
                        $(this).addClass("range-compare");
                    }
                    if(dateValue === startCompare) {
                        $(this).addClass("start-selected-compare");
                    }
                    if(dateValue === endCompare) {
                        $(this).addClass("end-selected-compare");
                    }
                } else {
                    $(this).removeClass("range-compare").removeClass("start-selected-compare").removeClass("end-selected-compare");
                }
            });
        }

        function setValue(elt, newDate){
            if(typeof newDate === 'string'){
                elt.date = DPGlobal.parseDate(newDate, elt.data("format"));
            }else{
                elt.date = new Date(newDate);
            }
            setFormattedDate(elt);
            elt.data("view-date", new Date(elt.date.getFullYear(), elt.date.getMonth(), 1, 0, 0, 0, 0));
            elt.fill();
        }

        function setFormattedDate(elt){
            var formatted = DPGlobal.formatDate(elt.date, elt.data("format"));
            elt.data("date", formatted);
        }

        function setStartCompare(elt, value){
            if(value !== undefined) {
                if (value === null) {
                    elt.data("start-compare", value);
                }
                else if (value.constructor === String) {
                    elt.data("start-compare", DPGlobal.parseDate(date, DPGlobal.parseFormat('Y-m-d')).getTime());
                }
                else if (date.constructor === Number) {
                    elt.data("start-compare", value);
                }
                else if (date.constructor === Date) {
                    elt.data("start-compare", value.getTime());
                }
            }
        }

        function setEndCompare(elt, value){
            if(value !== undefined){
                if (value === null){
                    elt.data("start-compare", value);
                } else if (value.constructor === String){
                    elt.data("start-compare", DPGlobal.parseDate(value, DPGlobal.parseFormat('Y-m-d')).getTime());
                } else if (value.constructor === Number){
                    elt.data("start-compare", value);
                } else if (value.constructor === Date){
                    elt.data("start-compare", value.getTime());
                }
            }
        }

        function subDays(elt, value) {
            setDate(elt, (getDate(elt) - value))
        }

        function subYears(elt, value){
            var month = getMonth(elt);
            setFullYear(elt, getFullYear(elt) - value);

            if (month < getMonth(elt)) {
                this.setDate(0);
            }
        }

        function setDate(elt, value){}

        function getDate(elt){}

        function getMonth(elt){}

        function getFullYear(elt){ return ""; }

        function setFullYear(elt, value){}
    }
})(jQuery);