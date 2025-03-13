<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Requests\TagRequest;
use App\Http\Requests\StoreTagsRequest;
use App\Http\Requests\UpdateTagsRequest;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     summary="Display a listing of the tags",
     *     tags={"Tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="A list of tags",
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Tag::all());
    }

    /**
     * @OA\Post(
     *     path="/api/tags",
     *     summary="Store a newly created tag",
     *     tags={"Tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Travel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag créé avec succès")
     *         )
     *     )
     * )
     */
    public function store(TagRequest $request)
    {
        $fields = $request->validated();
        $tag = Tag::create(['title' => $fields['title']]);

        return response()->json(['message' => 'Tag créé avec succès', 'tag' => $tag], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/{id}",
     *     summary="Display the specified tag",
     *     tags={"Tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the specified tag"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public function show(Tag $tag)
    {
        return response()->json($tag, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     summary="Update the specified tag",
     *     tags={"Tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Updated Travel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tag mis à jour avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public function update(TagRequest $request, Tag $tag)
    {
        $fields = $request->validated();
        $tag->update($fields);

        return response()->json(['tag' => $tag, 'message' => 'Tag mis à jour avec succès'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     summary="Remove the specified tag",
     *     tags={"Tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le tag a été supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(['message' => 'Le tag a été supprimé avec succès'], 200);
    }
}
