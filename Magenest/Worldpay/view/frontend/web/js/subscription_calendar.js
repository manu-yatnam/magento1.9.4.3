/**
 * Created by joel on 24/12/2016.
 */
define([
    "jquery",
    "jquery/ui",
    "mage/calendar"
], function($) {
    'use strict';

    $.widget('magenest.subscription_calendar', {
        options: {
            dateSelector: '#user_define_start_date'
        },

        _create: function() {
            var self = this;
            var dateSelectorId = self.options.dateSelector;

            $(dateSelectorId).calendar({
                minDate: 0,
                dateFormat: 'yyyy-mm-dd'
            });
        }
    });

    return $.magenest.subscription_calendar;
});
