<script>
    import {mapGetters, mapActions} from 'vuex'
    import {errors} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/getters'
    import {clearErrors} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/actions'

    export default {
        props: {
            name: String,
            value: String,
            label: String,
            required: Boolean
        },
        computed: Object.assign(
            mapGetters({
                errors: 'errors'
            }),
        ),

        methods: Object.assign(
            mapActions(['clearErrors']),
            {
                updateValue(value) {
                    this.$emit('input', value)
                }
            }),
    }
</script>

<template>
    <div>
        <input :id="name" name="name" :class="{invalid: errors.get(name)}" @input="updateValue($event.target.value)"
               @keydown="clearErrors(name)"
               :value="value">

        <label :class="{active: value}" :for="name">
            {{label}} <span v-if="required" class="red-text text-lighten-1">*</span>
        </label>

        <p class="validation-error">{{errors.get(name)}}</p>
    </div>
</template>

<style>
    .validation-error {
        display: block;
        font-size: .75rem;
        transition: opacity .2s ease-out, color .2s ease-out;
        position: absolute;
        top: 2.5rem;
        color: #f44336;
    }
</style>