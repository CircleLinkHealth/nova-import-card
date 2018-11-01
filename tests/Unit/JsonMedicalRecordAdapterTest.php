<?php

namespace Tests\Unit;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Services\Eligibility\Adapters\JsonMedicalRecordAdapter;
use Tests\TestCase;

class JsonMedicalRecordAdapterTest extends TestCase
{
    const VALID_JSON = '{"patient_id":"1234","last_name":"Bar","first_name":"Foo","middle_name":"","date_of_birth":"1900-01-20 00:00:00.0","address_line_1":"123 Summer Street","address_line_2":"","city":"NYC","state":"NY","postal_code":"12345","primary_phone":"(201) 281-9204","cell_phone":"","preferred_provider":"","last_visit":"","insurance_plans":{"primary":{"plan":"Test Insurance","group_number":"","policy_number":"TEST1234","insurance_type":"Medicaid"},"secondary":{"plan":"Test Medicare","group_number":"","policy_number":"123455","insurance_type":"Medicare"}},"problems":[{"name":"Chronic Obstructive Pulmonary Disease","code_type":"ICD9","code":"496","start_date":"07/30/2013"},{"name":"Solar Dermatitis","code_type":"ICD9","code":"692.74","start_date":"07/12/2013"},{"name":"Hypertension","code_type":"ICD9","code":"401.9","start_date":"08/21/2014"}],"medications":[{"name":"Avinza 30 mg oral capsule, ER multiphase 24 hr","sig":"take 1 capsule by oral route daily for 30 days","startdate":"2014-03-11"},{"name":"Feosol 45 mg oral tablet","sig":"take 1 tablet by oral route daily for 90 days","startdate":"2017-03-31"},{"name":"morphine 120 mg oral capsule, ER multiphase 24 hr","sig":"take 1 capsule (120 mg) and sprinkle entire contents on a small amount of applesauce then immediately take by oral route once daily ; do not","startdate":"2014-08-22"},{"name":"promethazine-codeine 6.25-10 mg/5 mL oral syrup","sig":"take 5 milliliters by oral route every 6 hours for 10 days","startdate":"2013-07-30"},{"name":"Medrol (Pak) 4 mg oral tablets,dose pack","sig":"take as directed for 30 days","startdate":"2013-03-22"}],"allergies":[{"name":"Animal Dander"},{"name":"Lipitor"},{"name":"Lyrica"}]}';

    public function test_validation_success()
    {
        $data = self::VALID_JSON;

        $adapter = new JsonMedicalRecordAdapter($data);

        $this->assertTrue($adapter->isValid());
    }

    public function test_validation_fails_if_not_json()
    {
        $data = "this is definitely not json";

        $adapter = new JsonMedicalRecordAdapter($data);

        $this->assertFalse($adapter->isValid());
    }

    public function test_first_or_create_eligibility_job()
    {
        $data = self::VALID_JSON;

        $batch = factory(EligibilityBatch::class)->create();

        $adapter = new JsonMedicalRecordAdapter($data);
        $job     = $adapter->createEligibilityJob($batch);

        $this->assertTrue(is_a($job, EligibilityJob::class));
    }

    public function test_validation_fails_if_problems_only_contains_single_empty_problem()
    {
        $data = '{"patient_id":"1234","last_name":"Bar","first_name":"Foo","middle_name":"","date_of_birth":"1900-01-20 00:00:00.0","address_line_1":"123 Summer Street","address_line_2":"","city":"NYC","state":"NY","postal_code":"12345","primary_phone":"(201) 281-9204","cell_phone":"","preferred_provider":"","last_visit":"","insurance_plans":{"primary":{"plan":"Test Insurance","group_number":"","policy_number":"TEST1234","insurance_type":"Medicaid"},"secondary":{"plan":"Test Medicare","group_number":"","policy_number":"123455","insurance_type":"Medicare"}},"problems":[{"name":"","code_type":"","code":"","start_date":""}],"allergies":[{"name":"Animal Dander"},{"name":"Lipitor"},{"name":"Lyrica"}]}';

        $adapter = new JsonMedicalRecordAdapter($data);

        $this->assertFalse($adapter->isValid());
    }

    public function test_validation_fails_if_problems_only_contains_many_empty_problems()
    {
        $data = '{"patient_id":"1234","last_name":"Bar","first_name":"Foo","middle_name":"","date_of_birth":"1900-01-20 00:00:00.0","address_line_1":"123 Summer Street","address_line_2":"","city":"NYC","state":"NY","postal_code":"12345","primary_phone":"(201) 281-9204","cell_phone":"","preferred_provider":"","last_visit":"","insurance_plans":{"primary":{"plan":"Test Insurance","group_number":"","policy_number":"TEST1234","insurance_type":"Medicaid"},"secondary":{"plan":"Test Medicare","group_number":"","policy_number":"123455","insurance_type":"Medicare"}},"problems":[{"name":"","code_type":"","code":"","start_date":""},{"name":"","code_type":"","code":"","start_date":""},{"name":"","code_type":"","code":"","start_date":""}],"allergies":[{"name":"Animal Dander"},{"name":"Lipitor"},{"name":"Lyrica"}]}';

        $adapter = new JsonMedicalRecordAdapter($data);

        $this->assertFalse($adapter->isValid());
    }

    public function test_validation_passes_if_problems_contains_both_valid_and_empty_problems()
    {
        $data = '{"patient_id":"1234","last_name":"Bar","first_name":"Foo","middle_name":"","date_of_birth":"1900-01-20 00:00:00.0","address_line_1":"123 Summer Street","address_line_2":"","city":"NYC","state":"NY","postal_code":"12345","primary_phone":"(201) 281-9204","cell_phone":"","preferred_provider":"","last_visit":"","insurance_plans":{"primary":{"plan":"Test Insurance","group_number":"","policy_number":"TEST1234","insurance_type":"Medicaid"},"secondary":{"plan":"Test Medicare","group_number":"","policy_number":"123455","insurance_type":"Medicare"}},"problems":[{"name":"","code_type":"","code":"","start_date":""},{"name":"Solar Dermatitis","code_type":"ICD9","code":"692.74","start_date":"07/12/2013"},{"name":"Hypertension","code_type":"ICD9","code":"401.9","start_date":"08/21/2014"},{"name":"","code_type":"","code":"","start_date":""}],"allergies":[{"name":"Animal Dander"},{"name":"Lipitor"},{"name":"Lyrica"}]}';

        $adapter = new JsonMedicalRecordAdapter($data);

        $this->assertTrue($adapter->isValid());
    }
}
