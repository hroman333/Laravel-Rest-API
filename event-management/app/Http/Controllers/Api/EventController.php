<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user', 'attendees', 'attendee.user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Event::class, 'event');    }

    public function index()
    {
        $query = $this->loadRelationships(Event::query());

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');

        if (!$include){
            return false;
        }

        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations);
    }

    public function store(Request $request)
    {
        $event = Event::create([
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time'
            ]),
            'user_id' => $request->user()->id
        ]);

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return new EventResource(
            $this->loadRelationships($event)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
//        if (Gate::denies('update-event', $event)) {
//            abort(403, 'You are not authorized to update this event');
//        }

        $this->authorize('update-event', $event);

        $event->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time'
        ]));

        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response(status: 204);
    }
}
