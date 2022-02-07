<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Post::orderBy('created_at', 'desc')->get();
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
        if (isset($request->image)) {
            $file = $request->file('image');

            $contents = $file->openFile()->fread($file->getSize());
            $post->image = $contents;
            $post->image_mime_type = $file->getClientMimeType();

            // Alternative for storing images to filesystem instead of database
            // $imageName = time().'.'.$request->image->extension();
            // $post->image = $request->image->storeAs('images', $imageName);
        }
        $post->save();
        return $post;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return $post;
    }
    public function showImage(Post $post)
    {
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

        return $post;
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
