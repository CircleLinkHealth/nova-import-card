<?php

namespace Tests\Unit;

use App\DatabaseNotification;
use App\Note;
use App\Notifications\NoteForwarded;
use App\Services\NoteService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NoteServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $provider;
    protected $note;
    protected $service;

    public function setUp()
    {
        parent::setUp();

        $this->provider = factory(User::class)->create();
        $this->note     = factory(Note::class)->create();
        $this->service  = new NoteService();
    }

    public function test_it_marks_unread_notifications_as_read()
    {
        $args = [
            'id'              => "test_1",
            'type'            => NoteForwarded::class,
            'notifiable_id'   => $this->provider->id,
            'notifiable_type' => User::class,
            'attachment_id'   => $this->note->id,
            'attachment_type' => Note::class,
            'read_at'         => null,
        ];

        DatabaseNotification::create($args);

        $this->assertDatabaseHas((new DatabaseNotification())->getTable(), $args);

        $this->service->markNoteAsRead($this->provider, $this->note);

        $this->assertDatabaseMissing((new DatabaseNotification())->getTable(), $args);

        $notification = DatabaseNotification::find($args['id']);

        $this->assertNotNull($notification->read_at);
    }
}
