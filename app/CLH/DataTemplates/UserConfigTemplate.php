<?php

namespace App\CLH\DataTemplates;


class UserConfigTemplate extends DataTemplate
{
    public $status = 'Inactive';
    public $email;
    public $mrn_number;
    public $study_phone_number;
    public $home_phone_number;
    public $mobile_phone_number;
    public $work_phone_number;
    public $active_date = null;
    public $preferred_contact_time;
    public $preferred_contact_timezone;
    public $preferred_contact_method;
    public $preferred_contact_language;
    public $preferred_contact_location = null;
    public $prefix;
    public $gender;
    public $address;
    public $city;
    public $state;
    public $zip;
    public $birth_date;
    public $consent_date;
    public $daily_reminder_optin;
    public $daily_reminder_time;
    public $daily_reminder_areas;
    public $hospital_reminder_optin;
    public $hospital_reminder_time;
    public $hospital_reminder_areas;
    public $registration_date;
    public $care_team = [];
    public $send_alert_to = [];
    public $billing_provider;
    public $lead_contact;
    public $qualification;
    public $npi_number;
    public $specialty;

}