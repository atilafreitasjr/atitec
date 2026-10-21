<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Support\BudgetEstimator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('site.home', [
            'featured' => Project::where('featured', true)->where('status', '!=', 'arquivado')->orderBy('sort_order')->take(6)->get(),
            'projects' => Project::where('status', '!=', 'arquivado')->orderBy('sort_order')->take(7)->get(),
            'liveProjectCount' => Project::whereIn('status', ['entregue', 'suporte'])->count(),
            'channels' => Channel::where('active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function sobre(): View
    {
        return view('site.sobre');
    }

    public function servico(string $servico): View
    {
        $data = [
            'desenvolvimento-sob-medida' => [
                'titulo' => 'Desenvolvimento de Softwares sob Medida',
                'subtitulo' => 'Sistemas web personalizados, do levantamento ao suporte contínuo.',
                'descricao' => 'Projetamos e construímos sistemas web sob medida em Laravel + MariaDB: ERPs leves, CRMs, portais, rastreabilidade e integrações. Código próprio, documentação e evolução contínua — sem amarras de plataforma.',
                'itens' => ['Levantamento e prototipação', 'Desenvolvimento Laravel + MariaDB', 'Painéis administrativos em AdminLTE', 'Integrações (WhatsApp, gateways, APIs)', 'Hospedagem, backups e SLA de suporte'],
            ],
            'consultoria-comercial' => [
                'titulo' => 'Consultoria Comercial e Tecnológica',
                'subtitulo' => 'Diagnóstico, processo comercial e tecnologia trabalhando juntos.',
                'descricao' => 'Ajudamos empresas a organizar o funil comercial, precificar, medir indicadores e escolher as ferramentas certas — do caderno ao dashboard.',
                'itens' => ['Diagnóstico comercial e de processos', 'Organização de funil e indicadores', 'Capacitação de equipes', 'Planos de evolução tecnológica', 'Acompanhamento por metas'],
            ],
            'automatizacao-atendimento' => [
                'titulo' => 'Automatização de Atendimentos',
                'subtitulo' => 'Chatbots, WhatsApp e IA para atender mais rápido e vender mais.',
                'descricao' => 'Automatizamos o atendimento com chatbots, respostas inteligentes, triagem de leads e integração com seus sistemas — sem perder o toque humano.',
                'itens' => ['Chatbots para WhatsApp e site', 'Triagem e qualificação de leads', 'Respostas automáticas com IA', 'Integração com CRM e ERP', 'Relatórios de atendimento'],
            ],
        ];

        abort_unless(isset($data[$servico]), 404);

        return view('site.servico', ['servico' => $data[$servico], 'slug' => $servico]);
    }

    public function portfolio(): View
    {
        return view('site.portfolio', [
            'projects' => Project::where('status', '!=', 'arquivado')->orderBy('sort_order')->get(),
        ]);
    }

    public function case(string $slug): View
    {
        $project = Project::where('slug', $slug)->where('status', '!=', 'arquivado')->firstOrFail();

        return view('site.case', [
            'project' => $project,
            'others' => Project::where('id', '!=', $project->id)->where('status', '!=', 'arquivado')->orderBy('sort_order')->take(3)->get(),
        ]);
    }

    public function contato(Request $request): View
    {
        return view('site.contato', [
            'channels' => Channel::where('active', true)->orderBy('sort_order')->get(),
            'prefill' => $this->contactPrefill($request),
        ]);
    }

    public function orcamento(Request $request): View
    {
        return view('site.orcamento', [
            'prefill' => $this->contactPrefill($request),
            'orcamento' => config('orcamento'),
        ]);
    }

    public function orcamentoStore(Request $request): RedirectResponse
    {
        $orcamento = config('orcamento');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'project_type' => ['required', Rule::in(array_keys($orcamento['types']))],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => [Rule::in(array_keys($orcamento['scope']))],
            'infrastructure' => ['nullable', 'array'],
            'infrastructure.*' => [Rule::in(array_keys($orcamento['infrastructure']))],
            'deadline' => ['nullable', Rule::in(array_keys($orcamento['deadlines']))],
            'budget_range' => ['nullable', 'string', 'max:80'],
            'details' => ['required', 'string', 'max:5000'],
        ]);

        $estimate = BudgetEstimator::estimate(
            $data['project_type'],
            $data['requirements'] ?? [],
            $data['infrastructure'] ?? [],
            $data['deadline'] ?? null,
        );

        unset($data['requirements'], $data['infrastructure'], $data['deadline']);

        Lead::create([
            ...$data,
            'requirements' => $estimate,
            'client_id' => $this->clientIdForLead($request, $data['email']),
            'status' => 'novo',
            'source' => 'site/orcamento',
        ]);

        return redirect()->route('site.orcamento')
            ->with('success', 'Pedido de orçamento recebido! A ATITEC retornará em breve pelos seus contatos.');
    }

    public function contatoStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'details' => ['required', 'string', 'max:5000'],
        ]);

        Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'client_id' => $this->clientIdForLead($request, $data['email']),
            'phone' => $data['phone'] ?? null,
            'details' => $data['details'],
            'project_type' => 'contato',
            'status' => 'novo',
            'source' => 'site/contato',
        ]);

        return redirect()->route('site.contato')
            ->with('success', 'Mensagem enviada com sucesso! Retornaremos em breve.');
    }

    public function cartao(Request $request): View
    {
        $channels = Channel::where('active', true)->orderBy('sort_order')->get()->keyBy('type');

        return view('site.cartao', [
            'channels' => $channels,
            'projects' => Project::where('status', '!=', 'arquivado')->orderBy('sort_order')->get(),
            'prefill' => $this->contactPrefill($request),
            'card' => [
                'name' => config('card.name'),
                'role' => config('card.role'),
                'company' => config('card.company'),
                'tagline' => config('card.tagline'),
            ],
        ]);
    }

    public function cartaoContatoStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        Lead::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? '',
            'client_id' => ! empty($data['email']) ? $this->clientIdForLead($request, $data['email']) : $request->user()?->client_id,
            'phone' => $data['phone'],
            'details' => $data['details'] ?? null,
            'project_type' => 'cartao',
            'status' => 'novo',
            'source' => 'site/cartao',
        ]);

        return redirect()->route('site.cartao')
            ->with('success', 'Contato registrado! Obrigado — retornarei em breve.');
    }

    public function cartaoVcard(): StreamedResponse
    {
        $name = config('card.name');
        $filename = str()->slug($name).'.vcf';

        $vcf = implode("\r\n", [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:Freitas Jr;Átila;;;',
            'FN:'.$name,
            'ORG:'.config('card.company'),
            'TITLE:'.config('card.role'),
            'TEL;TYPE=CELL,VOICE:'.config('card.phone'),
            'EMAIL:'.config('card.email'),
            'URL:'.config('app.url'),
            'END:VCARD',
        ]);

        return response()->streamDownload(function () use ($vcf) {
            echo $vcf;
        }, $filename, ['Content-Type' => 'text/vcard']);
    }

    /** Dados já conhecidos para reduzir digitação nos formulários públicos. */
    private function contactPrefill(Request $request): array
    {
        $user = $request->user();
        $client = $user?->client;

        return [
            'name' => $client?->name ?? $user?->name ?? '',
            'email' => $client?->email ?? $user?->email ?? '',
            'phone' => $client?->phone ?? '',
            'company' => $client?->company ?? '',
        ];
    }

    private function clientIdForLead(Request $request, string $email): ?int
    {
        $user = $request->user();

        if ($user?->role === 'cliente' && $user->client_id) {
            return (int) $user->client_id;
        }

        return Client::where('email', strtolower($email))->value('id');
    }
}
