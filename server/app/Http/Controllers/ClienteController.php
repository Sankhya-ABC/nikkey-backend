<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page    = (int) $request->query('page', 1);
        $search  = trim($request->query('search'));

        $query = Cliente::with([
            'usuarios.tipoUsuario',
            'usuarios.departamento',
            'endereco',
            'bairro',
            'cidade.uf'
        ]);

        // 🔍 APLICA O SEARCH SE EXISTIR
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('razao_social', 'LIKE', "%{$search}%")
                ->orWhere('nome_fantasia', 'LIKE', "%{$search}%")
                ->orWhere('cnpj_cpf', 'LIKE', "%{$search}%");
            });
        }

        $clientes = $query->paginate($perPage, ['*'], 'page', $page);

        $clientesFormatados = $clientes->getCollection()->map(function ($cliente) {
            $user = $cliente->usuarios->first();

            return [
                'id' => $cliente->id,
                'razaoSocial' => $cliente->razao_social,
                'nomeFantasia' => $cliente->nome_fantasia,
                'cnpjCpf' => $cliente->cnpj_cpf,

                'validadeCertificado' => $cliente->validade_certificado
                    ? $cliente->validade_certificado->timestamp
                    : "",

                'tipoAtividade' => $cliente->tipo_atividade,
                'possuiContrato' => (bool) $cliente->tem_contrato,

                'logradouro'  => optional($cliente->endereco)->logradouro ?? "",
                'numero'      => $cliente->numero ?? "",
                'complemento' => $cliente->complemento ?? "",
                'bairro'      => optional($cliente->bairro)->nome ?? "",
                'cidade'      => optional($cliente->cidade)->nome ?? "",
                'estado'      => optional(optional($cliente->cidade)->uf)->sigla ?? "",
                'cep'         => $cliente->cep ?? "",

                'contato'  => $cliente->contato ?? "",
                'telefone' => $cliente->telefone ?? "",
                'email'    => $cliente->email ?? "",

                'nomeAcesso' => $user?->name ?? "",
                'emailAcesso' => $user?->email ?? "",
                'departamento' => $user?->departamento?->descricao ?? "",

                'ativo' => (bool) $cliente->ativo,
                'dataCadastro' => $cliente->created_at,
            ];
        });

        $clientes->setCollection($clientesFormatados);

        return response()->json([
            'data' => $clientes->items(),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'per_page'     => $clientes->perPage(),
                'total'        => $clientes->total(),
                'last_page'    => $clientes->lastPage(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // 🔹 Normalização básica
        $norm = fn ($v) => $v ? trim(preg_replace('/\s+/', ' ', $v)) : null;

        /** UF */
        $uf = null;
        if (!empty($data['estado'])) {
            $uf = \App\Models\Uf::firstOrCreate(
                ['sigla' => strtoupper($norm($data['estado']))],
                ['nome'  => strtoupper($norm($data['estado']))]
            );
        }

        /** Cidade */
        $cidade = null;
        if (!empty($data['cidade']) && $uf) {
            $cidade = \App\Models\Cidade::firstOrCreate([
                'nome'  => strtoupper($norm($data['cidade'])),
                'uf_id' => $uf->id
            ]);
        }

        /** Bairro */
        $bairro = null;
        if (!empty($data['bairro'])) {
            $bairro = \App\Models\Bairro::firstOrCreate([
                'nome' => strtoupper($norm($data['bairro']))
            ]);
        }

        /** Endereço */
        $endereco = null;
        if (!empty($data['logradouro'])) {
            $endereco = \App\Models\Endereco::firstOrCreate([
                'logradouro' => strtoupper($norm($data['logradouro']))
            ]);
        }

        /** Cliente */
        $cliente = Cliente::create([
            'razao_social'       => $norm($data['razaoSocial'] ?? null),
            'nome_fantasia'      => $norm($data['nomeFantasia'] ?? null),
            'cnpj_cpf'           => $norm($data['cnpjCpf'] ?? null),
            'validade_certificado' => $data['validadeCertificado']
                ? now()->createFromTimestamp($data['validadeCertificado'])
                : null,

            'tipo_atividade' => $norm($data['tipoAtividade'] ?? null),
            'tem_contrato'   => (bool) ($data['possuiContrato'] ?? false),

            'endereco_id' => $endereco?->id,
            'bairro_id'   => $bairro?->id,
            'cidade_id'   => $cidade?->id,

            'numero'      => $norm($data['numero'] ?? null),
            'complemento' => $norm($data['complemento'] ?? null),
            'cep'         => $norm($data['cep'] ?? null),

            'contato'  => $norm($data['contato'] ?? null),
            'telefone' => $norm($data['telefone'] ?? null),
            'email'    => $norm($data['email'] ?? null),

            'ativo' => true,
        ]);

        return response()->json(['id' => $cliente->id], 201);
    }

    public function update(Request $request, int $id)
    {
        $cliente = Cliente::findOrFail($id);

        $data = $request->all();
        $norm = fn ($v) => $v ? trim(preg_replace('/\s+/', ' ', $v)) : null;

        /** UF */
        $uf = null;
        if (!empty($data['estado'])) {
            $uf = \App\Models\Uf::firstOrCreate(
                ['sigla' => strtoupper($norm($data['estado']))],
                ['nome'  => strtoupper($norm($data['estado']))]
            );
        }

        /** Cidade */
        $cidade = null;
        if (!empty($data['cidade']) && $uf) {
            $cidade = \App\Models\Cidade::firstOrCreate([
                'nome'  => strtoupper($norm($data['cidade'])),
                'uf_id' => $uf->id
            ]);
        }

        /** Bairro */
        $bairro = null;
        if (!empty($data['bairro'])) {
            $bairro = \App\Models\Bairro::firstOrCreate([
                'nome' => strtoupper($norm($data['bairro']))
            ]);
        }

        /** Endereço */
        $endereco = null;
        if (!empty($data['logradouro'])) {
            $endereco = \App\Models\Endereco::firstOrCreate([
                'logradouro' => strtoupper($norm($data['logradouro']))
            ]);
        }

        $cliente->update([
            'razao_social'  => $norm($data['razaoSocial'] ?? $cliente->razao_social),
            'nome_fantasia' => $norm($data['nomeFantasia'] ?? $cliente->nome_fantasia),
            'cnpj_cpf'      => $norm($data['cnpjCpf'] ?? $cliente->cnpj_cpf),

            'validade_certificado' => $data['validadeCertificado']
                ? now()->createFromTimestamp($data['validadeCertificado'])
                : $cliente->validade_certificado,

            'tipo_atividade' => $norm($data['tipoAtividade'] ?? $cliente->tipo_atividade),
            'tem_contrato'   => (bool) ($data['possuiContrato'] ?? $cliente->tem_contrato),

            'endereco_id' => $endereco?->id,
            'bairro_id'   => $bairro?->id,
            'cidade_id'   => $cidade?->id,

            'numero'      => $norm($data['numero'] ?? $cliente->numero),
            'complemento' => $norm($data['complemento'] ?? $cliente->complemento),
            'cep'         => $norm($data['cep'] ?? $cliente->cep),

            'contato'  => $norm($data['contato'] ?? $cliente->contato),
            'telefone' => $norm($data['telefone'] ?? $cliente->telefone),
            'email'    => $norm($data['email'] ?? $cliente->email),

            'ativo' => isset($data['ativo']) ? (bool) $data['ativo'] : $cliente->ativo,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(int $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json(['success' => true]);
    }
}
