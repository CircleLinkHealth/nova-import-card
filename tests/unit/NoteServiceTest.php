<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Note;
use App\Notifications\NoteForwarded;
use App\Services\NoteService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class NoteServiceTest extends TestCase
{
    protected $note;
    protected $provider;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = factory(User::class)->create();
        $this->note     = factory(Note::class)->create();
        $this->service  = app(NoteService::class);
    }

    public function createNotification($id = null)
    {
        $args = [
            'id'              => $id ?? 'test_'.Str::random(5),
            'type'            => NoteForwarded::class,
            'notifiable_id'   => $this->provider->id,
            'notifiable_type' => User::class,
            'attachment_id'   => $this->note->id,
            'attachment_type' => Note::class,
            'read_at'         => null,
        ];

        return DatabaseNotification::create($args);
    }

    public function test_it_marks_unread_notifications_as_read()
    {
        $notification = $this->createNotification();

        $this->assertDatabaseHas((new DatabaseNotification())->getTable(), $notification->toArray());

        $this->service->markNoteAsRead($this->provider, $this->note);

        $this->assertDatabaseMissing((new DatabaseNotification())->getTable(), $notification->toArray());

        $notification = DatabaseNotification::find($notification->id);

        $this->assertNotNull($notification->read_at);
    }
}
