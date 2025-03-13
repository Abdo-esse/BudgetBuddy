<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Requests\TagRequest;
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
        $tag = Tag::create(['title' => $fields['title']]); 
    
        return response()->json(['message' => 'Tag créé avec succès', 'tag' => $tag], 201);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        return response()->json($tag, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\TagRequest  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update(TagRequest $request, Tag $tag)
    {
        $fields = $request->validated();
        $tag->update($fields);

        return response()->json(['tag' => $tag, 'message' => 'tag mis à jour avec succès'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tag $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(['message' => 'Le tag a été supprimé avec succès'], 200);
    }
}
