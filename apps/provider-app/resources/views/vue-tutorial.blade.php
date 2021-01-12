<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>

<style>

    .red{
        color: red;
    }

    .green{
        color: green;
    }

</style>

<div id="root">

    <input v-model="num" type="text" name="input">
    <button v-bind:class="color" v-on:click="check">Do I work?</button>

</div>

<script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>

<script type="application/javascript">


    let app = new Vue({

        el: "#root",

        data: {

            num: 100,
            color: "red"

        },

        methods:{

            check() {

                this.color = (this.num > 1000) ? "green" : "red";

            }

        }

    });


</script>

</body>
</html>