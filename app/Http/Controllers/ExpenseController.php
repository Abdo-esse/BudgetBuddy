<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\ExpenseRequest;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/expenses",
     *     summary="Display a listing of expenses",
     *     tags={"Expenses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="A list of expenses",
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Auth::user()->expenses()->with('tags')->get(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/expenses",
     *     summary="Store a newly created expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Expense created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense créé avec succès")
     *         )
     *     )
     * )
     */
    public function store(ExpenseRequest $request)
    {
        $fields = $request->validated();
        $expense = $request->user()->expenses()->create($fields);
        return response()->json([ 'message' => 'Expense créé avec succès'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/{id}",
     *     summary="Display the specified expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the specified expense",
     *     )
     * )
     */
    public function show(Expense $expense)
    {
        Gate::authorize('modify', $expense);
        return response()->json($expense, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/expenses/{id}",
     *     summary="Update the specified expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense mis à jour avec succès")
     *         )
     *     )
     * )
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        Gate::authorize('modify', $expense);
        $fields = $request->validated();
        $expense->update($fields);
        return response()->json(['message' => 'Expense mis à jour avec succès'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/expenses/{id}",
     *     summary="Remove the specified expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expense a été supprimé avec succès")
     *         )
     *     )
     * )
     */
    public function destroy(Expense $expense)
    {
        Gate::authorize('modify', $expense);
        $expense->delete();
        return response()->json(['message' => 'Expense a été supprimé avec succès'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/expenses/{expenseId}/tags",
     *     summary="Attach tags to an expense",
     *     tags={"Expenses"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="expenseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tags", type="array", items=@OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tags successfully attached to the expense",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tags associés avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Expense non trouvé")
     *         )
     *     )
     * )
     */
    public function attachTags(Request $request, $expenseId)
    {
        $expense = Expense::find($expenseId);

        if (!$expense) {
            return response()->json(['error' => 'Expense non trouvé'], 404);
        }

        $validated = $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id'
        ]);

        $expense->tags()->syncWithoutDetaching($validated['tags']);

        return response()->json(['message' => 'Tags associés avec succès'], 200);
    }
}
