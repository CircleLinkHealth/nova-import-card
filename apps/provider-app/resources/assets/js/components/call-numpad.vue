<template>
    <div :class="wrapperClass">
        <input class="form-control" type="text" placeholder="Numeric Keypad" @focus="numpadShow"/>
        <vue-touch-keyboard v-if="numpadVisible"
                            :layout="numpadCustomLayout"
                            :change="numpadChanged"
                            :cancel="numpadHide"
                            :accept="numpadDone"
                            :input="numpadInputElement"/>
    </div>
</template>

<script>

    import VueTouchKeyboard from "vue-touch-keyboard";

    require("vue-touch-keyboard/dist/vue-touch-keyboard.css");
    window.Vue.use(VueTouchKeyboard);

    export default {
        name: "call-numpad",
        props: {
            wrapperClass: {
                required: false,
                default: 'row'
            },
            onInput: Function,
        },
        data() {
            return {
                numpadRegex: new RegExp('[0-9]|[*]|#'),
                numpadVisible: false,
                numpadInputElement: null,
                numpadCustomLayout: {

                    _meta: {
                        "backspace": {func: "backspace", classes: "control"},
                        "accept": {func: "accept", text: "Hide", classes: "control featured"},
                        "zero": {key: "0", width: 130}
                    },

                    default: [
                        "1 2 3",
                        "4 5 6",
                        "7 8 9",
                        "* {zero} # {backspace} {accept}"
                    ]
                },
            }
        },
        methods: {
            numpadChanged: function (allInput, lastInput) {
                if (!lastInput || lastInput.length > 1 || !this.numpadRegex.test(lastInput)) {
                    return;
                }
                this.onInput(allInput, lastInput);
            },

            numpadDone: function (val) {
                this.numpadHide();
            },

            numpadShow: function (e) {
                this.numpadInputElement = e.target;
                this.numpadVisible = true;
            },

            numpadHide: function () {
                this.numpadVisible = false;
            },
        }
    }
</script>

<style scoped>
    .vue-touch-keyboard {
        margin-top: 3px;
    }
</style>
