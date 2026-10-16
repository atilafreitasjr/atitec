<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\View\View;

class DocsController extends Controller
{
    /** Ordem e rótulos das páginas do Guia do Cliente. */
    public const PAGES = [
        'README' => 'Visão geral',
        'acesso' => 'Acesso e cadastro',
        'projetos' => 'Meus projetos',
        'orcamentos' => 'Orçamentos e propostas',
        'financeiro' => 'Financeiro e cobranças',
        'documentos' => 'Documentos',
        'mensagens' => 'Mensagens',
        'faq' => 'Perguntas frequentes',
    ];

    /** Documentação interna (AdminLTE): /docs e /docs/{page}. */
    public function show(string $page = 'README'): View
    {
        return view('docs.show', $this->content($page, url('docs')));
    }

    /** Central de ajuda no workspace do cliente: /cliente/ajuda. */
    public function ajuda(string $page = 'README'): View
    {
        return view('cliente.ajuda', $this->content($page, url('cliente/ajuda')));
    }

    /**
     * Lê o markdown do guia (resources/docs), converte para HTML e reescreve os
     * links entre páginas para o contexto informado (docs ou workspace).
     *
     * @return array{nav: array<string, string>, current: string, title: string, html: string}
     */
    private function content(string $page, string $base): array
    {
        $slug = preg_replace('/[^A-Za-z0-9_-]/', '', $page) ?: 'README';
        $path = resource_path('docs/'.$slug.'.md');

        abort_unless(is_file($path), 404);

        $html = (string) Str::markdown((string) file_get_contents($path));

        $html = (string) preg_replace_callback(
            '/href="(?!https?:\/\/)([A-Za-z0-9_-]+)\.md(#[^"]*)?"/',
            fn (array $match) => 'href="'.$base.'/'.$match[1].($match[2] ?? '').'"',
            $html,
        );

        return [
            'nav' => self::PAGES,
            'current' => $slug,
            'title' => self::PAGES[$slug] ?? Str::headline($slug),
            'html' => $html,
        ];
    }
}
