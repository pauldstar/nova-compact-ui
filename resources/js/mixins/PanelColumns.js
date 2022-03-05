export default {
    data: _ => ({
        minimumColumnCount: 2,
        columns: null
    }),

    computed: {
        columnsWithFields() {
            if (this.mode === 'modal') return;

            let sortedFields = this.columnSortedFields();
            this.prepareColumns(sortedFields);

            sortedFields.forEach(field => {
                if (field.column !== undefined) {
                    this.addFieldToColumn(field);
                } else {
                    this.addFieldToColumn(field, this.nextFreeColumn());
                }
            });

            return this.columns;
        }
    },

    methods: {
        columnSortedFields() {
            let fields = this.fields;

            return fields.filter(f => !f.tabulated && !f.listable).sort((a, b) => {
                let aCol = a.column === undefined ? -1 : a.column,
                    bCol = b.column === undefined ? -1 : b.column;

                return -1 * (aCol - bCol);
            });
        },

        addFieldToColumn(field, columnNumber) {
            if (columnNumber !== undefined) {
                this.columns[columnNumber].push(field);
            } else {
                this.columns[field.column].push(field);
            }
        },

        nextFreeColumn() {
            let freeColSizes = [];

            for (let c = 0; c < this.minimumColumnCount; c++) {
                freeColSizes[c] = this.columns[c].length;
            }

            return this.smallestColumn(freeColSizes);
        },

        smallestColumn(columns) {
            let minValue, minColumn, value;

            for (let c = 0; c < columns.length; c++) {
                value = columns[c];

                if (value !== undefined) {
                    if (value === 0) return c;

                    if (value < minValue || !minValue) {
                        minValue = value;
                        minColumn = c;
                    }
                }
            }

            return minColumn;
        },

        isRow(column) {
            return column[0] && column[0].columnRow;
        },

        prepareColumns(fields) {
            this.columns = [[], []];

            fields.forEach(field => {
                field.column && this.addColumn(field.column);
            });
        },

        addColumn(columnNumber) {
            if (!this.columns[columnNumber]) {
                this.columns[columnNumber] = [];
            }
        }
    }
}
