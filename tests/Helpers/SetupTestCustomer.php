<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 09/04/2018
 * Time: 8:07 PM
 */

namespace Helpers;


trait SetupTestCustomer
{
    //Put this in a trait to be used in tests. The objective is to create a test customer setup we can use for testing.
    //
    //It should create:
    //
    //A practice
    //A location
    //Patients with various ccm_statuses, careplan statuses, conditions and so on
    //A provider who is the billing provider for all patients

}