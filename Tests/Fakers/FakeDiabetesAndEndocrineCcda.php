<?php


namespace CircleLinkHealth\Eligibility\Tests\Fakers;


use CircleLinkHealth\SharedModels\Entities\Ccda;

class FakeDiabetesAndEndocrineCcda
{
    const ARGS = [
        'mrn' => 'fake-record-12345212',
        'referring_provider_name' => 'John Doe',
        'json' => '{"type":"csv-with-json","document":{"custodian":{"name":"Jimi Spage, MD"},"date":"","title":"","author":{"npi":"","name":{"prefix":null,"given":[],"family":null,"suffix":null},"address":{"street":[""],"city":"","state":"","zip":"","country":""},"phones":[{"type":"","number":""}]},"documentation_of":[{"provider_id":null,"name":{"prefix":null,"given":["Jimi Spage, MD"],"family":"","suffix":""},"phones":[{"type":"","number":""}],"address":{"street":[""],"city":"","state":"","zip":"","country":""}}],"legal_authenticator":{"date":null,"ids":[],"assigned_person":{"prefix":null,"given":[],"family":null,"suffix":null},"representedOrganization":{"ids":[],"name":null,"phones":[],"address":{"street":[],"city":null,"state":null,"zip":null,"country":null}}},"location":{"name":null,"address":{"street":[],"city":null,"state":null,"zip":null,"country":null},"encounter_date":null}},"allergies":[{"date_range":{"start":"","end":null},"name":null,"code":"","code_system":"","code_system_name":"","status":null,"severity":"","reaction":{"name":"","code":"","code_system":""},"reaction_type":{"name":"","code":"","code_system":"","code_system_name":""},"allergen":{"name":"Cephalexin Powder","code":"","code_system":"","code_system_name":""}},{"date_range":{"start":"","end":null},"name":null,"code":"","code_system":"","code_system_name":"","status":null,"severity":"","reaction":{"name":"","code":"","code_system":""},"reaction_type":{"name":"","code":"","code_system":"","code_system_name":""},"allergen":{"name":"Penicillins","code":"","code_system":"","code_system_name":""}},{"date_range":{"start":"","end":null},"name":null,"code":"","code_system":"","code_system_name":"","status":null,"severity":"","reaction":{"name":"","code":"","code_system":""},"reaction_type":{"name":"","code":"","code_system":"","code_system_name":""},"allergen":{"name":"Sulfa Antibiotics","code":"","code_system":"","code_system_name":""}}],"demographics":{"ids":{"mrn_number":"1111111111"},"name":{"prefix":null,"given":["Foo"],"family":"Bar","suffix":null},"dob":"01\/01\/1970","gender":"F","mrn_number":"1111111111","marital_status":"","address":{"street":["1234 Summer Street",""],"city":"NYC","state":"NY","zip":"12345","country":""},"phones":[{"type":"home","number":""},{"type":"primary_phone","number":"1231231234"},{"type":"mobile","number":""}],"email":null,"language":null,"race":null,"ethnicity":null,"religion":null,"birthplace":{"state":null,"zip":null,"country":null},"guardian":{"name":{"given":[],"family":null},"relationship":null,"relationship_code":null,"address":{"street":[],"city":null,"state":null,"zip":null,"country":null},"phone":{"home":null}},"patient_contacts":[],"provider":{"ids":[],"organization":null,"phones":[],"address":{"street":[],"city":null,"state":null,"zip":null,"country":null}}},"medications":[{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"hydrALAZINE HCl","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Fish Oil","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Nystatin","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"GNP Vitamin D Maximum Strength","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"4X Probiotic","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Vitamin C","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"traMADol HCl","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Chromium","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Meloxicam","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Evista","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Lipitor","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Lipitor","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Lumigan","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Spiriva HandiHaler","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Furosemide","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Rosuvastatin","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}}],"payers":[],"problems":[{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"250.80","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"272.2","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"496","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"401.9","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"E11.329","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"Z79.4","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"C18.9","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null}],"vitals":[{"date":null,"results":[{"name":null,"code":null,"code_system":null,"code_system_name":null,"value":null,"unit":null}]}]}',
    ];
    
    public static function make() {
        return new Ccda(self::ARGS);
    }
    
    public static function create(array $additionalArgs = []) {
        return Ccda::create(array_merge(self::ARGS, $additionalArgs));
    }
}