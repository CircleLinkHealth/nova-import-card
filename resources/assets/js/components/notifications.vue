<template>
    <div class="row" :class="componentName">
        <div class="col-sm-12 toast-container" v-for="(note, index) in notes" :key="index">
            <div class="alert toast" :class="{
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
    import EventBus from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/time-tracker/comps/event-bus'
    import {Event} from 'vue-tables-2'

    export default {
        props: {
            name: String,
            reverse: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                notes: [],
                types: {
                    success: ['success'],
                    warning: ['warning', 'warn'],
                    error: ['danger', 'error'],
                    all() {
                        return [
                            ...this.success,
                            ...this.warning,
                            ...this.error
                        ]
                    }
                }
            }
        },
        computed: {
            componentName() {
                return `notifications${this.name ? '-' + this.name : ''}`;
            }
        },
        methods: {
            create(note) {

                if (!note) {
                    throw new Error('no note provided');
                }

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

                if (note.overwrite) {
                    this.notes.splice(0);
                    this.notes.push(newNote);
                }
                else if (!this.reverse) this.notes.push(newNote)
                else this.notes.unshift(newNote)

                return newNote

            },
            remove(id) {
                const note = this.notes.find(n => n.id === id)
                if (note) {
                    note.close()
                }
            },
            removeAll() {
                this.notes.splice(0);
            }
        },
        created() {
            EventBus.$on(`${this.componentName}:create`, this.create);
            EventBus.$on(`${this.componentName}:dismissAll`, this.removeAll);
            Event.$on(`${this.componentName}:create`, (...args) => EventBus.$emit(`${this.componentName}:create`, ...args));
            Event.$on(`${this.componentName}:dismissAll`, (...args) => EventBus.$emit(`${this.componentName}:dismissAll`, ...args));
        },
        beforeDestroy() {
            EventBus.$off(`${this.componentName}:create`);
            EventBus.$off(`${this.componentName}:dismissAll`);
            Event.$off(`${this.componentName}:create`);
            Event.$off(`${this.componentName}:dismissAll`);
        }
    }
</script>

<style>
    div.alert .close {
        cursor: pointer;
        margin-top: -2px;
        margin-left: 12px;
        font-size: 17px;
    }
</style>
