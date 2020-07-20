<template>
    <div class="phone-numbers">
        <div class="input-group">
            <template v-if="true" v-for="number in phoneNumbers">
                <div style="display: inline-flex; padding-right: 5px;">
                    <input name="type"
                           class="form-control phone-type" type="text"
                           title="Please select telephone type" placeholder="Home"
                           :value="number.type"
                           :disabled="inputDisabled"/>
                </div>
              <div style="display: inline-flex; padding-bottom: 10px;">
                  <span class="input-group-addon" style="padding-right: 26px; padding-top: 10px;">+1</span>
                  <input name="number"
                         class="form-control phone-number" type="tel"
                         title="10-digit US Phone Number" placeholder="2345678901"
                         :value="number.number"
                         :disabled="inputDisabled"/>
                  <a class="glyphicon glyphicon-pencil edit-phone"></a>
                  <a class="glyphicon glyphicon-remove remove-phone"></a>
              </div>
                <br>
            </template>

        </div>
    </div>
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
                phoneNumber:'',
                inputDisabled:true,
            }
        }
    }
</script>

<style scoped>
.phone-numbers{
    float: left;
}
    .phone-type{
        max-width: 80px;
        text-align: center;
    }
    .edit-phone{
        padding-left: 10px;
        padding-top: 5px;
        color: #50b2e2;
        cursor: pointer;
    }
    .remove-phone{
        padding-left: 10px;
        padding-top: 5px;
        color: red;
        cursor: pointer;
    }

</style>