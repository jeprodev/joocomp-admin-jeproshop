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
            start_date_id : 'start-date',
            start_date : undefined,
            end_date_id : 'end-date',
            end_date : undefined,
            date_input : "date-input",
            translated_dates : [],
            compare : undefined
        };

        options = jQuery.fn.extend(defaults, opts);
        var calendarObj = this;
        var datePickerStart, datePickerEnd;

        return calendarObj.each(function () {
            initializeCalendar();
        });

        function initializeCalendar(){
            datePickerStart = jQuery('.' + options.start_date_class);
            datePickerEnd = jQuery('.' + options.end_date_class);
            var startDate = jQuery("#" + options.start_date_id), endDate = jQuery("#" + options.end_date_id);
            var dateInput = jQuery('.' + options.date_input);
            datePickerStart.each(function(index, elt){
                elt = jQuery(elt);
                initializeCalendarDatePicker(elt);
                elt.on('changeDate', function(evt){
                    if(evt.date.val() >= datePickerEnd.date.val()){
                        setDatePickerValue(datePickerEnd, evt.date.setMonth(evt.getMonth() + 1));
                    }
                }).data("date-range-picker");
            });

            datePickerEnd.each(function(index, elt){
                elt = jQuery(elt);
                initializeCalendarDatePicker(elt);
                elt.on('changeDate', function(ev){
                    if (ev.date.valueOf() <= datePickerStart.date.valueOf()){
                        setDatePickerValue(datePickerStart, ev.date.setMonth(ev.date.getMonth()-1));
                    }
                }).data("date-range-picker");
            });

            //Set first date picker to month -1 if same month
            var parsedStartDate = Date.parseDate(startDate.val(), startDate.data('date-format'));
            var parsedEndDate = Date.parseDate(endDate.val(), endDate.data('date-format'));

            if (parsedStartDate.getFullYear() == parsedEndDate.getFullYear() && parsedStartDate.getMonth() == parsedEndDate.getMonth()) {
                setDatePickerValue(datePickerStart, parsedStartDate.subMonths(1));
            }

            //Events binding
            startDate.focus(function(){
                setCompare(datePickerStart, false);
                setCompare(datePickerEnd, false);
                dateInput.removeClass("input-selected");
                jQuery(this).addClass("input-selected");
            });

            endDate.focus(function() {
                setCompare(datePickerStart, false);
                setCompare(datePickerEnd, false);
                dateInput.removeClass("input-selected");
                jQuery(this).addClass("input-selected");
            });

            var dateStartCompare = jQuery("#" + options.start_date_id + "_compare");
            var compareOptions = jQuery('#jeproshop_compare_options');
            dateStartCompare.focus(function() {
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
                compareOptions.val(3);
                dateInput.removeClass("input-selected");
                jQuery(this).addClass("input-selected");
            });

            var dateEndCompare = jQuery('#' + options.end_date_id + "_compare");
            dateEndCompare.focus(function() {
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
                compareOptions.val(3);
                dateInput.removeClass("input-selected");
                jQuery(this).addClass("input-selected");
            });

            var datePickerCancelBtn = jQuery('#jeproshop_date_picker_cancel');
            var dateWrapper = jQuery("#" + options.wrapper);
            datePickerCancelBtn.click(function() {
                dateWrapper.addClass('hidden');
            });

            dateWrapper.show(function() {
                startDate.focus();
                startDate.trigger('change');
            });
 
            var datePickerCompare = jQuery('#jeproshop_date_picker_compare');
            var formDateBodyCompare = jQuery('#form_date_body_compare');
            datePickerCompare.on('click', function(evt) {
                var localCompareOptions = jQuery('#jeproshop_compare_options');
                if (datePickerCompare.attr("checked")) {
                    localCompareOptions.trigger('change');
                    formDateBodyCompare.show();
                    localCompareOptions.prop('disabled', false);
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

            compareOptions.on('change', function() {
                if (this.value == 1)
                    setPreviousPeriod();

                if (this.value == 2)
                    setPreviousYear();

                var dateStartCompare = jQuery("#jeproshop_date_start_compare");
                var dateEndCompare = jQuery("#jeproshop_date_end_compare");

                setStartCompare(datePickerStart, dateStartCompare.val());
                setEndCompare(datePickerStart, dateEndCompare.val());
                setStartCompare(datePickerEnd, dateStartCompare.val());
                setEndCompare(datePickerEnd, dateEndCompare.val());
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);

                if (this.value == 3)
                    dateStartCompare.focus();
            });

            if (datePickerCompare.attr("checked")){
                if (dateStartCompare.val().replace(/^\s+|\s+$/g, '').length == 0) {
                    compareOptions.trigger('change');
                }

                setStartCompare(datePickerStart, dateStartCompare.val());
                setEndCompare(datePickerStart, dateEndCompare.val());
                setStartCompare(datePickerEnd, dateStartCompare.val());
                setEndCompare(datePickerEnd, dateEndCompare.val());
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
            }

            jQuery('#jeproshop_date_picker_expand').on('click',function() {
                var datePickerWrapper = jQuery('#jeproshop_date_picker');
                if (datePickerWrapper.hasClass('hidden')) {
                    datePickerWrapper.removeClass('hidden');
                    startDate.focus();
                } else {
                    datePickerWrapper.addClass('hidden');
                }
            });

            jQuery('#jeproshop_submit_date_day').on('click',function(evt){
                evt.preventDefault();
                evt.stopPropagation();
                setDayPeriod();
            });
            jQuery('#jeproshop_submit_date_month').on('click',function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setMonthPeriod()
            });

            jQuery('#jeproshop_submit_date_year').on('click',function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setYearPeriod();
            });
            jQuery('#jeproshop_submit_date_previous_day').on('click',function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setPreviousDayPeriod();
            });
            jQuery('#jeproshop_submit_date_previous_month').on('click',function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setPreviousMonthPeriod();
            });

            jQuery('#jeproshop_submit_date_previous_year').on('click',function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setPreviousYearPeriod();
            });

            jQuery('button[name="submit_date_range"]').on('click', function(evt){
                evt.stopPropagation();
                evt.preventDefault();
            });
        }

        function setPreviousYearPeriod(){
            var date = new Date();
            date = new Date(date.getFullYear(), 11, 31);
            date = date.subYears(1);
            var endDate = jQuery('#jeproshop_date_end');
            endDate.val(date.format(endDate.data('date-format')));
            date = new Date(date.getFullYear(), 0, 1);
            var startDate = jQuery('#jeproshop_date_start');
            startDate.val(date.format(startDate.data('date-format')));
            startDate.trigger('change');

            updatePickerFromInput();
            jQuery('#jeproshop_date_picker_from_info').html(startDate.val());
            jQuery('#jeproshop_date_picker_to_info').html(endDate.val());
            jQuery('#jeproshop_preselect_date_range').val('previous-year');
            switchActiveButton('previous-year');
            jQuery('button[name="submit_date_range"]').click();
        }

        function setPreviousPeriod(){
            var startDate = jQuery("#" + options.start_date_id);
            var endDate = jQuery("#" + options.end_date_id);
            var parsedStartDate = Date.parseDate(startDate.val(), startDate.data('date-format')).subDays(1);
            var parsedEndDate = Date.parseDate(endDate.val(), endDate.data('date-format')).subDays(1);

            var diff = parsedEndDate - parsedStartDate;
            var startDateCompare = new Date(parsedStartDate - diff);
            var dateStartCompare = jQuery("#jeproshop_date_start_compare");
            var dateEndCompare = jQuery("#jeproshop_date_end_compare");

            dateEndCompare.val(parsedStartDate.format(dateEndCompare.data('date-format')));
            dateStartCompare.val(startDateCompare.format(dateStartCompare.data('date-format')));
        }

        function setPreviousYear() {
            var dateStart = jQuery("#" + options.start_date_id);
            var startDate = Date.parseDate(dateStart.val(), dateStart.data('date-format')).subYears(1);
            var dateEnd = jQuery("#" + options.end_date_id);
            var endDate = Date.parseDate(dateEnd.val(), dateEnd.data('date-format')).subYears(1);
            jQuery("#jeproshop_date_start_compare").val(startDate.format(dateStart.data('date-format')));
            jQuery("#jeproshop_date_end_compare").val(endDate.format(dateStart.data('date-format')));
        }

        function updatePickerFromInput() {
            var startDate = jQuery("#jeproshop_date_start");
            var endData = jQuery('#jeproshop_date_end');
            setRangeStartDate(datePickerStart, startDate.val());
            setRangeEndDate(datePickerStart, endData.val());
            updateDatePicker(datePickerStart);
            setRangeStartDate(datePickerEnd, startDate.val());
            setRangeEndDate(datePickerEnd, endData.val());
            updateDatePicker(datePickerEnd);

            startDate.trigger('change');

            if (jQuery('#jeproshop_date_picker_compare').attr("checked")) {
                var compareOptions = jQuery('#jeproshop_compare_options');
                if (compareOptions.val() == 1){ setPreviousPeriod(); }

                if (compareOptions.val() == 2){ setPreviousYear(); }

                var startDateCompare = jQuery('#jeproshop_date_start_compare');
                var endDateCompare = jQuery('#jeproshop_date_end_compare');

                setStartCompare(datePickerStart, startDateCompare.val());
                setEndCompare(datePickerStart, endDateCompare.val());
                setStartCompare(datePickerEnd, startDateCompare.val());
                setEndCompare(datePickerEnd, endDateCompare.val());
                setCompare(datePickerStart, true);
                setCompare(datePickerEnd, true);
            }
        }

        function setDayPeriod() {
            var date = new Date();
            var startFrom = jQuery("#jeproshop_date_start");
            startFrom.val(date.format(startFrom.data('date-format')));
            var endsAt = jQuery("#jeproshop_date_end");
            endsAt.val(date.format(endsAt.data('date-format')));
            startFrom.trigger('change');

            updatePickerFromInput();
            jQuery('#jeproshop_date_picker_from_info').html(startFrom.val());
            jQuery('#jeproshop_date_picker_to_info').html(endsAt.val());
            jQuery('#jeproshop_preselect_date_range').val('day');
            jQuery('button[name="submit_date_range"]').click();
            switchActiveButton('day');
        }

        function setPreviousDayPeriod() {
            var date = new Date();
            date = date.subDays(1);
            var startDate = jQuery("#jeproshop_date_start");
            var endDate = jQuery("#jeproshop_date_end");
            startDate.val(date.format(startDate.data('date-format')));
            endDate.val(date.format(endDate.data('date-format')));
            startDate.trigger('change');

            updatePickerFromInput();
            jQuery('#jeproshop_date_picker_from_info').html(startDate.val());
            jQuery('#jeproshop_date_picker_to_info').html(endDate.val());
            jQuery('#jeproshop_preselect_date_range').val('previous-day');
            switchActiveButton('previous-day');
            jQuery('button[name="submit_date_range"]').click();
        }

        function setMonthPeriod() {
            var date = new Date();
            var endDate = jQuery("#jeproshop_date_end");
            endDate.val(date.format(endDate.data('date-format')));
            date = new Date(date.setDate(1));
            var startDate = jQuery("#jeproshop_date_start");
            startDate.val(date.format(startDate.data('date-format')));
            startDate.trigger('change');

            updatePickerFromInput();
            jQuery('#jeproshop_date_picker_from_info').html(startDate.val());
            jQuery('#jeproshop_date_picker_to_info').html(endDate.val());
            jQuery('#jeproshop_preselect_date_range').val('month');
            switchActiveButton('month');
            jQuery('button[name="submit_date_range"]').click();
        }

        function switchActiveButton(target){
            var day = jQuery('#jeproshop_submit_date_day');
            var month = jQuery('#jeproshop_submit_date_month');
            var year = jQuery('#jeproshop_submit_date_year');
            var previousDay = jQuery('#jeproshop_submit_date_previous_day');
            var previousMonth = jQuery('#jeproshop_submit_date_previous_month');
            var previousYear = jQuery('#jeproshop_submit_date_previous_year');

            day.removeClass('btn-success');
            month.removeClass('btn-success');
            year.removeClass('btn-success');
            previousDay.removeClass('btn-success');
            previousMonth.removeClass('btn-success');
            previousYear.removeClass('btn-success');

            switch(target){
                case 'month' : month.addClass('btn-success'); break;
                case 'year' : year.addClass('btn-success'); break;
                case 'previous-day' : previousDay.addClass('btn-success'); break;
                case 'previous-month' : previousMonth.addClass('btn-success'); break;
                case 'previous-year' : previousYear.addClass('btn-success'); break;
                default : day.addClass('btn-success'); break;
            }
        }

        function setPreviousMonthPeriod() {
            var date = new Date();
            date = new Date(date.getFullYear(), date.getMonth(), 0);
            var endDate = jQuery("#jeproshop_date_end");
            endDate.val(date.format(endDate.data('date-format')));
            date = new Date(date.setDate(1));
            var startDate = jQuery("#jeproshop_date_start");
            startDate.val(date.format(startDate.data('date-format')));
            startDate.trigger('change');

            updatePickerFromInput();
            jQuery('#jeproshop_date_picker_from_info').html(startDate.val());
            jQuery('#jeproshop_date_picker_to_info').html(endDate.val());
            jQuery('#jeproshop_preselect_date_range').val('previous-month');
            switchActiveButton('previous-month');
            jQuery('button[name="submit_date_range"]').click();
        }

        function setYearPeriod() {
            var date = new Date();
            var endDate = jQuery("#jeproshop_date_end");
            endDate.val(date.format(endDate.data('date-format')));
            date = new Date(date.getFullYear(), 0, 1);
            var startDate = jQuery("#jeproshop_date_start");
            startDate.val(date.format(startDate.data('date-format')));
            startDate.trigger('change');

            updatePickerFromInput();
            jQuery('#jeproshop_date_picker_from_info').html(startDate.val());
            jQuery('#jeproshop_date_picker_to_info').html(endDate.val());
            jQuery('#jeproshop_preselect_date_range').val('year');
            jQuery('button[name="submit_date_range"]').click();
            switchActiveButton('year');
        }

        function setCompare(elt, value){
            elt.data("compare", value);
            updateRange(elt);
        }
        
        function updateRange(elt){
            var calendarWrapper = jQuery("#" + options.wrapper);
            var end = elt.data("end"), start = elt.data("start");
            var endCompare = elt.data("end-compare"), startCompare = elt.data("start-compare");

            calendarWrapper.find(".day").each(function () {
                var dateValue = parseInt(jQuery(this).data('val'), 10);

                if (end && start) {
                    if(dateValue > start && dateValue < end) {
                        jQuery(this).addClass("range");
                    }
                    if(dateValue === start) {
                        jQuery(this).addClass("start-selected");
                    }
                    if(dateValue === end) {
                        jQuery(this).addClass("end-selected");
                    }
                }

                if (endCompare && startCompare) {
                    jQuery(this).removeClass("range-compare").removeClass("start-selected-compare").removeClass("end-selected-compare");

                    if(dateValue > startCompare && dateValue < endCompare) {
                        jQuery(this).addClass("range-compare");
                    }
                    if(dateValue === startCompare) {
                        jQuery(this).addClass("start-selected-compare");
                    }
                    if(dateValue === endCompare) {
                        jQuery(this).addClass("end-selected-compare");
                    }
                } else {
                    jQuery(this).removeClass("range-compare").removeClass("start-selected-compare").removeClass("end-selected-compare");
                }
            });
        }

        function setDatePickerValue(elt, newDate){
            if(typeof newDate === 'string'){
                elt.date = DPGlobal.parseDate(newDate, elt.data("format"));
            }else{
                elt.date = new Date(newDate);
            }
            setFormattedDate(elt);
            elt.data("view-date", new Date(elt.date.getFullYear(), elt.date.getMonth(), 1, 0, 0, 0, 0));
            fillCalendarPicker(elt);
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
                    elt.data("start-compare", DPGlobal.parseDate(value, DPGlobal.parseFormat('Y-m-d')).getTime());
                }
                else if (value.constructor === Number) {
                    elt.data("start-compare", value);
                }
                else if (value.constructor === Date) {
                    elt.data("start-compare", value.getTime());
                }
            }
        }

        function setEndCompare(elt, value){
            if(value !== undefined){
                if (value === null){
                    elt.data("end-compare", value);
                } else if (value.constructor === String){
                    elt.data("end-compare", DPGlobal.parseDate(value, DPGlobal.parseFormat('Y-m-d')).getTime());
                } else if (value.constructor === Number){
                    elt.data("end-compare", value);
                } else if (value.constructor === Date){
                    elt.data("end-compare", value.getTime());
                }
            }
        }

        function initializeCalendarDatePicker(elt){
            if(typeof(elt) !== 'undefined'){
                if(typeof(options.dates) !== 'undefined'){
                    DPGlobal.dates = options.dates;
                }

                var startDate = jQuery("#" + options.start_date_id).val(), endDate = jQuery("#" + options.end_date_id).val();
                var compareDate = undefined;
                if(startDate !== undefined){
                    if(startDate.constructor === String){
                        elt.data("start-date", (DPGlobal.parseDate(startDate, DPGlobal.parseFormat('Y-m-d')).getTime()));
                    }else if(startDate.constructor === Number){
                        elt.data("start-date", startDate);
                    }else if(startDate.constructor === Date){
                        elt.data("start-date", startDate.getTime());
                    }
                }

                if(typeof(endDate !== 'undefined')){
                    if(endDate.constructor === String){
                        elt.data("end-date", DPGlobal.parseDate(endDate, DPGlobal.parseFormat('Y-m-d')).getTime());
                    }else if(endDate.constructor === Number){
                        elt.data("end-date", endDate);
                    }else if(endDate.constructor === Date){
                        elt.data("end-date", endDate.getTime());
                    }
                }

                if(compareDate !== undefined){ options.compare = compareDate; }

                elt.data("format", DPGlobal.parseFormat(elt.data("format")||elt.data('date-format')||'Y-m-d'));

                var picker  = jQuery(DPGlobal.template).appendTo(elt).show();
                picker.on('click', function(evt){ handleItemClicked(elt, evt); });
                picker.on('mouseover', function(evt){ handleItemMouseOver(elt, evt); });
                picker.on('mouseout', function(evt){ handleItemMouseOut(elt, evt);  });
                elt.data("picker", picker);
                elt.data("min-view-mode", (elt.data("min-view-mode")||elt.data('date-min-view-mode')||0));

                if (typeof elt.data("min-view-mode") === 'string') {
                    switch (elt.data("min-view-mode")) {
                        case 'months':
                            elt.data("min-view-mode", 1);
                            break;
                        case 'years':
                            elt.data("min-view-mode", 2);
                            break;
                        default:
                            elt.data("min-view-mode", 0);
                            break;
                    }
                }

                elt.data("view-mode", (elt.data("view-mode")||elt.data('date-view-mode')||0));
                if (typeof elt.data("view-mode") === 'string') {
                    switch (elt.data("view-mode")){
                        case 'months':
                            elt.data("view-mode", 1);
                            break;
                        case 'years':
                            elt.data("view-mode", 2);
                            break;
                        default:
                            elt.data("view-mode", 0);
                            break;
                    }
                }
                options.start_view_mode = elt.data("view-mode");
                elt.data("week-start", (elt.data("week-start")||elt.data('date-week-start')||0));
                options.week_end = elt.data("week-start") === 0 ? 6 : elt.data("week-start") - 1;
                //options.onRender = options.onRender;
                fillDown(elt);
                fillMonths(elt);
                updateDatePicker(elt);
                showMode(elt);
            }
        }

        function handleItemClicked(elt, evt){
            evt.stopPropagation();
            evt.preventDefault();
            var target = jQuery(evt.target).closest('span, td, th');
            if (target.length === 1) {
                switch(target[0].nodeName.toLowerCase()) {
                    case 'th':
                        switch(target[0].className) {
                            case 'month-switch':
                                this.showMode(1);
                                break;
                            case 'prev':
                            case 'next':
                                this.viewDate['set'+DPGlobal.modes[this.viewMode].navFnc].call(
                                    this.viewDate,
                                    this.viewDate['get'+DPGlobal.modes[this.viewMode].navFnc].call(this.viewDate) +
                                    DPGlobal.modes[this.viewMode].navStep * (target[0].className === 'prev' ? -1 : 1)
                                );

                                this.date = new Date(this.viewDate);
                                this.element.trigger({
                                    type: 'changeDate',
                                    date: this.date,
                                    viewMode: DPGlobal.modes[this.viewMode].clsName
                                });

                                this.fill();
                                this.set();
                                break;
                        }
                        break;

                    case 'span':
                        if (target.is('.month')) {
                            var month = target.parent().find('span').index(target);
                            this.viewDate.setMonth(month);
                        } else {
                            var year = parseInt(target.text(), 10)||0;
                            this.viewDate.setFullYear(year);
                        }
                        if (this.viewMode !== 0) {
                            this.date = new Date(this.viewDate);
                            this.element.trigger({
                                type: 'changeDate',
                                date: this.date,
                                viewMode: DPGlobal.modes[this.viewMode].class_name
                            });
                        }
                        this.showMode(-1);
                        this.fill();
                        this.set();
                        break;

                    case 'td':
                        //reset
                        if (target.is('.day') && !target.is('.disabled')){
                            // reset process for a new range
                            if (elt.data("start") && elt.data("end")) {
                                if (!elt.data("compare")) {
                                    elt.data("click", 2);
                                    jQuery(".range").removeClass('range');
                                    jQuery(".start-selected").removeClass("start-selected");
                                    jQuery(".end-selected").removeClass("end-selected");
                                }
                            }
                            if(elt.data("click") === 2) {
                                if (elt.data("compare")) {
                                    elt.data("start-compare", null);
                                    elt.data("end-compare", null);
                                }
                                else {
                                    elt.data("start", null);
                                    elt.data("end", null);
                                }
                                elt.data("click", null);
                                elt.data("switched", false);
                                if (elt.data("compare")){
                                    jQuery("td.day").removeClass("start-selected-compare").removeClass("end-selected-compare");
                                    jQuery(".date-input").removeClass("input-selected").removeClass("input-complete");
                                    jQuery(".range-compare").removeClass("range-compare");
                                } else {
                                    jQuery("td.day").removeClass("start-selected").removeClass("end-selected");
                                    jQuery(".date-input").removeClass("input-selected").removeClass("input-complete");
                                    jQuery(".range").removeClass("range");
                                }
                            }
                            //define start with first click or switched one
                            var startCompare = jQuery("#jeproshop_date_start_compare");
                            var startDate = jQuery('#jeproshop_date_start');
                            var endDate = jQuery('#jeproshop_date_end');
                            if (!elt.data("click") || elt.data("switched" === true)){
                                if (elt.data("compare")) {
                                    jQuery(".start-selected-compare").removeClass("start-selected-compare");
                                    target.addClass("start-selected-compare");
                                    elt.data("start-compare", target.data("val"));
                                    startCompare.val(DPGlobal.formatDate(new Date(elt.data("start-compare")), DPGlobal.parseFormat('Y-m-d')));
                                } else {
                                    jQuery(".start-selected").removeClass("start-selected");
                                    target.addClass("start-selected");
                                    elt.data("start", target.data("val"));
                                    startDate.val(DPGlobal.formatDate(new Date(elt.data("start")), DPGlobal.parseFormat('Y-m-d')));
                                    startDate.trigger('change');
                                }

                                if(!elt.data("switched")) { elt.data("click", 1);} else { elt.data("click", 2);}
                                if(!elt.data("switched")) {
                                    if (elt.data("compare")) {
                                        jQuery("#jeproshop_date_end_compare").val(null).focus().addClass("input-selected");
                                        target.addClass("start-selected-compare").addClass("end-selected-compare");
                                    } else {
                                        endDate.val(null).focus().addClass("input-selected");
                                        target.addClass("start-selected").addClass("end-selected");
                                    }
                                }

                                if (elt.data("compare")){
                                    jQuery("#jeproshop_date_start_compare").removeClass("input-selected").addClass("input-complete");
                                }
                                else {
                                    startDate.removeClass("input-selected").addClass("input-complete");
                                }
                            }
                            //define end
                            else {
                                if (elt.data("compare")) {
                                    //var endCompareDate = jQuery('#jeproshop_date_end_compare');
                                    jQuery(".end-selected-compare").removeClass("end-selected-compare");
                                    target.addClass("end-selected-compare");
                                    elt.data("end-compare", target.data("val"));
                                    startCompare.val(DPGlobal.formatDate(new Date(elt.data("end-compare")), DPGlobal.parseFormat('Y-m-d')));
                                    elt.data("click", 2);
                                    startCompare.removeClass("input-selected").addClass("input-complete");
                                } else {
                                    jQuery(".end-selected").removeClass("end-selected");
                                    target.addClass("end-selected");
                                    elt.data("end", target.data("val"));
                                    endDate.val(DPGlobal.formatDate(new Date(elt.data("end")), DPGlobal.parseFormat('Y-m-d')));
                                    elt.data("click", 2);
                                    endDate.removeClass("input-selected").addClass("input-complete");
                                    endDate.trigger('change');
                                }
                            }
                        }
                        break;
                }
            }
        }

        function handleItemMouseOver(elt, evt){
            //data-val from day overed
            var overValue = jQuery(evt.target).data("val");

            //action when one of two dates has been set
            if(elt.data("click") === 1 && overValue){
                var datePicker = jQuery("#jeproshop_date_picker");
                if(elt.data("compare")){
                    datePicker.find(".range-compare").removeClass("range-compare");
                    var startCompareDate = jQuery("#jeproshop_date_start_compare");
                    var endCompareDate = jQuery("#jeproshop_date_end_compare");

                    if (elt.data("start-compare") && overValue < elt.data("start-compare")){
                        elt.data("end-compare",  elt.data("start-compare"));
                        endCompareDate.val(DPGlobal.formatDate(new Date(elt.data("start-compare")), DPGlobal.parseFormat('Y-m-d'))).removeClass("input-selected");
                        startCompareDate.val(null).focus().addClass("input-selected");
                        datePicker.find(".start-selected-compare").removeClass("start-selected-compare").addClass("end-selected-compare");
                        elt.data("start-compare", null);
                        elt.data("switched", true);
                    }
                    else if (elt.data("end-compare") && overValue > elt.data("end-compare")){
                        elt.data("start-compare", elt.data("end-compare"));
                        startCompareDate.val(DPGlobal.formatDate(new Date(elt.data("end-compare")), DPGlobal.parseFormat('Y-m-d'))).removeClass("input-selected");
                        endCompareDate.val(null).focus().addClass("input-selected");
                        datePicker.find(".end-selected-compare").removeClass("end-selected-compare").addClass("start-selected-compare");
                        elt.data("end-compare", null);
                        elt.data("switched", false);
                    }

                    if (elt.data("start-compare")){
                        jQuery(".end-selected-compare").removeClass("end-selected-compare");
                        jQuery(evt.target).addClass("end-selected-compare");
                    }
                    else if (elt.data("end-compare")){
                        jQuery(".start-selected-compare").removeClass("start-selected-compare");
                        jQuery(evt.target).addClass("start-selected-compare");
                    }
                }
                else {
                    datePicker.find(".range").removeClass("range");
                    var periodEndDate = jQuery("#jeproshop_date_end")
                    var periodStartDate = jQuery("#jeproshop_date_start");

                    if (elt.data("start") && overValue < elt.data("start")){
                        elt.data("end", elt.data("start"));
                        periodEndDate.val(DPGlobal.formatDate(new Date(elt.data("start")), DPGlobal.parseFormat('Y-m-d'))).removeClass("input-selected");
                        periodEndDate.trigger('change');
                        periodStartDate.val(null).focus().addClass("input-selected");
                        datePicker.find(".start-selected").removeClass("start-selected").addClass("end-selected");
                        elt.data("start", null);
                        elt.data("switched", true);
                    }else if (elt.data("end") && overValue > elt.data("end")) {
                        elt.data("start", elt.data("end"));
                        periodStartDate.val(DPGlobal.formatDate(new Date(elt.data("end")), DPGlobal.parseFormat('Y-m-d'))).removeClass("input-selected");
                        periodStartDate.trigger('change');
                        periodEndDate.val(null).focus().addClass("input-selected");
                        datePicker.find(".end-selected").removeClass("end-selected").addClass("start-selected");
                        elt.data("end", null);
                        elt.data("switched", false);
                    }

                    if (elt.data("start")){
                        jQuery(".end-selected").removeClass("end-selected");
                        jQuery(evt.target).addClass("end-selected");
                    }
                    else if (elt.data("end")){
                        jQuery(".start-selected").removeClass("start-selected");
                        jQuery(evt.target).addClass("start-selected");
                    }
                }
                //switch
                jQuery(".input-date").removeClass("input-complete");
                mouseOverRange(elt, overValue);
            }
        }
        
        function mouseOverRange(elt, overValue) {
            //range
            jQuery("#jeproshop_date_picker").find(".day").each(function(index, item){
                item = jQuery(item);
                var dateValue = parseInt(item.data('val'),10);
                if (elt.data("compare")){
                    if (!elt.data("end-compare") && dateValue > elt.data("start-compare") && dateValue < overValue) {
                        item.not(".old").not(".new").addClass("range-compare");
                    }
                    else if (!elt.data("start-compare") && dateValue > overValue && dateValue < elt.data("end-compare")) {
                        item.not(".old").not(".new").addClass("range-compare");
                    }
                    else if (elt.data("start-compare") && elt.data("end-compare")) {
                        item.addClass("range-compare");
                    }
                }
                else {
                    if (!elt.data("end") && dateValue > elt.data("start") && dateValue < overValue) {
                        item.not(".old").not(".new").addClass("range");
                    }
                    else if (!elt.data("start") && dateValue > overValue && dateValue < elt.data("end")) {
                        item.not(".old").not(".new").addClass("range");
                    }
                }
            });
        }

        function handleItemMouseOut(elt, evt){
            var datePicker = jQuery('#jeproshop_date_picker');
            var endSelectedCompare = jQuery(".end-selected-compare");
            var startSelectedCompare = jQuery(".start-selected-compare");
            if(elt.data("compare")){
                if (!elt.data("start-compare") ||!elt.data("end-compare")) {
                    datePicker.find('.range-compare').removeClass("range-compare");
                }
                if (!elt.data("end-compare")) {
                    endSelectedCompare.removeClass("end-selected-compare");
                }
                else if (!elt.data("start-compare"))
                    startSelectedCompare.removeClass("start-selected-compare");
            }
            else {
                if (!elt.data("start")||!elt.data("end")) {
                    datePicker.find(".range").removeClass("range");
                }
                if (!elt.data("end")) {
                    jQuery(".end-selected").removeClass("end-selected");
                }
                else if (!elt.data("start")) {
                    jQuery(".start-selected").removeClass("start-selected");
                }
            }
        }
        
        function showMode(elt, dir){
            if(dir){
                elt.data("view-mode", Math.max(elt.data("min-view-mode"), Math.min(2, elt.data("view-mode") + dir)));
            }
            elt.data("picker").find('>div').hide().filter('.date-range-picker-' + DPGlobal.modes[elt.data("view-mode")].class_name).show();
        }

        function updateDatePicker(elt, newDate) {
            options.date = DPGlobal.parseDate(
                typeof newDate === 'string' ? newDate : (elt.is("input") ? elt.val() : elt.data('date')),
                (elt.data("format") ? elt.data("format") :  DPGlobal.parseFormat('Y-m-d'))
            );
            elt.data("view-date", new Date(options.date.getFullYear(), options.date.getMonth(), 1, 0, 0, 0, 0));
            fillCalendarPicker(elt);
        }

        function fillCalendarPicker(elt){
            var d = new Date(elt.data("view-date")),
                year = d.getFullYear(),
                month = d.getMonth(),
                currentDate = elt.data("date").valueOf();
            elt.data("picker").find('.date-range-picker-days th:eq(1)')
                .text(year+' / '+DPGlobal.dates.months[month]).append('&nbsp;<small><i class="icon-angle-down"></i><small>');
            var prevMonth = new Date(year, month-1, 28,0,0,0,0),
                daysInMonth = DPGlobal.getDaysInMonth(prevMonth.getFullYear(), prevMonth.getMonth());
            prevMonth.setDate(daysInMonth);
            prevMonth.setDate(daysInMonth - (prevMonth.getDay() - elt.data("week-start") + 7)%7);
            var nextMonth = new Date(prevMonth);
            nextMonth.setDate(nextMonth.getDate() + 42);
            nextMonth = nextMonth.valueOf();
            var html = [];
            var clsName, prevY, prevM;
            while(prevMonth.valueOf() < nextMonth) {
                if (prevMonth.getDay() === elt.data("week-start")){
                    html.push('<tr>');
                }
                //clsName = options.onRender(prevMonth);
                prevY = prevMonth.getFullYear();
                prevM = prevMonth.getMonth();
                if ((prevM < month &&  prevY === year) ||  prevY < year) {
                    clsName = ' old';
                } else if ((prevM > month && prevY === year) || prevY > year) {
                    clsName = ' new';
                }else{ clsName = 'day'; }

                if (clsName === 'day'){
                    html.push('<td class="day" data-val="'+prevMonth.getTime()+'">' + prevMonth.getDate() + '</td>');
                } else {
                    html.push('<td class="'+clsName+'">' + prevMonth.getDate() + '</td>');
                }

                if (prevMonth.getDay() === options.week_end) {
                    html.push('</tr>');
                }
                prevMonth.setDate(prevMonth.getDate()+1);
            }
            elt.data("picker").find('.date-range-picker-days tbody').empty().append(html.join(''));
            var currentYear = options.date.getFullYear();

            var months = elt.data("picker").find('.date-range-picker-months')
                .find('th:eq(1)')
                .text(year)
                .end()
                .find('span').removeClass('active');
            if (currentYear === year) {
                months.eq(options.date.getMonth()).addClass('active');
            }

            html = '';
            year = parseInt(year/10, 10) * 10;
            var yearCont = elt.data("picker").find('.date-range-picker-years')
                .find('th:eq(1)')
                .text(year + '-' + (year + 9))
                .end()
                .find('td');
            year -= 1;
            for (var i = -1; i < 11; i++) {
                html += '<span class="year'+(i === -1 || i === 10 ? ' old' : '')+(currentYear === year ? ' active' : '')+'">'+year+'</span>';
                year += 1;
            }
            yearCont.html(html);
            updateRange(elt);
        }

        function fillMonths(elt) {
            var html = '';
            var i = 0;
            while (i < 12) {
                html += '<span class="month">' + DPGlobal.dates.monthsShort[i++] + '</span>';
            }
            elt.data("picker").find('.date-range-picker-months td').append(html);
        }

        function fillDown(elt) {
            var dowCnt = elt.data("week-start");
            var html = '<tr>';
            while (dowCnt < elt.data("week-start") + 7) {
                html += '<th class="dow">' + DPGlobal.dates.daysMin[(dowCnt++)%7] + '</th>';
            }
            html += '</tr>';
            elt.data("picker").find('.date-range-picker-days thead').append(html);
        }

        function setRangeStartDate(elt, value) {
            if (value.constructor === String) {
                var startDate = DPGlobal.parseDate(value, DPGlobal.parseFormat('Y-m-d')).getTime();
                elt.data("start-date", startDate);
            } else if (value.constructor === Number) {
                elt.data("start-date", value);
            } else if (value.constructor === Date) {
                elt.data("start-date", value.getTime());
            }
        }

        function setRangeEndDate(elt, value) {
            if (value.constructor === String) {
                var endDate = DPGlobal.parseDate(value, DPGlobal.parseFormat('Y-m-d')).getTime();
                elt.data("end-date", endDate);
            } else if (value.constructor === Number) {
                elt.data("end-date", value);
            } else if (value.constructor === Date) {
                elt.data("end-date", value.getTime());
            }
        }
    };




    var DPGlobal = {
        modes: [
            {
                class_name: 'days',
                nav_func: 'Month',
                nav_step: 1
            },
            {
                class_name: 'months',
                nav_func: 'FullYear',
                nav_step: 1
            },
            {
                class_name: 'years',
                nav_func: 'FullYear',
                nav_step: 10
            }
        ],
        dates:{
            days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
            months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
        },
        isLeapYear: function (year) {
            return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0));
        },
        getDaysInMonth: function (year, month) {
            return [31, (DPGlobal.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month];
        },
        parseFormat: function(format){
            var separator = format.match(/[.\/\-\s].*?/),
                parts = format.split(/\W+/);
            if (!separator || !parts || parts.length === 0){
                throw new Error("Invalid date format.");
            }
            return {separator: separator, parts: parts};
        },
        parseDate: function(inDate, format) {
            var parts = inDate.split(format.separator),
                date = new Date(),
                val;
            date.setHours(0);
            date.setMinutes(0);
            date.setSeconds(0);
            date.setMilliseconds(0);
            if (parts.length === format.parts.length) {
                var year = date.getFullYear(), day = date.getDate(), month = date.getMonth();
                for (var i=0, cnt = format.parts.length; i < cnt; i++) {
                    val = parseInt(parts[i], 10)||1;
                    switch(format.parts[i]) {
                        case 'dd':
                        case 'd':
                            day = val;
                            date.setDate(val);
                            break;
                        case 'mm':
                        case 'm':
                            month = val - 1;
                            date.setMonth(val - 1);
                            break;
                        case 'yy':
                        case 'y':
                            year = 2000 + val;
                            date.setFullYear(2000 + val);
                            break;
                        case 'yyyy':
                        case 'Y':
                            year = val;
                            date.setFullYear(val);
                            break;
                    }
                }
                date = new Date(year, month, day, 0 ,0 ,0);
            }
            return date;
        },
        formatDate: function(date, format){
            var val = {
                d: date.getDate(),
                m: date.getMonth() + 1,
                yy: date.getFullYear().toString().substring(2),
                y: date.getFullYear().toString().substring(2),
                yyyy: date.getFullYear(),
                Y: date.getFullYear()
            };
            val.d = (val.d < 10 ? '0' : '') + val.d;
            val.m = (val.m < 10 ? '0' : '') + val.m;
            date = [];
            for (var i=0, cnt = format.parts.length; i < cnt; i++) {
                date.push(val[format.parts[i]]);
            }
            return date.join(format.separator);
        },
        head_template: '<thead><tr><th class="prev"><i class="icon-angle-left"></i> </th>'+
            '<th colspan="5" class="month-switch"></th><th class="next"><i class="icon-angle-right" ></i> </th>'+
            '</tr></thead>',
        content_template : '<tbody><tr><td colspan="7"></td></tr></tbody>'
    };

    DPGlobal.template = '<div class="date-range-picker" ><div class="date-range-picker-days">'+
        '<table class=" table-condensed">' + DPGlobal.head_template + '<tbody></tbody></table>'+
        '</div><div class="date-range-picker-months"><table class="table-condensed">'+
        DPGlobal.head_template + DPGlobal.content_template + '</table>' + '</div>'+
        '<div class="date-range-picker-years"><table class="table-condensed">'+
        DPGlobal.head_template + DPGlobal.content_template + '</table></div></div>';
})(jQuery);