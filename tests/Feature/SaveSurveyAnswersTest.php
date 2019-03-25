<?php

namespace Tests\Feature;

use App\InvitationLink;
use App\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SaveSurveyAnswersTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /** @test */
    public function survey_data_can_be_saved_in_answers_table()
    {
        factory(InvitationLink::class, 1)->create([
            'link_token' => '3cbddd697b491be7469dc960e085cea64e74f4929b74e9611ad6398568452ef3',
        ]);

        factory(Patient::class)->create();

        $this->call('POST', route('saveSurveyAnswer'),
            [
                'link_token'         => '3cbddd697b491be7469dc960e085cea64e74f4929b74e9611ad6398568452ef3',
                'survey_instance_id' => '4',
                'question_id'        => '3',
                'question_answer_id' => '1',
                'value'              => 'YaBaDaBaDoo',
            ]);

        $this->assertDatabaseHas('answers', [
            'survey_instance_id' => '4',
            'question_id'        => '3',
            'question_answer_id' => '1',
            'value'              => 'YaBaDaBaDoo',
        ]);

    }
}
