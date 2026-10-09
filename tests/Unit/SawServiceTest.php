<?php

namespace Tests\Unit;

use App\Services\SawService;
use DomainException;
use PHPUnit\Framework\TestCase;

class SawServiceTest extends TestCase
{
    private function criteria(): array
    {
        return [
            ['kode_kriteria' => 'C1', 'nama_kriteria' => 'Jenjang', 'jenis_kriteria' => 'benefit', 'bobot' => 30],
            ['kode_kriteria' => 'C2', 'nama_kriteria' => 'Mapel', 'jenis_kriteria' => 'benefit', 'bobot' => 45],
            ['kode_kriteria' => 'C3', 'nama_kriteria' => 'Jarak', 'jenis_kriteria' => 'cost', 'bobot' => 25],
        ];
    }

    private function alternatives(): array
    {
        return [
            ['id' => 1, 'nama' => 'A', 'values' => ['C1' => 5, 'C2' => 3, 'C3' => 4]],
            ['id' => 2, 'nama' => 'B', 'values' => ['C1' => 4, 'C2' => 5, 'C3' => 2]],
            ['id' => 3, 'nama' => 'C', 'values' => ['C1' => 3, 'C2' => 4, 'C3' => 1]],
        ];
    }

    public function test_known_saw_fixture_normalizes_weights_and_ranks_without_rounding(): void
    {
        $result = (new SawService)->calculate($this->alternatives(), $this->criteria());
        $this->assertEqualsWithDelta(1.0, array_sum(array_column($result['criteria'], 'normalized_weight')), 1e-12);
        $this->assertEqualsWithDelta(0.3, $result['criteria'][0]['normalized_weight'], 1e-12);
        $this->assertSame([2, 3, 1], array_column($result['ranking'], 'id'));
        $this->assertSame([1, 2, 3], array_column($result['ranking'], 'rank'));
        $this->assertEqualsWithDelta(0.815, $result['ranking'][0]['score'], 1e-12);
        $this->assertEqualsWithDelta(0.790, $result['ranking'][1]['score'], 1e-12);
        $this->assertEqualsWithDelta(0.6325, $result['ranking'][2]['score'], 1e-12);
        $this->assertEqualsWithDelta($result['ranking'][0]['score'], array_sum($result['ranking'][0]['contributions']), 1e-12);
        $this->assertSame(['C1' => 5.0, 'C2' => 3.0, 'C3' => 4.0], $result['decision_matrix'][1]);
    }

    public function test_benefit_uses_column_maximum_and_cost_uses_column_minimum(): void
    {
        $result = (new SawService)->calculate($this->alternatives(), $this->criteria());
        $this->assertEqualsWithDelta(4 / 5, $result['normalized_matrix'][2]['C1'], 1e-12);
        $this->assertEqualsWithDelta(3 / 5, $result['normalized_matrix'][1]['C2'], 1e-12);
        $this->assertEqualsWithDelta(1 / 4, $result['normalized_matrix'][1]['C3'], 1e-12);
        $this->assertEqualsWithDelta(1 / 2, $result['normalized_matrix'][2]['C3'], 1e-12);
    }

    public function test_changing_weights_changes_ranking(): void
    {
        $criteria = $this->criteria();
        $criteria[0]['bobot'] = 10;
        $criteria[1]['bobot'] = 10;
        $criteria[2]['bobot'] = 80;
        $result = (new SawService)->calculate($this->alternatives(), $criteria);
        $this->assertSame(3, $result['ranking'][0]['id']);
    }

    public function test_zero_benefit_column_contributes_zero(): void
    {
        $result = (new SawService)->calculate([
            ['id' => 1, 'values' => ['C1' => 0]], ['id' => 2, 'values' => ['C1' => 0]],
        ], [$this->criteria()[0]]);
        $this->assertSame(0.0, $result['ranking'][0]['score']);
        $this->assertSame(0.0, $result['normalized_matrix'][2]['C1']);
    }

    public function test_zero_cost_gets_full_credit_and_positive_cost_gets_zero_when_minimum_is_zero(): void
    {
        $result = (new SawService)->calculate([
            ['id' => 1, 'values' => ['C3' => 0]], ['id' => 2, 'values' => ['C3' => 5]],
        ], [$this->criteria()[2]]);
        $this->assertSame(1.0, $result['normalized_matrix'][1]['C3']);
        $this->assertSame(0.0, $result['normalized_matrix'][2]['C3']);
    }

    public function test_all_zero_cost_column_gives_equal_full_credit(): void
    {
        $result = (new SawService)->calculate([
            ['id' => 1, 'values' => ['C3' => 0]], ['id' => 2, 'values' => ['C3' => 0]],
        ], [$this->criteria()[2]]);
        $this->assertSame([1.0, 1.0], array_column($result['ranking'], 'score'));
    }

    public function test_exact_ties_use_ascending_id_independent_of_input_order(): void
    {
        $result = (new SawService)->calculate([
            ['id' => 10, 'values' => ['C1' => 5]], ['id' => 2, 'values' => ['C1' => 5]],
        ], [$this->criteria()[0]]);
        $this->assertSame([2, 10], array_column($result['ranking'], 'id'));
    }

    public function test_empty_alternatives_keep_criteria_and_empty_matrices(): void
    {
        $result = (new SawService)->calculate([], $this->criteria());
        $this->assertSame([], $result['ranking']);
        $this->assertSame([], $result['decision_matrix']);
        $this->assertSame([], $result['normalized_matrix']);
        $this->assertCount(3, $result['criteria']);
    }

    public function test_missing_criteria_are_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([], []);
    }

    public function test_zero_total_weight_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([], [['kode_kriteria' => 'C1', 'jenis_kriteria' => 'benefit', 'bobot' => 0]]);
    }

    public function test_negative_weight_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([], [['kode_kriteria' => 'C1', 'jenis_kriteria' => 'benefit', 'bobot' => -1]]);
    }

    public function test_non_numeric_weight_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([], [['kode_kriteria' => 'C1', 'jenis_kriteria' => 'benefit', 'bobot' => 'invalid']]);
    }

    public function test_non_finite_weight_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([], [['kode_kriteria' => 'C1', 'jenis_kriteria' => 'benefit', 'bobot' => INF]]);
    }

    public function test_missing_alternative_value_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([['id' => 1, 'values' => ['C1' => 3]]], $this->criteria());
    }

    public function test_negative_alternative_value_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([['id' => 1, 'values' => ['C1' => -2]]], [$this->criteria()[0]]);
    }

    public function test_duplicate_criteria_are_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new SawService)->calculate([], [$this->criteria()[0], $this->criteria()[0]]);
    }
}
