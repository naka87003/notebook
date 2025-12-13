<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $props = [
            'unreadNotificationCount' => Auth::user()->unreadNotifications->count()
        ];
        return Inertia::render('Tag', $props);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:20',
            'color' => 'nullable|string',
        ]);

        $tag = Tag::create([
            'name' => $request->name,
            'hex_color' => $request->color,
            'user_id' => Auth::id()
        ]);

        return response()->json(['tag_id' =>  $tag->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|max:20',
            'color' => 'nullable|string',
        ]);

        $tag->update([
            'name' => $request->name,
            'hex_color' => $request->color,
        ]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        DB::transaction(function () use ($tag) {
            $tag->notes()->update(['tag_id' => null]);
            $tag->delete();
        });

        return response()->json();
    }

    public function selectItems()
    {
        $items = Tag::where('user_id', Auth::id())
            ->withCount('notes')
            ->orderBy('notes_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $items;
    }

    public function datatableItems(Request $request)
    {

        $query = Tag::withCount([
            'notes',
            'notes as normal_count' => fn($q) => $q->where('status_id', 1),
            'notes as archived_count' => fn($q) => $q->where('status_id', 2),
        ])->where('user_id', Auth::id());

        if ($request->search) {
            $query->whereLike('name', "%{$request->search}%");
        }

        if ($request->sortBy) {
            switch ($request->sortBy[0]['key']) {
                case 'normal_count':
                case 'archived_count':
                    $query->orderByRaw("COALESCE({$request->sortBy[0]['key']}, 0) {$request->sortBy[0]['order']}");
                    break;
                default:
                    $query->orderBy($request->sortBy[0]['key'], $request->sortBy[0]['order']);
            }
        } else {
            $query->orderBy('notes_count', 'desc')->orderBy('updated_at', 'desc');
        }

        $count = $query->count();
        $items = $query->limit($request->itemsPerPage)->offset(($request->page - 1) * $request->itemsPerPage)->get();

        return response()->json(compact('items', 'count'));
    }
}
