define([
    'Magento_Catalog/component/static-type-select'
], function (TypeSelect) {
    'use strict';

    /**
     * Recursively loops over data to find non-undefined, non-array value
     *
     * @param  {Array} data
     * @return {*} - first non-undefined value in array
     */
    function findFirst(data) {
        var value;

        data.some(function (node) {
            value = node.value;

            if (Array.isArray(value)) {
                value = findFirst(value);
            }

            return !_.isUndefined(value);
        });

        return value;
    }

    return TypeSelect.extend({
        /**
         * Select first available option
         *
         * @returns {Object} Chainable.
         */
        clear: function () {
            var value = this.caption() ? '' : findFirst(this.options());

            this.value(value);

            return this;
        }
    });
});
