<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = $request->user()->categories()
            ->withSum([
                'transactions as total' => function ($query){
                    $query->where('is_active', true);
                }
            ])
            ->orderBy('name')
            ->get();
        return $this->sendResponse(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => $request->validated('name'),
            'type' => $request->validated('type'),
            'icon' => $request->validated('icon') ?? null,
            'is_active'=> $request->validated('is_active') ?? true,
        ]);
        return $this->sendResponse(new CategoryResource($category), 'Category added successfully',201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category, Request $request)
    {
        if($category->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        $data['category'] = new CategoryResource($category);
        $data['latest_transactions'] = $category->transactions()->where('user_id', $request->user()->id)->latest()->limit(3)->get();
        return $this->sendResponse($data,'Category retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        if($category->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        $category->update($request->validated());
        return $this->sendResponse(new CategoryResource($category),'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category, Request $request)
    {
        if($category->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        $category->transactions()->delete();
        $category->delete();
        return $this->sendResponse(null, 'Category and transactions related deleted successfully');
    }
}
