var Vue = require('vue');

Vue.use(require('vue-resource'));

new Vue({
    el: '#events',
    data: {
        event: { name: 'asdf11', description: 'ssss22', date: 'dddd33' }
    },
    // Anything within the ready function will run when the application loads
    ready: function() {
        // When the application loads, we want to call the method that initializes
        // some data
        this.fetchEvents();
    },
    // Methods we want to use in our application are registered here
    methods: {

        // We dedicate a method to retrieving and setting some data
        fetchEvents: function() {
            var events = [
                {
                    id: 1,
                    name: 'TIdddddFF',
                    description: 'Toronto International Film Festival',
                    date: '2015-09-10'
                },
                {
                    id: 2,
                    name: 'The Martian Premiere',
                    description: 'The Martian comes to theatres.',
                    date: '2015-10-02'
                },
                {
                    id: 3,
                    name: 'SXSW',
                    description: 'Music, film and interactive festival in Austin, TX.',
                    date: '2016-03-11'
                }
            ];
            // $set is a convenience method provided by Vue that is similar to pushing
            // data onto an array
            this.$set('events', events);
        },

        // Adds an event to the existing events array
        addEvent: function() {
            if(this.event.name) {
                this.events.push(this.event);
                this.event = { name: '', description: '', date: '' };
            }
        },

        // Adds an event to the existing events array
        editEvent: function(index) {
            // show textarea
            var editId = 'event-edit-' + index;
            //alert( $('#' + editId).html() );
            $('#' + editId).toggle();
            //alert(editId);
        },

        deleteEvent: function(index, e) {
            //e.preventDefault();
            //e.stopPropagation();
            if(confirm("Are you sure you want to delete this event?")) {
                // $remove is a Vue convenience method similar to splice
                alert(index);
                console.log(this.events);
                //this.events.$remove(index);
                /*
                this.events.push({ id: 5,
                    name: 'The Martian Premiere',
                    description: 'The Martian comes to theatres.',
                    date: '2015-10-02' });
                    */
                Vue.delete(this.events, index);
                //this.events.splice(index, 1);
                console.log(this.events);
                alert('done');
                return false;
            }
            return false;
        }
    }
});

