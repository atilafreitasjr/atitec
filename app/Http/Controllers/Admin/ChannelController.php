<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(): View
    {
        return view('admin.channels.index', [
            'channels' => Channel::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Channel::create($this->validated($request));

        return redirect()->route('admin.channels.index')->with('success', 'Canal criado.');
    }

    public function update(Request $request, Channel $channel): RedirectResponse
    {
        $channel->update($this->validated($request));

        return redirect()->route('admin.channels.index')->with('success', 'Canal atualizado.');
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        $channel->delete();

        return redirect()->route('admin.channels.index')->with('success', 'Canal excluído.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'string', 'max:40'],
            'label' => ['required', 'string', 'max:120'],
            'value' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'icon' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ]);
    }
}
