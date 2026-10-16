<?php

namespace App\Support;

/**
 * Estima o investimento de um pedido de orçamento a partir do perfil informado
 * no formulário (tipo, necessidades, infraestrutura e prazo). Os valores vêm
 * de config/orcamento.php; o cálculo no navegador usa as mesmas regras via
 * atributos data-* e este resultado é o que fica registrado no lead.
 */
class BudgetEstimator
{
    /**
     * @param  array<int, string>  $scopeKeys
     * @param  array<int, string>  $infrastructureKeys
     * @return array<string, mixed>
     */
    public static function estimate(?string $projectType, array $scopeKeys, array $infrastructureKeys, ?string $deadlineKey): array
    {
        $config = config('orcamento');
        $rate = (float) $config['hourly_rate'];

        $typeHours = (float) ($config['types'][$projectType] ?? 0);

        $scope = collect($scopeKeys)
            ->filter(fn ($key) => isset($config['scope'][$key]))
            ->map(fn ($key) => [
                'key' => $key,
                'label' => $config['scope'][$key]['label'],
                'hours' => (float) $config['scope'][$key]['hours'],
                'monthly' => (float) ($config['scope'][$key]['monthly'] ?? 0),
            ])
            ->map(fn (array $item) => [
                ...$item,
                'cost' => round($item['hours'] * $rate, 2),
            ])
            ->values();

        $infrastructure = collect($infrastructureKeys)
            ->filter(fn ($key) => isset($config['infrastructure'][$key]))
            ->map(fn ($key) => [
                'key' => $key,
                'label' => $config['infrastructure'][$key]['label'],
                'setup' => (float) ($config['infrastructure'][$key]['setup'] ?? 0),
                'monthly' => (float) ($config['infrastructure'][$key]['monthly'] ?? 0),
            ])
            ->values();

        $deadline = $deadlineKey !== null && isset($config['deadlines'][$deadlineKey])
            ? ['key' => $deadlineKey, ...$config['deadlines'][$deadlineKey]]
            : null;

        $hours = $typeHours + $scope->sum('hours');
        $multiplier = (float) ($deadline['multiplier'] ?? 1.0);
        $development = round($hours * $rate * $multiplier, 2);
        $setup = round($infrastructure->sum('setup'), 2);
        $monthly = round($scope->sum('monthly') + $infrastructure->sum('monthly'), 2);
        $total = round($development + $setup, 2);

        $roundTo = max(1, (int) ($config['range']['round_to'] ?? 100));
        $min = floor($total * (float) $config['range']['min_factor'] / $roundTo) * $roundTo;
        $max = ceil($total * (float) $config['range']['max_factor'] / $roundTo) * $roundTo;

        return [
            'hourly_rate' => $rate,
            'type' => $projectType,
            'type_hours' => $typeHours,
            'scope' => $scope->all(),
            'infrastructure' => $infrastructure->all(),
            'deadline' => $deadline,
            'hours' => round($hours, 2),
            'development' => $development,
            'setup' => $setup,
            'monthly' => $monthly,
            'total' => $total,
            'range' => ['min' => $min, 'max' => $max],
        ];
    }
}
