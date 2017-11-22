<template>
    <table>
        <thead>
        <tr>
            <th v-for="(col, index) in columns"
                v-if="col.name"
                @click="sortBy(index)"
                :class="sortKey == index ? 'active th-' + index : 'th-' + index" :key="index">
                {{ col.name | capitalize }}
                <span class="arrow" :class="sortOrders[index] > 0 ? 'asc' : 'dsc'"></span>
            </th>
            <th v-else :class="'th-' + index"></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(entry, entryIndex) in filteredData" :key="entryIndex">
            <td v-for="(col, index) in columns" @click="$emit('click', index, entry)" :class="'td-' + index" :key="index">
                <div v-if="col.content" v-html="col.content"></div>
                <div v-else>{{entry[index]}}</div>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        props: {
            data: Array,
            options: Object,
            filterKey: String
        },

        data: function () {
            let sortOrders = {}
            _.mapValues(this.columns, (column, index) => {
                sortOrders[index] = 1
            })
            return {
                sortKey: '',
                sortOrders: sortOrders,
            }
        },

        computed: {
            columns: function () {
                let options = this.options
                if (_.isUndefined(options)) {
                    return []
                }

                let columns = {}
                _.mapValues(options.columns, (column, index) => {
                    if (!_.isUndefined(column)) {
                        if (_.isUndefined(column.name)) {
                            column.name = index
                        }
                        if (_.isUndefined(column.content) || column.content === '') {
                            column.content = null
                        }

                        columns[index] = column
                    }
                })

                return columns
            },
            filteredData: function () {
                let sortKey = this.sortKey
                let filterKey = this.filterKey && this.filterKey.toLowerCase()
                let order = this.sortOrders[sortKey] || 1
                let data = this.data
                if (filterKey) {
                    data = data.filter(function (row) {
                        return Object.keys(row).some(function (key) {
                            return String(row[key]).toLowerCase().indexOf(filterKey) > -1
                        })
                    })
                }
                if (sortKey) {
                    data = data.slice().sort(function (a, b) {
                        a = a[sortKey]
                        b = b[sortKey]
                        return (a === b ? 0 : a > b ? 1 : -1) * order
                    })
                }
                return data
            }
        },

        filters: {
            capitalize: function (str) {
                return str.charAt(0).toUpperCase() + str.slice(1)
            }
        }
        ,

        methods: {
            className (index) {
                return
            },

            sortBy: function (key) {
                this.sortKey = key
                this.sortOrders[key] = this.sortOrders[key] * -1
            }
        }
    }
</script>

<style>
    table {
        display: table !important;
        /*border: 2px solid #2196f3;*/
        /*border-radius: 3px;*/
        background-color: #fff;
        border-collapse: separate !important;
        border-spacing: 2px !important;
    }

    th {
        background-color: #2196f3;
        color: rgba(255, 255, 255, 0.66);
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    td {
        background-color: #f9f9f9;
    }

    th, td {
        min-width: 20px;
        padding: 5px 15px;
    }

    th.active {
        color: #fff;
    }

    th.active .arrow {
        opacity: 1;
    }

    .arrow {
        display: inline-block;
        vertical-align: middle;
        width: 0;
        height: 0;
        margin-left: 5px;
        opacity: 0.66;
    }

    .arrow.asc {
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-bottom: 4px solid #fff;
    }

    .arrow.dsc {
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 4px solid #fff;
    }

</style>