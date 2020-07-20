<template>
    <div class="col-xs-9 no-padding">
        <div class="input-group">
            <template v-if="true" v-for="number in phoneNumbers">
                <a class="input-group-addon">+1</a>
                <input name="number"
                       class="form-control" type="tel"
                       title="10-digit US Phone Number" placeholder="2345678901"
                       :value="number.number"
                       />
            </template>
            <template v-else>
                <input v-for="number in phoneNumbers" name="patient-unlisted-number"
                       maxlength="10" minlength="10"
                       class="form-control" type="tel"
                       title="10-digit US Phone Number" placeholder="2345678901"
                       :value="number.number"
                       />
            </template>
        </div>
    </div>
<!--    <div>-->
<!--       <ul>-->
<!--           <li v-for="number in phoneNumbers">-->
<!--              {{number.type}}: {{number.number}}-->
<!--           </li>-->
<!--       </ul>-->
<!--    </div>-->
</template>

<script>
    import LoaderComponent from "./loader";

    export default {
        name: "edit-patient-number",
        components: {
            loader: LoaderComponent,

        },
        props: [
            'phoneNumbers'
        ],

        data(){
            return {
                phoneNumber:''
            }
        }
    }
</script>

<style scoped>

</style>