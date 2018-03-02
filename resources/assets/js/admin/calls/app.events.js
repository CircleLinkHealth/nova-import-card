export default (App, Event) => {
    const $table = App.$refs.tblCalls;

    Event.$on('vue-tables.pagination', (page) => {
        App.next();
    })
}