@include('vendor.pagination.atitec', [
    'paginator' => $paginator,
    'elements' => [],
    'livewire' => true,
    'scrollTo' => $scrollTo ?? 'body',
])
