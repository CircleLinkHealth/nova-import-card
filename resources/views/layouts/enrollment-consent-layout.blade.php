<div class="row">
    <div class="input-field col s12 m6">
        <label for="first_name">First Name</label>
        <input placeholder="Enter First Name..." id="first_name" name="first_name" type="text">
    </div>
    <div class="input-field col s12 m6">
        <label for="last_name">Last Name</label>
        <input placeholder="Enter Last Name..." id="last_name" name="last_name" type="text">
    </div>
</div>
<div class="row">
    <div class="input-field col s12 m6">
        <label class="active" for="dob">Date Of Birth</label>
        <input placeholder="XX-XX-XXXX" type="date" class="datepicker" name="dob" id="dob">
    </div>
    <div class="input-field col s12 m6">
        <label for="phone"><span v-bind:class="phoneValid">Phone Number</span></label>
        <input placeholder="XXX-XXX-XXXX" v-on:keyUp="checkPhone" v-model="phone" id="phone" type="text"
               name="phone">
    </div>
</div>
