<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Note;
use App\Services\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Note::where('user_id', Auth::id())
            ->whereIn('category_id', $request->category)
            ->where('status_id', $request->status);
        if ($request->search) {
            $query->where(function (Builder $query) use ($request) {
                $query->whereLike('title', "%{$request->search}%")
                    ->orWhereLike('content', "%{$request->search}%")
                    ->orWhereHas('tag', function (Builder $query) use ($request) {
                        $query->whereLike('name', "%{$request->search}%");
                    });
            });
        }
        if ($request->onlyLiked === 'true') {
            $query->whereHas('likes');
        }
        if ($request->key === 'starts_at') {
            $query->where('category_id', 3);
        }
        if ($request->tag) {
            $query->whereIn('tag_id', $request->tag);
        }
        if (is_numeric($request->offset)) {
            $query->offset($request->offset);
        }
        $notes = $query->with(['user', 'category', 'status', 'tag', 'likes'])->withCount(['likes', 'comments'])->orderBy($request->key, $request->order)->limit(20)->get();
        return response()->json($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => 'required_unless:category,3|max:' . config('notes.content_max_length'),
            'image' => 'nullable|image|max:10240',
            'title' => 'required_if:category,3|max:50',
            'public' => 'required|boolean',
            'category' => 'required|numeric',
            'tag' => 'numeric|nullable',
            'starts' => 'exclude_unless:category,3|date',
            'ends' => 'exclude_unless:category,3|date|after:starts'
        ]);

        $newImagePath = null;

        // 画像ファイルインスタンス取得
        $image = $request->file('image');

        if (isset($image)) {
            $newImagePath = ImageStorage::store($image, 'images/notes', 600);
        }

        Note::create([
            'title' => $request->title,
            'content' => $request->content,
            'public' => $request->public,
            'image_path' => $newImagePath,
            'category_id' => $request->category,
            'tag_id' => $request->tag,
            'user_id' => Auth::id(),
            'starts_at' => $request->starts,
            'ends_at' => $request->ends,
            'status_id' => 1
        ]);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        $request->validate([
            'content' => 'required_unless:category,3|max:' . config('notes.content_max_length'),
            'image' => 'nullable|image|max:10240',
            'title' => 'required_if:category,3|max:50',
            'public' => 'required|boolean',
            'category' => 'required|numeric',
            'tag' => 'numeric|nullable',
            'starts' => 'exclude_unless:category,3|date',
            'ends' => 'exclude_unless:category,3|date|after:starts'
        ]);

        // 更新対象
        $attributes = [
            'title' => $request->title,
            'content' => $request->content,
            'public' => $request->public,
            'category_id' => $request->category,
            'tag_id' => $request->tag,
            'starts_at' => $request->starts,
            'ends_at' => $request->ends
        ];

        // 画像ファイルインスタンス取得
        $image = $request->file('image');

        if (isset($image)) {
            $newImagePath = ImageStorage::store($image, 'images/notes', 600);
            // 新規画像Pathを更新対象にセット
            $attributes['image_path'] = $newImagePath;
        }

        $oldImagePath = $note->image_path;
        $note->update($attributes);
        if (isset($newImagePath) && $oldImagePath) {
            ImageStorage::disk()->delete($oldImagePath);
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();
        return response()->json();
    }

    /**
     * noteを1件取得
     */
    public function note(string $id)
    {
        $note = Note::with(['user', 'category', 'status', 'tag', 'likes'])->withCount(['likes', 'comments'])->find($id);
        return response()->json($note);
    }

    /**
     * タイムラインに表示するNotesを取得
     */
    public function posts(Request $request)
    {
        $query = Note::where('category_id', 1)->where('public', true);
        if ($request->search) {
            $query->where(function (Builder $query) use ($request) {
                $query->whereLike('title', "%{$request->search}%")
                    ->orWhereLike('content', "%{$request->search}%")
                    ->orWhereHas('user', function (Builder $query) use ($request) {
                        $query->whereLike('name', "%{$request->search}%");
                    })
                    ->orWhereHas('tag', function (Builder $query) use ($request) {
                        $query->whereLike('name', "%{$request->search}%");
                    });
            });
        }
        if ($request->onlyMyLiked === 'true') {
            $query->whereHas('likes', function (Builder $query) {
                $query->where('user_id', Auth::id());
            });
        }
        if ($request->user) {
            $query->where('user_id', $request->user);
        }

        if ($request->following === 'true') {
            $query->whereHas('user.followers', function (Builder $query) {
                $query->where('follows.follower_id', Auth::id());
            });
        }

        if (is_numeric($request->offset)) {
            $query->offset($request->offset);
        }
        $notes = $query->with(['user', 'category', 'status', 'tag', 'likes'])->withCount(['likes', 'comments'])->orderBy('updated_at', 'DESC')->limit(20)->get();
        return response()->json($notes);
    }

    /**
     * change status to `archived`
     */
    public function archive(Note $note)
    {
        $note->status_id = 2;
        $note->save();
        return response()->json();
    }

    /**
     * change status to `active`
     */
    public function retrieve(Note $note)
    {
        $note->status_id = 1;
        $note->save();
        return response()->json();
    }

    public function schedule()
    {
        $schedule = Note::where('user_id', Auth::id())->where('category_id', 3)->with(['user', 'category', 'status', 'tag'])->get();

        return response()->json($schedule);
    }

    public function likes(string $id, Request $request)
    {
        $query = Like::where('note_id', $id)->with('user');

        if (is_numeric($request->offset)) {
            $query->offset($request->offset);
        }

        $likes = $query->orderBy('created_at', 'DESC')->limit(20)->get();
        return response()->json($likes);
    }
}
