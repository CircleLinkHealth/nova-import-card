<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use Tests\TestCase;

class EmailReplyParserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_email_reply_is_parsed_correctly()
    {
        $emailBody = "Test response

> On Jul 30, 2020, at 10:39 PM, CarePlan Manager <staging@careplanmanager.com> wrote:
>
> ﻿
> Hi, it's Dr. Demo's care program at Demo!
>
> Nurse Nurse just tried calling.
>
> Please reply with date(s) and time(s) you are available for Nurse Nurse to call you back.
>
> We'll forward your message to Nurse Nurse.
>
> Thanks!
> Dr. Demo's Office
>
> Demo
>";

        $visibleText = \EmailReplyParser\EmailReplyParser::parseReply($emailBody);
        self::assertEquals('Test response', $visibleText);
    }

    public function test_email_reply_is_parsed_correctly_2()
    {
        $emailBody = 'Please call me on following days/hrs

- Monday 1pm - 3pm
-Thursday 1pm - 3pm

Thanks';
        $visibleText = \EmailReplyParser\EmailReplyParser::read($emailBody);
        $text        = '';
        foreach ($visibleText->getFragments() as $fragment) {
            if ($fragment->isQuoted()) {
                continue;
            }
            $text .= $fragment->getContent();
        }

        self::assertStringContainsString('Please call me on following days/hrs', $text);
        self::assertStringContainsString('- Monday 1pm - 3pm', $text);
        self::assertStringContainsString('-Thursday 1pm - 3pm', $text);
        self::assertStringContainsString('Thanks', $text);
    }
}
