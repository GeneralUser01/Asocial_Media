<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
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
        return PostResource::collection(Post::orderBy('created_at', 'desc')->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
        return new PostResource($post);
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

        return response($post->image)->header('Content-Type', $post->image_mime_type);
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
