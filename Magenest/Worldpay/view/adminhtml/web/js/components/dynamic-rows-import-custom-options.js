define([
    'Magento_Catalog/js/components/dynamic-rows-import-custom-options'
], function (DynamicRows) {
    'use strict';

    return DynamicRows.extend({
        /**
         * Contains old data with new
         *
         * @param {Array} data
         *
         * @returns {Array} changed data
         */
        getNewData: function (data) {
            if (data instanceof Array) {
                return this._super();
            }
            return [];
        }
    });
});
