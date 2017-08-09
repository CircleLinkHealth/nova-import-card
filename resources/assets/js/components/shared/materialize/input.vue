<script>
    import {mapGetters, mapActions} from 'vuex'
    import {errors} from '../../../store/getters'

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

        methods: {
            updateValue(value) {
                this.$emit('input', value)
            }
        }
    }
</script>

<template>
    <div>
        <input :id="name" name="name" :class="{invalid: errors.get(name)}" @input="updateValue($event.target.value)" @keydown="errors.clear(name)"
               :value="value">

        <label :class="{active: value}" :for="name" :data-error="errors.get(name)"
               data-success="">
            {{label}} <span v-if="required" class="red-text text-lighten-1">*</span>
        </label>
    </div>
</template>