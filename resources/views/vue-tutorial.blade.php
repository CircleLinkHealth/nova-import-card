<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>

<div id="root">

    <input v-model="num" type="text" name="input">
    <button v-bind:disabled="disabled" v-on:click="check">Do I work?</button>

</div>

<script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>

<script type="application/javascript">


    var app = new Vue({

        el: "#root",

        data: {

            color: 'red',
            num: 100,
            disabled: ''

        },

        methods:{

            check() {

                if(this.num > 100){

                    this.disabled = 'disabled'

                } else {

                    this.disabled = ''

                }

            }

        }

    });


</script>

</body>
</html>