<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Entry;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Set up authorization checks for the default "resource" controller
        // methods.
        //
        // For more info see:
        // https://laravel.com/docs/8.x/authorization#authorizing-resource-controllers
        $this->authorizeResource(Post::class, 'post');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO: lower posts per page when we improve the frontend.
        return PostResource::collection(Post::orderBy('created_at', 'desc')->paginate(1000));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $post = new Post($request->only('title', 'body'));

            Str::limit($post->title, 100);
            Str::limit($post->body, 512);

            $post->scrambled_body = $request->user()->scrambleText($post->body, null);

            $post->user_id = $request->user()->id;
            if (isset($request->image)) {
                $file = $request->file('image');

                $contents = $file->openFile()->fread($file->getSize());
                $post->image = $contents;
                $post->image_mime_type = $file->getClientMimeType();
            }
            $post->save();

            Entry::createForUser($request->user(), $post);

            return new PostResource($post);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return new PostResource($post);
    }
    public function showImage(Post $post)
    {
        $this->authorize('view', $post);

        if ($post->image === null || $post->image_mime_type === null) {
            abort(404);
        }

        return response($post->image)->header('Content-Type', $post->image_mime_type);
    }

    public function like(Request $request, Post $post)
    {
        $this->authorize('like', $post);

        $request->user()->like($post->entry()->first());
    }
    public function dislike(Request $request, Post $post)
    {
        $this->authorize('dislike', $post);

        $request->user()->dislike($post->entry()->first());
    }
    public function unlike(Request $request, Post $post)
    {
        $request->user()?->removeLike($post->entry()->first(), function ($like) use ($post) {
            $this->authorize('unlike', [$post, $like]);
            return true;
        });
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $post->update($request->all());

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return 204;
    }
}
