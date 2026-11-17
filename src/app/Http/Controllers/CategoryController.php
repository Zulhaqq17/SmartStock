<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** MODULE 2: Product categories */
class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', [
            'categories' => Category::withCount('products')->orderBy('category_name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($request->validate($this->rules()));

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($request->validate($this->rules($category)));

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete this category because products are still assigned to it.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    private function rules(?Category $category = null): array
    {
        return [
            'category_name' => ['required', 'string', 'max:100', Rule::unique('categories', 'category_name')->ignore($category)],
            'description' => ['nullable', 'string', 'max:1000'],
            'expiry_alert_days' => ['required', 'integer', 'min:0', 'max:3650'],
        ];
    }
}
