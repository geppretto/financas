<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PagamentoMensal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function create()
    {
        $data = now()->addMonth();

        $mes = $data->format('m');
        $ano = $data->format('Y');

        return view('categories.create', compact('mes', 'ano'));
    }

    public function store(Request $request)
    {
            Category::create([
                'name' => $request->name,
            ]);

        return redirect()->back()->with('success', 'Categoria criada com sucesso.');
    }
    // public function edit($id)
    // {
    //     $data = now()->addMonth();

    //     $mes = $data->format('m');
    //     $ano = $data->format('Y');

    //     $despesa = Despesa::find($id);
        
    //     return view('despesas.edit', compact('despesa', 'mes', 'ano'));
    // }
    // public function update(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required',
    //     ]);

    //     $despesa = Despesa::findOrFail($request->id);
    //     $despesa->update([
    //         'name' => $request->name,
    //         'company_id' => $request->company_id,
    //         'position' => $request->position,
    //         'email' => $request->email,
    //         'cel' => $request->cel,
    //         'date_request' => $request->date_request,
    //         'date_meeting' => $request->date_meeting,
    //         'hour' => $request->hour,
    //         'number_person' => $request->number_person,
    //     ]);

    //     return redirect()->route('despesas.edit')->with('success', 'Solicitação de Sala em Evento atualizada com sucesso');
    // }
}
