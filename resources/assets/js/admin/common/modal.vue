<template>
    <div :class="className" v-if="show">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-wrapper" @click="close">
                    <div class="modal-container">
                        
                        <div class="modal-header" v-if="!noTitle">
                            <slot name="title" :info="info">
                                <div>{{title}}</div>
                            </slot>
                        </div>
            
                        <div class="modal-body">
                            <slot :info="info">
                                <div v-html="body"></div>
                            </slot>
                        </div>
            
                        <div class="modal-footer" v-if="!noFooter">
                            <div>
                                <slot name="footer" :info="info">
                                    <div>{{footer}}</div>
                                </slot>
                            </div>
                        </div>

                        <div class="modal-footer close-footer">
                            <button class="modal-default-button" @click="ok()">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
    /**
     * This is a generic modal component
     * 
     * It depends on an EventBus component called `Event` ... feel free to replace this
     * 
     * Trigger with:
     * 
     * ```
     * Event.$emit('modal:show', { title: 'My Modal Title', 'body': 'Hello Modal Everyone', footer: 'Cool Modal!' })
     * ```
     * 
     * You can pass a name prop to the modal to differentiate it from others.
     * 
     * In that case, the trigger script key becomes 'modal-<name>:show` where you replace `<name>` with whatever :name value you passed to the component
     * 
     * You can choose to not show title or footer by passing the `no-title` and `no-footer` props which take boolean values
     * 
     * You may specify the templates for title, body and footer via templates. 
     * 
     * For Body,
     * 
     * <template> ... body html here ... </template>
     * 
     * For title,
     * 
     * <template slot='title'> ... title html here ... </template>
     * 
     * For footer,
     * 
     * <template slot='footer'> ... footer html here ... </template>
     * 
     * For custom behavior, use the template slots and pass the :info prop to the modal component.
     * 
     * Within the template slots, you can use scope to props e.g.
     * 
     * <modal :no-title="true" :no-footer="true" :info="selectNursesModalInfo">
            <template scope="props">
                <select class="form-control" @change="props.info.onChange">
                    <option value="">Pick a Nurse</option>
                    <option value="1">Nurse N RN</option>
                    <option value="2">Kathryn Alchalabi RN</option>
                </select>
            </template>
       </modal>

     * Where [selectNursesModalInfo] is an object that contains the `onChange` callback
     */
    import { Event } from 'vue-tables-2'

    export default {
        name: 'modal',
        props: ['name', 'no-title', 'no-footer', 'info', 'class-name'],
        data() {
            return {
                title: '',
                body: '',
                footer: '',
                show: false
            }
        },
        methods: {
            close(e) {
                if (!e || (e.target && e.target.classList.contains('modal-wrapper'))) {
                    this.show = false;
                }
            },
            ok() {
                if (this.info && this.info.okHandler) this.info.okHandler();
                else this.close();
            }
        },
        computed: {
            
        },
        mounted() {
            Event.$on(`modal${this.name ? '-' + this.name : ''}:show`, (modal) => {
                modal = modal || {}
                this.title = modal.title || '';
                this.body = modal.body;
                this.footer = modal.footer || '';
                this.show = true;
            })

            Event.$on(`modal${this.name ? '-' + this.name : ''}:hide`, () => {
                this.close();
            })

            console.log(this.info)
        }
    }
</script>

<style>
    .modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: table;
        transition: opacity .3s ease;
    }
    
    .modal-wrapper {
        display: table-cell;
        vertical-align: middle;
    }
    
    .modal-container {
        width: 300px;
        margin: 0px auto;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        font-family: Helvetica, Arial, sans-serif;
    }
    
    .modal-header h3 {
        margin-top: 0;
        color: #42b983;
    }
    
    .modal-body {
        margin: 20px 0;
    }
    
    .modal-default-button {
        float: right;
    }
    
    /*
    * The following styles are auto-applied to elements with
    * transition="modal" when their visibility is toggled
    * by Vue.js.
    *
    * You can easily play with the modal transition by editing
    * these styles.
    */
    
    .modal-enter {
        opacity: 0;
    }
    
    .modal-leave-active {
        opacity: 0;
    }
    
    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }

    .modal-footer.close-footer {
        padding: 0px;
        border: none;
    }
</style>