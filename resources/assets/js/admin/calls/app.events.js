export default (App, Event) => {
    const $table = App.$refs.tblCalls;

    Event.$on('vue-tables.pagination', (page) => {
        if (page === $table.totalPages) {
            console.log('next table data')
            App.next();
        }
    })
}