<template>
    <div class="row">
        <div class="col-sm-12" v-for="(note, index) in notes" :key="index">
            <div class="alert" :class="{ 
                                    'alert-success': types.success.includes(note.type), 
                                    'alert-warning': types.warning.includes(note.type), 
                                    'alert-info': !types.all().includes(note.type), 
                                    'alert-danger': types.error.includes(note.type)
                                }">
                <slot :note="note">
                    {{note.text}}
                </slot>
                
                <span class="close" @click="note.close()">x</span>
            </div>
        </div>
    </div>
</template>

<script>
    import EventBus from '../admin/time-tracker/comps/event-bus'

    export default {
        props: [ 'name' ],
        data() {
            return {
                notes: [],
                types: {
                    success: ['success'],
                    warning: ['warning', 'warn'],
                    error: ['danger', 'error'],
                    all () {
                        return [
                            ...this.success,
                            ...this.warning,
                            ...this.error
                        ]
                    }
                }
            }
        },
        methods: {
            create(note) {
                if (note) {
                    const id = ((this.notes.map(note => note.id)[this.notes.length - 1] || 0) + 1)
                    const newNote = {
                        id,
                        type: note.type || 'success',
                        close: () => {
                            this.notes.splice(this.notes.findIndex(note => note.id === id), 1)
                        }
                    }
                    if (typeof(note) === 'string') {
                        newNote.text = note
                    }
                    else if (note.constructor.name === 'Object') {
                        Object.assign(newNote, note)
                    }
                    else {
                        newNote.data = note
                    }
                    if (!note.noTimeout) {
                        newNote.timeout = setTimeout(() => {
                            newNote.close()
                        }, note.interval || 15000)
                    }
                    
                    this.notes.push(newNote)
                    console.log('notifications:create', newNote)
                }
            }
        },
        mounted() {
            EventBus.$on('notifications:create', this.create)
        }
    }
</script>

<style>
    div.alert .close {
        cursor: pointer;
        margin-top: -2px;
    }
</style>