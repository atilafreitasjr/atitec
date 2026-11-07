@include('vendor.pagination.atitec', [
    'paginator' => $paginator,
    'elements' => $elements,
    'livewire' => true,
    'scrollTo' => $scrollTo ?? 'body',
])
