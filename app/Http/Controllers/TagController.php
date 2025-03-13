<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Requests\StoreTagsRequest;
use App\Http\Requests\UpdateTagsRequest;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Tag::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\TagRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TagRequest $request)
    {
        $fields = $request->validated();
        $tag= Tag::create($fields->only('title'));
        return response()->json([ 'message' => 'Tag créé avec succès'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tag  $tags
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tags)
    {
        return response()->json($tags, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TagRequest  $request
     * @param  \App\Models\Tag  $tags
     * @return \Illuminate\Http\Response
     */
    public function update(TagRequest $request, Tag $tags)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tags  $tags
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tags $tags)
    {
        //
    }
}
