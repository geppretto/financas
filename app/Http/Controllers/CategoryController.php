<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Despesa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $data = now()->addMonth();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $categories = Category::where('user_id', Auth::id())
            ->withCount('despesas')
            ->withSum('despesas', 'valor')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('mes', 'ano', 'categories'));
    }

    public function create()
    {
        $data = now()->addMonth();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $categories = Category::where('user_id', Auth::id())
            ->withCount('despesas')
            ->withSum('despesas', 'valor')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('mes', 'ano', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function edit($id)
    {
        $data = now()->addMonth();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $category = $this->findUserCategory($id);

        return view('categories.edit', compact('category', 'mes', 'ano'));
    }

    public function update(Request $request, $id)
    {
        $category = $this->findUserCategory($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $category = $this->findUserCategory($id);

        Despesa::where('user_id', Auth::id())
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoria excluída com sucesso. As despesas foram mantidas sem categoria.');
    }

    private function findUserCategory($id): Category
    {
        return Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
