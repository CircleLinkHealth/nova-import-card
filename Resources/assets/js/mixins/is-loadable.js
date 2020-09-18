import Load from '../../../../../../resources/assets/js/util/load'

export default {
    mounted () {
        Load.call(this, () => this.init())
    }
}
