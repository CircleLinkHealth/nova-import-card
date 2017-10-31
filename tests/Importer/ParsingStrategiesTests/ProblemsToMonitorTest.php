<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/1/16
 * Time: 9:40 PM
 */
class ProblemsToMonitorTest extends TestCase
{
    public $icd9hypertension = '{"reference":"#Condition1","reference_title":"\n                    BENIGN HYPERTENSION","date_range":{"start":null,"end":null},"name":null,"status":"Active","age":null,"code":null,"code_system":"2.16.840.1.113883.6.96","code_system_name":null,"translation":{"name":"BENIGN HYPERTENSION","code":"401.1","code_system":"2.16.840.1.113883.6.103","code_system_name":"ICD-9"},"comment":null  }';
    public $icd10highCholesterol = '{"reference":"#problem1","reference_title":"Mixed hyperlipidemia (E78.2) 10/12/2012","date_range":{"start":"2012-10-12T15:55:47Z","end":null},"name":"Mixed hyperlipidemia","status":null,"age":null,"code":"267434003","code_system":"2.16.840.1.113883.6.96","code_system_name":null,"translation":{"name":"Mixed hyperlipidemia","code":"E78.2","code_system":"2.16.840.1.113883.6.3","code_system_name":"ICD10"},"comment":null  }';
    public $snomedObesity = '{"reference":"#problem-66984","reference_title":"Body mass index 30+ - obesity","date_range":{"start":null,"end":null},"name":"Body mass index 30+ - obesity","status":"Active","age":null,"code":null,"code_system":null,"code_system_name":null,"translation":{"name":null,"code":"162864005","code_system":"2.16.840.1.113883.6.96","code_system_name":"SNOMED CT"},"comment":null  }';
    public $keywordsDiabetes = '{"reference":"#problem-66989","reference_title":"Disorder due to type 2 diabetes mellitus","date_range":{"start":null,"end":null},"name":"Disorder due to type 2 diabetes mellitus","status":"Active","age":null,"code":null,"code_system":null,"code_system_name":null,"translation":{"name":null,"code":"422014003","code_system":"2.16.840.1.113883.6.96","code_system_name":"SNOMED CT"},"comment":null  }';
    public $emptyProblem = '{"reference":"","reference_title":"","date_range":{"start":null,"end":null},"name":"null","status":"","age":null,"code":null,"code_system":null,"code_system_name":null,"translation":{"name":null,"code":"null","code_system":"","code_system_name":""},"comment":null  }';

    public function test_icd10_returns_expected()
    {
        $expected = '{"0":"High Cholesterol"}';

        $problems = $this->mockProblems(json_decode($this->icd10highCholesterol));
        $parser = $this->getParser();

        $this->assertEquals(
            json_decode($expected, true),
            $parser->parse($problems, new \App\Importer\Section\Validators\ImportAllItems())
        );
    }

    public function mockProblems($problems)
    {
        $problemsJson = new stdClass();

        is_array($problems)
            ? $problemsJson->problems = $problems
            : $problemsJson->problems[] = $problems;

        return $problemsJson;
    }

    public function getParser()
    {
        return new \App\CLH\CCD\Importer\ParsingStrategies\Problems\ToMonitor();
    }

    public function test_icd9_returns_expected()
    {
        $expected = '{"0":"Hypertension"}';

        $problems = $this->mockProblems(json_decode($this->icd9hypertension));
        $parser = $this->getParser();

        $this->assertEquals(
            json_decode($expected, true),
            $parser->parse($problems, new \App\Importer\Section\Validators\ImportAllItems())
        );
    }

    public function test_keywords_returns_expected()
    {
        $expected = '{"0":"Diabetes"}';

        $problems = $this->mockProblems(json_decode($this->keywordsDiabetes));
        $parser = $this->getParser();

        $this->assertEquals(
            json_decode($expected, true),
            $parser->parse($problems, new \App\Importer\Section\Validators\ImportAllItems())
        );
    }

    public function test_snomed_returns_expected()
    {
        $expected = '{"0":"Obesity"}';

        $problems = $this->mockProblems(json_decode($this->snomedObesity));
        $parser = $this->getParser();

        $this->assertEquals(
            json_decode($expected, true),
            $parser->parse($problems, new \App\Importer\Section\Validators\ImportAllItems())
        );
    }

    public function test_one_of_each_returns_expected()
    {
        $expected = '{"0":"Hypertension","1":"High Cholesterol","2":"Obesity", "3":"Diabetes"}';


        $problems = $this->mockProblems([
            json_decode($this->icd9hypertension),
            json_decode($this->icd10highCholesterol),
            json_decode($this->emptyProblem),
            json_decode($this->snomedObesity),
            json_decode($this->keywordsDiabetes)
        ]);
        $parser = $this->getParser();

        $this->assertEquals(
            json_decode($expected, true),
            $parser->parse($problems, new \App\Importer\Section\Validators\ImportAllItems())
        );
    }

    public function test_empty_problem_returns_nothing()
    {
        $expected = '{}';

        $problems = $this->mockProblems([
            json_decode($this->emptyProblem)
        ]);
        $parser = $this->getParser();

        $this->assertEquals(
            json_decode($expected, true),
            $parser->parse($problems, new \App\Importer\Section\Validators\ImportAllItems())
        );
    }
}
