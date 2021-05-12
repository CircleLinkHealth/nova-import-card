<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Tests;

use CircleLinkHealth\Core\Utilities\JsonFixer;

class JsonFixerTest extends TestCase
{
    public static function assertJsonIsFixed(string $brokenJson)
    {
        self::assertTrue(is_json(JsonFixer::attemptFix($brokenJson)));
    }

    public function test_case_1()
    {
        $case = '{"Problems":[{"Name":"Mixed hyperlipidemia", "CodeType":"ICD10" , "Code":"E78.2" , "AddedDate":"05/07/2018" , "ResolveDate":"" , "Status":""}, {"Name":"Diverticulitis of large intestine without perforation or abscess without bleeding", "CodeType":"ICD10"';
        self::assertJsonIsFixed($case);
    }

    public function test_case_2()
    {
        $case = '{"Problems":[ {"Name":"Cellulitis and abscess", "CodeType":"ICD9" , "Code":"528.3" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"ALLERGIC RHINITIS NOS", "CodeType":"ICD9" , "Code":"477.9" , "AddedDate":"12/05/2012" , "ResolveDate":"" , "Status":""}, {"Name":"VACCIN FOR INFLUENZA", "CodeType":"ICD9" , "Code":"V04.81" , "AddedDate":"12/05/2012" , "ResolveDate":"" , "Status":""}, {"Name":"Depression with anxiety", "CodeType":"ICD9" , "Code":"300.4" , "AddedDate":"12/05/2012" , "ResolveDate":"" , "Status":""}, {"Name":"Sinusitis", "CodeType":"ICD9" , "Code":"473.9" , "AddedDate":"10/30/2014" , "ResolveDate":"" , "Status":""}, {"Name":"HTN (hypertension)", "CodeType":"ICD10" , "Code":"I10" , "AddedDate":"01/29/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Dysthymic disorder", "CodeType":"ICD10" , "Code":"F34.1" , "AddedDate":"01/29/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Acute sinusitis, unspecified", "CodeType":"ICD10" , "Code":"J01.90" , "AddedDate":"01/29/2016" , "ResolveDate":"" , "Status":""},]}';
        self::assertJsonIsFixed($case);
    }

    public function test_case_3()
    {
        $case = '{"Problems":[{"Name":"Nontoxic uninodular goiter", "CodeType":"ICD9" , "Code":"241.0" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Other chronic otitis externa", "CodeType":"ICD9" , "Code":"380.23" , "AddedDate":"08/28/2012" , "ResolveDate":"" , "Status":""}, {"Name":"Calculus of kidney", "CodeType":"ICD9" , "Code":"592.0" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Unspecified backache", "CodeType":"ICD9" , "Code":"724.5" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Spinal stenosis, other region other than cervical", "CodeType":"ICD9" , "Code":"724.09" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Other atopic dermatitis and related conditions", "CodeType":"ICD9" , "Code":"691.8" , "AddedDate":"08/28/2012" , "ResolveDate":"" , "Status":""}, {"Name":"Osteoarthrosis, unspecified whether generalized or localized, other specified sites", "CodeType":"ICD9" , "Code":"715.98" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Pain in joint, multiple sites", "CodeType":"ICD9" , "Code":"719.49" , "AddedDate":"01/07/2013" , "ResolveDate":"" , "Status":""}, {"Name":"Congenital spondylolisthesis", "CodeType":"ICD9" , "Code":"756.12" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Unspecified myalgia and myositis", "CodeType":"ICD9" , "Code":"729.1" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Unspecified osteoporosis", "CodeType":"ICD9" , "Code":"733.00" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Disorder of bone and cartilage, unspecified", "CodeType":"ICD9" , "Code":"733.90" , "AddedDate":"12/10/2012" , "ResolveDate":"" , "Status":""}, {"Name":"Unspecified abnormal mammogram", "CodeType":"ICD9" , "Code":"793.80" , "AddedDate":"" , "ResolveDate":"" , "Status":""}, {"Name":"Primary generalized (osteo)arthritis", "CodeType":"ICD10" , "Code":"M15.0" , "AddedDate":"12/08/2015" , "ResolveDate":"" , "Status":""}, {"Name":"Age-related osteoporosis without current pathological fracture", "CodeType":"ICD10" , "Code":"M81.0" , "AddedDate":"12/02/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Encounter for immunization", "CodeType":"ICD10" , "Code":"Z23" , "AddedDate":"12/02/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Rosacea", "CodeType":"ICD10" , "Code":"L71.9" , "AddedDate":"12/13/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Screening for colon cancer", "CodeType":"ICD10" , "Code":"Z12.11" , "AddedDate":"12/02/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Goiter", "CodeType":"ICD10" , "Code":"E04.9" , "AddedDate":"12/08/2015" , "ResolveDate":"" , "Status":""}, {"Name":"Wellness examination", "CodeType":"ICD10" , "Code":"Z01.89" , "AddedDate":"12/02/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Lumbar disc disease", "CodeType":"ICD10" , "Code":"M51.9" , "AddedDate":"12/08/2015" , "ResolveDate":"" , "Status":""}, {"Name":"Cervical disc disease", "CodeType":"ICD10" , "Code":"M50.90" , "AddedDate":"12/08/2015" , "ResolveDa]}';
        self::assertJsonIsFixed($case);
    }

    public function test_case_4()
    {
        $case = '{"Problems":[ {"Name":"DM Type II", "CodeType":"ICD9" , "Code":"250.00" , "AddedDate":"09/02/2014" , "ResolveDate":"" , "Status":""}, {"Name":"DMII OTH UNCNTRLD", "CodeType":"ICD9" , "Code":"250.82" , "AddedDate":"09/02/2014" , "ResolveDate":"" , "Status":""}, {"Name":"TRANSPLANT STATUS NOS", "CodeType":"ICD9" , "Code":"V42.9" , "AddedDate":"09/02/2014" , "ResolveDate":"" , "Status":""}, {"Name":"Abnormal weight loss", "CodeType":"ICD9" , "Code":"783.21" , "AddedDate":"09/02/2014" , "ResolveDate":"" , "Status":""}, {"Name":"Cellulitis of left foot", "CodeType":"ICD10" , "Code":"L03.116" , "AddedDate":"02/26/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Osteomyelitis of ankle or foot, left, acute", "CodeType":"ICD10" , "Code":"M86.172" , "AddedDate":"04/01/2016" , "ResolveDate":"" , "Status":""}, {"Name":"DM (diabetes mellitus), type 2, uncontrolled", "CodeType":"ICD10" , "Code":"E11.65" , "AddedDate":"05/03/2016" , "ResolveDate":"" , "Status":""}, {"Name":"Weakness", "CodeType":"ICD10" , "Code":"R53.1" , "AddedDate":"]}';
        self::assertJsonIsFixed($case);
    }

    public function test_case_5_json_breaks_due_to_double_quotes_in_string(){
        $case = '{"Medications":[{"Name":"Aspirin 81 MG Tablet Chewable","Sig":"1 tablet Orally Once a day 30 day(s)","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Eliquis 2.5 MG Tablet","Sig":"as directed Orally bid ","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"OneTouch Ultra - Strip","Sig":"TEST TWICE DAILY   50","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Complete Multivitamin/Mineral - tablet","Sig":" Orally  ","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Insulin Syringe 30G X 5/16 Miscellaneous","Sig":"USE UTS DAILY   30","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Insulin Syringe 30G X 5/16" 0.3 ML Miscellaneous","Sig":"USE UTS DAILY   30","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Oxybutynin Chloride 5 MG Tablet","Sig":"1 tablet Orally Twice a day 90","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Gabapentin 600 MG Tablet","Sig":"TAKE 1 TABLET BY MOUTH  TWICE DAILY   90","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Losartan Potassium 50 M]}';
        self::assertJsonIsFixed($case);
    }

    public function test_case6_json_breaks_due_to_double_quotes_in_string_and_at_the_end_of_value(){
        $case = '{"Medications":[{"Name":"Aspirin 81 MG Tablet Chewable","Sig":"1 tablet Orally Once a day 30 day(s)","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Eliquis 2.5 MG Tablet","Sig":"as directed Orally bid ","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"OneTouch Ultra - Strip","Sig":"TEST TWICE DAILY   50","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Complete Multivitamin/Mineral - tablet","Sig":" Orally  ","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Insulin Syringe 30G X 5/16 Miscellaneous","Sig":"USE UTS DAILY   30","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Insulin Syringe 30G X 5/16"","Sig":"USE UTS DAILY   30","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Oxybutynin Chloride 5 MG Tablet","Sig":"1 tablet Orally Twice a day 90","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Gabapentin 600 MG Tablet","Sig":"TAKE 1 TABLET BY MOUTH  TWICE DAILY   90","StartDate":"","StopDate":"","Status":"Taking"},{"Name":"Losartan Potassium 50 M]}';
        self::assertJsonIsFixed($case);
    }
}
