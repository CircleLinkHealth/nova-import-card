<template>
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Home</li>
            </ol>
        </nav>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Library</li>
            </ol>
        </nav>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Library</a></li>
                <li class="breadcrumb-item active" aria-current="page">Data</li>
            </ol>
        </nav>
    </div>
</template>

<script>

    export default {
        name: "pusher-see-all-notifications",

        methods: {
            created() {
                let self = this;
                window.addEventListener('click', function (e) {
                    // close dropdown when clicked outside
                    if (!self.$el.contains(e.target)) {
                        self.dropDropdown = false;
                    }
                })
            },
        }
    }
</script>

<style scoped>

</style>