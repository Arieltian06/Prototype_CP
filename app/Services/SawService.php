<?php

namespace App\Services;

use DomainException;

class SawService
{
    /**
     * Pure SAW computation. Values remain full precision until formatted by the view.
     *
     * @param  array<int, array<string, mixed>>  $alternatives  Each item has id, nama and values keyed by criterion code.
     * @param  array<int, array<string, mixed>>  $criteria  Each item has kode_kriteria, jenis_kriteria and bobot.
     */
    public function calculate(array $alternatives, array $criteria): array
    {
        if ($criteria === []) {
            throw new DomainException('Kriteria SAW belum tersedia. Admin perlu mengatur kriteria dan bobot terlebih dahulu.');
        }

        $criteria = array_values($criteria);
        $codes = [];
        $weightSum = 0.0;
        foreach ($criteria as &$criterion) {
            $code = $criterion['kode_kriteria'] ?? null;
            if (! is_string($code) || trim($code) === '' || in_array($code, $codes, true)) {
                throw new DomainException('Setiap kriteria harus memiliki kode yang unik dan tidak kosong.');
            }
            if (! in_array($criterion['jenis_kriteria'] ?? '', ['benefit', 'cost'], true)) {
                throw new DomainException("Jenis kriteria {$code} harus benefit atau cost.");
            }
            $criterion['bobot'] = $this->nonNegativeNumber($criterion['bobot'] ?? null, "Bobot {$code}");
            $weightSum += $criterion['bobot'];
            $codes[] = $code;
        }
        unset($criterion);

        if (! is_finite($weightSum) || $weightSum <= 0) {
            throw new DomainException('Total bobot harus lebih besar dari nol. Silakan minta admin memperbaiki bobot SAW.');
        }
        foreach ($criteria as &$criterion) {
            $criterion['normalized_weight'] = $criterion['bobot'] / $weightSum;
        }
        unset($criterion);

        $decision = [];
        $normalized = [];
        foreach ($alternatives as $alternative) {
            $id = $alternative['id'] ?? null;
            if ((! is_string($id) && ! is_int($id)) || (string) $id === '' || array_key_exists($id, $decision)) {
                throw new DomainException('Setiap alternatif harus memiliki ID yang unik dan tidak kosong.');
            }
            $decision[$id] = [];
            foreach ($codes as $code) {
                $decision[$id][$code] = $this->nonNegativeNumber(
                    $alternative['values'][$code] ?? null, "Nilai {$code} untuk alternatif {$id}"
                );
            }
        }

        if ($alternatives === []) {
            return ['criteria' => $criteria, 'decision_matrix' => [], 'normalized_matrix' => [], 'ranking' => []];
        }

        $bounds = [];
        foreach ($codes as $code) {
            $column = array_column($decision, $code);
            $bounds[$code] = ['min' => min($column), 'max' => max($column)];
        }

        $ranking = [];
        foreach ($alternatives as $alternative) {
            $id = $alternative['id'];
            $normalized[$id] = [];
            $contributions = [];
            foreach ($criteria as $criterion) {
                $code = $criterion['kode_kriteria'];
                $value = $decision[$id][$code];
                if ($criterion['jenis_kriteria'] === 'benefit') {
                    $r = $bounds[$code]['max'] > 0 ? $value / $bounds[$code]['max'] : 0.0;
                } else {
                    // At min=0: a zero cost gets full credit, positive costs get 0.
                    // If all costs are 0, every alternative receives equal full credit.
                    $r = $value == 0.0 ? 1.0 : $bounds[$code]['min'] / $value;
                }
                $normalized[$id][$code] = $r;
                $contributions[$code] = $criterion['normalized_weight'] * $r;
            }

            $ranking[] = array_merge($alternative, [
                'values' => $decision[$id],
                'normalized' => $normalized[$id],
                'contributions' => $contributions,
                'score' => array_sum($contributions),
            ]);
        }

        usort($ranking, static function (array $a, array $b): int {
            return ($b['score'] <=> $a['score']) ?: strnatcmp((string) $a['id'], (string) $b['id']);
        });
        foreach ($ranking as $index => &$alternative) {
            $alternative['rank'] = $index + 1;
        }
        unset($alternative);

        return [
            'criteria' => $criteria,
            'decision_matrix' => $decision,
            'normalized_matrix' => $normalized,
            'ranking' => $ranking,
        ];
    }

    private function nonNegativeNumber(mixed $value, string $label): float
    {
        if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0) {
            throw new DomainException("{$label} harus berupa angka valid yang tidak negatif.");
        }

        return (float) $value;
    }
}
