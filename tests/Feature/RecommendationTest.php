<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\PerhitunganSaw;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function selection(): array
    {
        $mapel = MataPelajaran::firstOrFail();

        return [
            'id_jenjang' => $mapel->id_jenjang,
            'id_mapel' => $mapel->getKey(),
            'id_sekolah' => Sekolah::where('id_jenjang', $mapel->id_jenjang)->firstOrFail()->getKey(),
        ];
    }

    public function test_public_pages_and_place_detail_are_available(): void
    {
        foreach (['/', '/tentang', '/tempat-belajar', '/rekomendasi', '/admin/login'] as $path) {
            $this->get($path)->assertOk()->assertSee('EduPesisir');
        }
        $this->get('/tempat-belajar/'.TempatBelajar::first()->getKey())->assertOk();
    }

    public function test_required_fields_and_cross_level_inputs_are_rejected(): void
    {
        $this->post('/rekomendasi', [])->assertSessionHasErrors(['id_jenjang', 'id_mapel', 'id_sekolah']);
        $data = $this->selection();
        $data['id_jenjang'] = JenjangPendidikan::where('id_jenjang', '!=', $data['id_jenjang'])->first()->getKey();
        $this->post('/rekomendasi', $data)->assertSessionHasErrors(['id_mapel', 'id_sekolah']);
        $this->assertDatabaseCount('perhitungan_saw', 0);
    }

    public function test_backend_calculates_and_persists_a_transparent_saw_snapshot(): void
    {
        $response = $this->post('/rekomendasi', $this->selection());
        $response->assertSessionHasNoErrors();
        $snapshot = PerhitunganSaw::firstOrFail();
        $response->assertRedirect(route('recommendation.show', $snapshot));
        $result = $snapshot->hasil;
        $this->assertNotEmpty($result['ranking']);
        $this->assertEqualsWithDelta(1.0, array_sum(array_column($result['criteria'], 'normalized_weight')), 1e-12);
        $previous = INF;
        foreach ($result['ranking'] as $index => $row) {
            $this->assertSame($index + 1, $row['rank']);
            $this->assertLessThanOrEqual($previous, $row['score']);
            $this->assertEqualsWithDelta(array_sum($row['contributions']), $row['score'], 1e-12);
            $this->assertGreaterThanOrEqual(0, $row['jarak']);
            $this->assertSame($row['values'], $result['decision_matrix'][$row['id']]);
            $previous = $row['score'];
        }
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('SAW');
        Kriteria::query()->update(['bobot' => 10]);
        $this->assertSame($result, $snapshot->fresh()->hasil);
        $this->actingAs(Admin::first(), 'admin');
        $this->get(route('admin.saw.index'))->assertOk()->assertSee($snapshot->input['mapel']);
        $this->get(route('admin.saw.show', $snapshot))->assertOk()
            ->assertSee(number_format($result['ranking'][0]['score'], 6));
    }

    public function test_geolocation_is_optional_and_coordinates_are_validated(): void
    {
        $data = $this->selection();
        $this->post('/rekomendasi', $data + ['use_location' => 1, 'latitude' => 91, 'longitude' => 181])
            ->assertSessionHasErrors(['latitude', 'longitude']);
        $this->post('/rekomendasi', $data + ['use_location' => 1])
            ->assertSessionHasErrors(['latitude', 'longitude']);
        $this->post('/rekomendasi', $data + ['use_location' => 1, 'latitude' => -3.8, 'longitude' => 102.26])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertEquals(-3.8, PerhitunganSaw::firstOrFail()->hasil['origin']['latitude']);
    }

    public function test_empty_alternatives_are_rendered_without_a_crash(): void
    {
        TempatBelajar::query()->update(['status' => 'nonaktif']);
        $response = $this->post('/rekomendasi', $this->selection())->assertSessionHasNoErrors();
        $this->assertSame([], PerhitunganSaw::firstOrFail()->hasil['ranking']);
        $this->get($response->headers->get('Location'))->assertOk();
    }

    public function test_invalid_weights_produce_a_helpful_error(): void
    {
        Kriteria::query()->update(['bobot' => 0]);
        $this->from('/rekomendasi')->post('/rekomendasi', $this->selection())
            ->assertRedirect('/rekomendasi')->assertSessionHasErrors('recommendation');
        $this->assertDatabaseCount('perhitungan_saw', 0);
    }

    public function test_nonactive_places_are_not_publicly_accessible(): void
    {
        $place = TempatBelajar::first();
        $place->update(['status' => 'nonaktif']);
        $this->get('/tempat-belajar/'.$place->getKey())->assertNotFound();
    }
}
