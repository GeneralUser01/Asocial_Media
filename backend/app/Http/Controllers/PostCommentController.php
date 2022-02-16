<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCommentResource;
use App\Models\Entry;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostCommentController extends Controller
{
    // Authorization:
    //
    // We pass on the post that the comment is related to when checking
    // permissions for each method.
    //
    // For info about policy method names as related to controller method names
    // see:
    // https://laravel.com/docs/8.x/authorization#authorizing-resource-controllers
    //
    // For info about authorizing using multiple arguments see:
    // https://laravel.com/docs/8.x/authorization#supplying-additional-context

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Post $post)
    {
        $this->authorize('viewAny', [PostComment::class, $post]);

        // TODO: lower comments per page when we improve the frontend.
        return PostCommentResource::collection($post->comments()->orderBy('created_at', 'asc')->paginate(1000));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Post $post)
    {
        $this->authorize('create', [PostComment::class, $post]);

        return DB::transaction(function () use ($request, $post) {
            $user = $request->user();

            $comment = new PostComment($request->all());
            Str::limit($comment->content, 255);
            $comment->scrambled_content = $request->user()->scrambleText($comment->content, null);
            $comment->post_id = $post->id;
            $comment->user_id = $request->user()->id;
            $comment->save();

            Entry::createForUser($user, $comment);

            return new PostCommentResource($comment);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post, PostComment $comment)
    {
        $this->authorize('view', [$comment, $post]);

        return new PostCommentResource($comment);
    }

    public function like(Request $request, Post $post, PostComment $comment)
    {
        $this->authorize('like', [$comment, $post]);

        $request->user()->like($comment->entry()->first());
    }
    public function dislike(Request $request, Post $post, PostComment $comment)
    {
        $this->authorize('dislike', [$comment, $post]);

        $request->user()->dislike($comment->entry()->first());
    }
    public function unlike(Request $request, Post $post, PostComment $comment)
    {
        $request->user()?->removeLike($comment->entry()->first(), function ($like) use ($post, $comment) {
            $this->authorize('unlike', [$comment, $post, $like]);
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
    public function update(Request $request, Post $post, PostComment $comment)
    {
        $this->authorize('update', [$comment, $post]);

        $comment->update($request->all());

        return new PostCommentResource($comment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post, PostComment $comment)
    {
        $this->authorize('delete', [$comment, $post]);

        $comment->delete();

        return 204;
    }
}
