<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeRecourse;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Database\Seeders\AttendeeSeeder;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'update']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }

    public function index(Event $event)
    {
        $attendees = $this->loadRelationships(
            $event->attendees()->latest()
        );

        return AttendeeRecourse::collection(
            $attendees->paginate()
        );
    }

    public function store(Request $request, Event $event)
    {
        $attendee = $this->loadRelationships(
            $event->attendees()->create([
                'user_id' => 1
            ])
        );

        return new AttendeeRecourse($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource(
            $this->loadRelationships($attendee)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        $this->authorize('delete-attendee', [$event, $attendee]);
        $attendee->delete();

        return response(status: 204);
    }
}
