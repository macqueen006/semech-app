<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $orderVar = 'id';
            $order = $request->get('order', 'desc');

            // Determine order column
            if (str_contains($order, 'Alphabetical')) {
                $orderVar = 'name';
                $order = str_replace('Alphabetical', '', $order);
            }

            $limit = $request->get('limit', 20);

            // Use Scout only when there's actual search text (minimum 2 characters)
            if ($search && strlen($search) >= 2) {
                $query = Category::search($search)
                    ->query(function ($builder) use ($orderVar, $order) {
                        return $builder->withCount('posts')
                            ->orderBy($orderVar, $order);
                    });

                $categories = $limit == 0
                    ? $query->get()
                    : $query->paginate($limit);
            } else {
                // Use regular Eloquent query for better performance when no search
                $query = Category::withCount('posts')
                    ->orderBy($orderVar, $order);

                $categories = $limit == 0
                    ? $query->get()
                    : $query->paginate($limit);
            }

            return response()->json($categories);
        }

        return view('admin.categories.index');
    }

    public function create()
    {
        if (!auth()->user()->can('category-create')) {
            abort(403);
        }
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('category-create')) {
            abort(403);
        }

        // Generate slug first so we can validate it
        $slug = \Illuminate\Support\Str::slug($request->name);

        $validated = $request->validate([
            'name' => 'required|unique:categories,name',
            'backgroundColor' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'textColor' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ], [
            'name.required' => 'The category name is required.',
            'name.unique' => 'This category name has already been taken.',
        ]);

        // Check if slug already exists
        if (Category::where('slug', $slug)->exists()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'name' => ['This category name has already been taken.']
                ]
            ], 422);
        }

        // Add slug to validated data
        $validated['slug'] = $slug;

        Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!'
        ]);
    }

    public function edit(string $id)
    {
        if (!auth()->user()->can('category-edit')) {
            abort(403);
        }

        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->can('category-edit')) {
            abort(403);
        }

        $category = Category::findOrFail($id);

        // Generate slug first so we can validate it
        $slug = \Illuminate\Support\Str::slug($request->name);

        $validated = $request->validate([
            'name' => ['required', Rule::unique('categories', 'name')->ignore($category->id)],
            'backgroundColor' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'textColor' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ], [
            'name.required' => 'The category name is required.',
            'name.unique' => 'This category name has already been taken.',
        ]);

        // Check if slug already exists (excluding current category)
        if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'name' => ['This category name has already been taken.']
                ]
            ], 422);
        }

        // Add slug to validated data
        $validated['slug'] = $slug;

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!'
        ]);
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->can('category-delete')) {
            abort(403);
        }

        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!'
        ]);
    }
}
