<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\GroupResource;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Auth::user()->groups()->with('users')->get();
        return GroupResource::collection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGroupRequest $request)
    {
        $user = Auth::user();
        
        $group = Group::create([
            'name' => $request->name,
            'devise' => $request->devise,
        ]);

        $members = $request->members ?? [];
        $members[] = $user->id;
        
        $group->users()->attach(array_unique($members));

        return response()->json([
            'message' => 'Groupe créé avec succès !',
            'group' => new GroupResource($group), 
        ], 201); 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        Gate::authorize('view', $group); 

        return new GroupResource($group->load('users'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        Gate::authorize('view', $group);
        $group->delete();
        return response()->json(['message' => 'Group a été supprimé avec succès'], 200);
    }
}
