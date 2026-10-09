<?php

namespace Tests\Feature;

use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\PenilaianTempat;
use App\Models\PerhitunganSaw;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use App\Services\RecommendationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private JenjangPendidikan $level;

    private MataPelajaran $subject;

    private Sekolah $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->level = JenjangPendidikan::where('nama_jenjang', 'SD')->firstOrFail();
        $this->subject = $this->level->mapel()->where('nama_mapel', 'IPA')->firstOrFail();
        $this->school = $this->level->sekolah()->firstOrFail();
    }

    private function recommend(?array $origin = null): array
    {
        return app(RecommendationService::class)->recommend($this->level, $this->subject, $this->school, $origin);
    }

    public function test_seeded_places_produce_complete_saw_result_from_school_coordinates(): void
    {
        $result = $this->recommend();
        $this->assertCount(6, $result['ranking']);
        $this->assertCount(3, $result['criteria']);
        $this->assertCount(6, $result['decision_matrix']);
        $this->assertSame(0, $result['skipped_count']);
        $this->assertSame($this->school->latitude, $result['origin']['latitude']);
        $this->assertSame($this->school->nama_sekolah, $result['origin']['label']);
        foreach ($result['ranking'] as $row) {
            $this->assertGreaterThanOrEqual(0, $row['jarak']);
            $this->assertEqualsWithDelta($row['score'], array_sum($row['contributions']), 1e-12);
            $this->assertSame($row['normalized'], $result['normalized_matrix'][$row['id']]);
            $this->assertTrue($row['is_prototype']);
        }
    }

    public function test_manual_criterion_is_computed_and_missing_values_are_explicitly_excluded(): void
    {
        $criterion = Kriteria::create([
            'kode_kriteria' => 'C4', 'nama_kriteria' => 'Fasilitas belajar',
            'jenis_kriteria' => 'benefit', 'bobot' => 1, 'sumber_nilai' => 'manual',
        ]);
        $place = TempatBelajar::firstOrFail();
        PenilaianTempat::create(['id_tempat' => $place->getKey(), 'id_kriteria' => $criterion->getKey(), 'nilai' => 80]);
        $result = $this->recommend();
        $this->assertCount(1, $result['ranking']);
        $this->assertSame($place->getKey(), $result['ranking'][0]['id']);
        $this->assertSame(80.0, $result['decision_matrix'][$place->getKey()]['C4']);
        $this->assertSame(1.0, $result['normalized_matrix'][$place->getKey()]['C4']);
        $this->assertEqualsWithDelta(0.5, $result['ranking'][0]['contributions']['C4'], 1e-12);
        $this->assertSame(5, $result['skipped_count']);
        $this->assertCount(5, $result['skipped_reasons']);
        $this->assertStringContainsString('C4', $result['skipped_reasons'][0]['reason']);
    }

    public function test_manual_zero_is_a_real_value_and_does_not_exclude_place(): void
    {
        $criterion = Kriteria::create([
            'kode_kriteria' => 'C4', 'nama_kriteria' => 'Biaya tambahan',
            'jenis_kriteria' => 'cost', 'bobot' => 1, 'sumber_nilai' => 'manual',
        ]);
        $place = TempatBelajar::firstOrFail();
        PenilaianTempat::create(['id_tempat' => $place->getKey(), 'id_kriteria' => $criterion->getKey(), 'nilai' => 0]);
        $result = $this->recommend();
        $this->assertCount(1, $result['ranking']);
        $this->assertSame(0.0, $result['decision_matrix'][$place->getKey()]['C4']);
        $this->assertSame(1.0, $result['normalized_matrix'][$place->getKey()]['C4']);
    }

    public function test_inactive_places_and_missing_selected_relations_are_not_candidates(): void
    {
        $places = TempatBelajar::orderBy('id_tempat')->get();
        $places[0]->update(['status' => 'nonaktif']);
        $places[1]->mapel()->detach($this->subject->getKey());
        $places[2]->jenjang()->detach($this->level->getKey());
        $result = $this->recommend();
        $this->assertCount(3, $result['ranking']);
        $this->assertSame($places->slice(3)->pluck('id_tempat')->sort()->values()->all(), collect($result['ranking'])->pluck('id')->sort()->values()->all());
    }

    public function test_invalid_place_coordinates_are_skipped_with_explanation(): void
    {
        TempatBelajar::firstOrFail()->update(['latitude' => null]);
        $result = $this->recommend();
        $this->assertCount(5, $result['ranking']);
        $this->assertSame(1, $result['skipped_count']);
        $this->assertStringContainsString('Koordinat', $result['skipped_reasons'][0]['reason']);
    }

    public function test_invalid_school_coordinate_gives_helpful_error(): void
    {
        $this->school->latitude = null;
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Koordinat lokasi asal');
        $this->recommend();
    }

    public function test_device_origin_can_replace_missing_school_coordinates(): void
    {
        $this->school->latitude = null;
        $result = $this->recommend(['latitude' => -3.8, 'longitude' => 102.2, 'label' => 'Lokasi saya']);
        $this->assertCount(6, $result['ranking']);
        $this->assertSame(-3.8, $result['origin']['latitude']);
        $this->assertSame('Lokasi saya', $result['origin']['label']);
    }

    public function test_subject_from_another_level_is_rejected(): void
    {
        $this->subject = MataPelajaran::where('id_jenjang', '!=', $this->level->getKey())->firstOrFail();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('sesuai dengan jenjang');
        $this->recommend();
    }

    public function test_no_active_places_is_a_valid_empty_result(): void
    {
        TempatBelajar::query()->update(['status' => 'nonaktif']);
        $result = $this->recommend();
        $this->assertSame([], $result['ranking']);
        $this->assertCount(3, $result['criteria']);
    }

    public function test_invalid_weight_configuration_fails_even_if_there_are_no_candidates(): void
    {
        TempatBelajar::query()->update(['status' => 'nonaktif']);
        Kriteria::query()->update(['bobot' => 0]);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Total bobot');
        $this->recommend();
    }

    public function test_database_weight_changes_recompute_ranking(): void
    {
        $places = TempatBelajar::orderBy('id_tempat')->get();
        TempatBelajar::whereNotIn('id_tempat', [$places[0]->getKey(), $places[1]->getKey()])->update(['status' => 'nonaktif']);
        $places[0]->mapel()->updateExistingPivot($this->subject->getKey(), ['nilai_kesesuaian' => 1]);
        $places[1]->mapel()->updateExistingPivot($this->subject->getKey(), ['nilai_kesesuaian' => 5]);
        $origin = ['latitude' => $places[0]->latitude, 'longitude' => $places[0]->longitude];

        Kriteria::query()->update(['bobot' => 0]);
        Kriteria::where('sumber_nilai', 'mapel')->update(['bobot' => 1]);
        $this->assertSame($places[1]->getKey(), $this->recommend($origin)['ranking'][0]['id']);

        Kriteria::where('sumber_nilai', 'mapel')->update(['bobot' => 0]);
        Kriteria::where('sumber_nilai', 'jarak')->update(['bobot' => 1]);
        $this->assertSame($places[0]->getKey(), $this->recommend($origin)['ranking'][0]['id']);
    }

    public function test_seed_is_idempotent_and_does_not_duplicate_relations(): void
    {
        $this->seed();
        $this->assertDatabaseCount('tempat_belajar', 6);
        $this->assertDatabaseCount('sekolah', 6);
        $this->assertDatabaseCount('mata_pelajaran', 18);
        $this->assertDatabaseCount('kriteria', 3);
        $this->assertDatabaseCount('tempat_mapel', 108);
        $this->assertDatabaseCount('tempat_jenjang', 18);
        $this->assertSame(0, DB::table('sekolah')->where('is_prototype', false)->count());
    }

    public function test_history_snapshot_survives_changes_and_deletion_of_source_data(): void
    {
        $result = $this->recommend();
        $history = PerhitunganSaw::create([
            'input' => ['jenjang' => $this->level->nama_jenjang, 'mapel' => $this->subject->nama_mapel],
            'hasil' => $result,
        ]);
        $original = $history->fresh()->hasil;
        Kriteria::query()->update(['bobot' => 99]);
        TempatBelajar::findOrFail($result['ranking'][0]['id'])->delete();
        $this->assertSame($original, $history->fresh()->hasil);
        $this->assertCount(6, $history->fresh()->hasil['ranking']);
    }
}
