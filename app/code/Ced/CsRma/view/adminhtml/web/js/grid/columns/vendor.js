define(
    [
    'Magento_Ui/js/grid/columns/column'
    ], function (Column) {
        'use strict';

        return Column.extend(
            {
                defaults: {
                    bodyTmpl: 'ui/grid/cells/html',
                    fieldClass: {
                        'data-grid-html-cell': true
                    }
                },
                getLabel: function (row) {
                    return row[this.index + '_html']
                },
        
            }
        );
    }
);