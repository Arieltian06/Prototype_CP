<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CoreRegressionTest extends TestCase
{
    use RefreshDatabase;

    private JenjangPendidikan $level;

    private MataPelajaran $subject;

    private Sekolah $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->level = JenjangPendidikan::create(['nama_jenjang' => 'SD']);
        $this->subject = MataPelajaran::create(['nama_mapel' => 'IPA', 'id_jenjang' => $this->level->getKey()]);
        $this->school = Sekolah::create([
            'nama_sekolah' => 'Sekolah Uji', 'alamat' => 'Alamat Uji',
            'latitude' => -3.8, 'longitude' => 102.2, 'id_jenjang' => $this->level->getKey(),
        ]);
    }

    private function admin(): void
    {
        $this->actingAs(Admin::create(['nama' => 'Admin Uji', 'email' => 'regression@example.test', 'password' => 'test-password']), 'admin');
    }

    public static function adminSearchPages(): array
    {
        return [
            'places' => ['/admin/tempat'],
            'master data' => ['/admin/jenjang'],
            'suitability' => ['/admin/kesesuaian'],
            'history' => ['/admin/perhitungan'],
        ];
    }

    #[DataProvider('adminSearchPages')]
    public function test_admin_search_rejects_array_input_without_server_error(string $path): void
    {
        $this->admin();
        $this->getJson($path.'?q%5B%5D=invalid')->assertUnprocessable()->assertJsonValidationErrors('q');
    }

    public function test_recommendation_rejects_nested_level_input_before_database_lookup(): void
    {
        $this->postJson('/rekomendasi', [
            'id_jenjang' => [[$this->level->getKey(), $this->level->getKey()]],
            'id_mapel' => $this->subject->getKey(),
            'id_sekolah' => $this->school->getKey(),
        ])->assertUnprocessable()->assertJsonValidationErrors('id_jenjang');
        $this->assertDatabaseCount('perhitungan_saw', 0);
    }

    public static function malformedRecommendationFields(): array
    {
        return [
            'level array' => ['id_jenjang'],
            'subject array' => ['id_mapel'],
            'school array' => ['id_sekolah'],
            'location flag array' => ['use_location'],
            'latitude array' => ['latitude'],
            'longitude array' => ['longitude'],
        ];
    }

    #[DataProvider('malformedRecommendationFields')]
    public function test_recommendation_rejects_array_form_fields_without_server_error(string $field): void
    {
        $data = [
            'id_jenjang' => $this->level->getKey(), 'id_mapel' => $this->subject->getKey(),
            'id_sekolah' => $this->school->getKey(), 'use_location' => 1,
            'latitude' => -3.8, 'longitude' => 102.2,
        ];
        $data[$field] = [$data[$field]];
        $this->postJson('/rekomendasi', $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('perhitungan_saw', 0);
    }

    public function test_subject_creation_rejects_nested_level_input_before_unique_lookup(): void
    {
        $this->admin();
        $this->postJson('/admin/mapel', [
            'nama_mapel' => 'IPS', 'id_jenjang' => [[$this->level->getKey(), $this->level->getKey()]],
        ])->assertUnprocessable()->assertJsonValidationErrors('id_jenjang');
        $this->assertDatabaseCount('mata_pelajaran', 1);
    }

    public function test_manual_zero_survives_the_admin_form_and_place_persistence(): void
    {
        $this->admin();
        $criterion = Kriteria::create([
            'kode_kriteria' => 'C4', 'nama_kriteria' => 'Biaya tambahan',
            'jenis_kriteria' => 'cost', 'bobot' => 1, 'sumber_nilai' => 'manual',
        ]);
        $this->post('/admin/tempat', $this->placePayload() + [
            'penilaian' => [$criterion->getKey() => '0'],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $place = TempatBelajar::firstOrFail();
        $this->assertDatabaseHas('penilaian_tempat', ['id_tempat' => $place->getKey(), 'id_kriteria' => $criterion->getKey(), 'nilai' => 0]);
        $this->assertSame(0.0, $place->penilaian()->firstOrFail()->nilai);
    }

    public function test_manual_form_rejects_values_for_dynamic_criteria(): void
    {
        $this->admin();
        $criterion = Kriteria::create([
            'kode_kriteria' => 'C3', 'nama_kriteria' => 'Jarak',
            'jenis_kriteria' => 'cost', 'bobot' => 1, 'sumber_nilai' => 'jarak',
        ]);
        $this->postJson('/admin/tempat', $this->placePayload() + [
            'penilaian' => [$criterion->getKey() => '99'],
        ])->assertUnprocessable()->assertJsonValidationErrors('penilaian');
        $this->assertDatabaseCount('tempat_belajar', 0);
        $this->assertDatabaseCount('penilaian_tempat', 0);
    }

    private function placePayload(): array
    {
        return [
            'nama_tempat' => 'Tempat Uji', 'alamat' => 'Alamat Uji',
            'deskripsi' => 'Deskripsi pembelajaran', 'karakteristik' => 'Ekosistem pesisir',
            'aktivitas' => 'Observasi', 'latitude' => -3.8, 'longitude' => 102.2,
            'biaya' => 0, 'jam_operasional' => '08.00-16.00', 'status' => 'aktif', 'is_prototype' => 1,
        ];
    }
}
