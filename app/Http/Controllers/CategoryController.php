<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Despesa;
use App\Models\Receita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return $this->renderIndex();
    }

    public function create()
    {
        return $this->renderIndex();
    }

    public function edit($id)
    {
        return $this->renderIndex($this->findUserCategory($id));
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request) + ['user_id' => Auth::id()]);

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function update(Request $request, $id)
    {
        $category = $this->findUserCategory($id);

        $category->update($this->validated($request));

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $category = $this->findUserCategory($id);

        Despesa::where('user_id', Auth::id())
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        Receita::where('user_id', Auth::id())
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoria excluída com sucesso. Os lançamentos foram mantidos sem categoria.');
    }

    public function export()
    {
        $rows = $this->categoriesWithTotals();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Categoria', 'Tipo', 'Registros no mês', 'Total no mês', 'Teto mensal', '% do teto'], ';');

            foreach ($rows as $category) {
                fputcsv($out, [
                    $category->name,
                    $category->tipo === 'receita' ? 'Receita' : 'Despesa',
                    $category->registros,
                    number_format($category->total, 2, ',', ''),
                    $category->teto_mensal !== null ? number_format((float) $category->teto_mensal, 2, ',', '') : '',
                    $category->percentual !== null ? number_format($category->percentual, 1, ',', '') : '',
                ], ';');
            }

            fclose($out);
        }, 'categorias-' . now()->format('Y-m') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function renderIndex(?Category $editing = null)
    {
        $data = now();
        $mes = $data->format('m');
        $ano = $data->format('Y');

        $categories = $this->categoriesWithTotals();

        $comTeto = $categories->filter(fn ($c) => $c->percentual !== null);
        $tetoConsolidado = (float) $comTeto->sum(fn ($c) => (float) $c->teto_mensal);
        $orcamentoMedio = $comTeto->isEmpty() ? null : (float) $comTeto->avg('percentual');

        return view('categories.index', [
            'mes' => $mes,
            'ano' => $ano,
            'categories' => $categories,
            'editing' => $editing,
            'icones' => Category::ICONES,
            'cores' => Category::CORES,
            'tetoConsolidado' => $tetoConsolidado,
            'orcamentoMedio' => $orcamentoMedio,
        ]);
    }

    /**
     * Categorias do usuário com registros e total do mês corrente
     * (despesas ou receitas, conforme o tipo) e % consumido do teto.
     */
    private function categoriesWithTotals()
    {
        $mes = now()->format('m');
        $ano = now()->format('Y');

        $categories = Category::where('user_id', Auth::id())
            ->withCount([
                'despesas as despesas_registros' => fn ($q) => $q->doMes($mes, $ano),
                'receitas as receitas_registros' => fn ($q) => $q->doMes($mes, $ano),
            ])
            ->withSum([
                'despesas as despesas_total' => fn ($q) => $q->doMes($mes, $ano),
            ], 'valor')
            ->withSum([
                'receitas as receitas_total' => fn ($q) => $q->doMes($mes, $ano),
            ], 'valor')
            ->orderBy('name')
            ->get();

        return $categories->each(function (Category $category) {
            $isReceita = $category->tipo === 'receita';
            $category->registros = (int) ($isReceita ? $category->receitas_registros : $category->despesas_registros);
            $category->total = (float) ($isReceita ? $category->receitas_total : $category->despesas_total);
            $teto = (float) $category->teto_mensal;
            $category->percentual = $teto > 0 ? ($category->total / $teto) * 100 : null;
        });
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['despesa', 'receita'])],
            'icone' => ['required', Rule::in(Category::ICONES)],
            'cor' => ['required', Rule::in(Category::CORES)],
            'teto_mensal' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $validated['teto_mensal'] = isset($validated['teto_mensal']) && (float) $validated['teto_mensal'] > 0
            ? $validated['teto_mensal']
            : null;

        return $validated;
    }

    private function findUserCategory($id): Category
    {
        return Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
