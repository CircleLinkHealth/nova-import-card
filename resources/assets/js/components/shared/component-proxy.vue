<script>
    export default {
        props: {
            name: {
                type: String,
                required: true
            },
            props: {
                type: Object,
                default: () => {
                }
            }
        },

        render(createElem) {
            return createElem(this.name, {
                attrs: this.props
            });
        }
    }
</script>