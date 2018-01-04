<template>
    <div class="row">
        <div class="col-sm-12" v-for="(note, index) in notes" :key="index">
            <div class="alert alert-success">
                {{note.text}}
                <span class="close" @click="note.close()">x</span>
            </div>
        </div>
    </div>
</template>

<script>
    import EventBus from '../../admin/time-tracker/comps/event-bus'

    export default {
        name: 'notifications',
        props: [],
        data() {
            return {
                notes: []
            }
        },
        mounted() {
            EventBus.$on('notifications:create', (note) => {
                if (typeof(note) === 'string') {
                    const id = ((this.notes.map(note => note.id)[this.notes.length - 1] || 0) + 1)
                    const newNote = {
                        id,
                        text: note,
                        close: () => {
                            this.notes.splice(this.notes.findIndex(note => note.id === id), 1)
                        }
                    }
                    newNote.timeout = setTimeout(() => {
                        newNote.close()
                    }, 2000)
                    this.notes.push(newNote)
                }
            })
        }
    }
</script>

<style>
    div.alert .close {
        cursor: pointer;
        margin-top: -2px;
    }
</style>