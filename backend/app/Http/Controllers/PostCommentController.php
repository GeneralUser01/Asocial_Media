<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCommentResource;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;

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

        return PostCommentResource::collection($post->comments()->paginate());
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

        $comment = new PostComment($request->all());
        $post->scrambled_content = $request->user()->scrambleText($post->content, null);
        $comment->post_id = $post->id;
        $comment->user_id = $request->user()->id;
        $comment->save();
        return new PostCommentResource($comment);
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
