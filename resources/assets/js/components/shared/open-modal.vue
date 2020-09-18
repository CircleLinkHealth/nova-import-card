<template>
    <component-proxy v-if="name" :name="name" :props="props"></component-proxy>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {openModal} from '../../store/getters';

    export default {
        computed: Object.assign({},
            mapGetters({
                openModal: 'openModal'
            }),
            {
                name() {

                    const result = _.isNull(this.openModal.name) ? false : this.openModal.name;

                    if (result) {
                        $('body').css('overflow', 'hidden');
                    }
                    else {
                        $('body').css('overflow', '');
                    }

                    return result;
                },
                props() {
                    return _.isNil(this.openModal.props) ? {'show': true} : Object.assign(this.openModal.props, {'show': true})
                }
            }
        ),
    }
</script>