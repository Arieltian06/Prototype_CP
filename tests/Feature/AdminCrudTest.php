<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\NilaiKesesuaian;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private JenjangPendidikan $level;

    private MataPelajaran $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(Admin::create([
            'nama' => 'Admin CRUD', 'email' => 'crud@example.test', 'password' => 'test-password',
        ]), 'admin');
        $this->level = JenjangPendidikan::create(['nama_jenjang' => 'SD']);
        $this->subject = MataPelajaran::create([
            'nama_mapel' => 'IPA', 'id_jenjang' => $this->level->getKey(),
        ]);
        foreach ([3 => 'Cukup', 5 => 'Sangat sesuai'] as $score => $label) {
            NilaiKesesuaian::create(['nilai' => $score, 'label' => $label]);
        }
    }

    public function test_place_lifecycle_saves_photo_scores_and_manual_zero_then_cleans_up(): void
    {
        $otherLevel = JenjangPendidikan::create(['nama_jenjang' => 'SMP']);
        $otherSubject = MataPelajaran::create(['nama_mapel' => 'Biologi', 'id_jenjang' => $otherLevel->getKey()]);
        $criterion = $this->manualCriterion();
        $clearedCriterion = $this->manualCriterion('C5');

        $this->post(route('admin.tempat.store'), $this->placePayload([
            'foto' => $this->png(),
            'mapel' => [$this->subject->getKey() => 5, $otherSubject->getKey() => ''],
            'jenjang' => [$this->level->getKey() => 3, $otherLevel->getKey() => ''],
            'penilaian' => [$criterion->getKey() => '7.125', $clearedCriterion->getKey() => '2.5'],
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $place = TempatBelajar::sole();
        $originalPhoto = $place->foto;
        $this->assertStringStartsWith('tempat/', $originalPhoto);
        Storage::disk('public')->assertExists($originalPhoto);
        $this->assertDatabaseHas('tempat_mapel', [
            'id_tempat' => $place->getKey(), 'id_mapel' => $this->subject->getKey(), 'nilai_kesesuaian' => 5,
        ]);
        $this->assertDatabaseHas('tempat_jenjang', [
            'id_tempat' => $place->getKey(), 'id_jenjang' => $this->level->getKey(), 'nilai_kesesuaian' => 3,
        ]);
        $this->assertDatabaseCount('tempat_mapel', 1);
        $this->assertDatabaseCount('tempat_jenjang', 1);
        $this->assertDatabaseHas('penilaian_tempat', [
            'id_tempat' => $place->getKey(), 'id_kriteria' => $criterion->getKey(), 'nilai' => 7.125,
        ]);

        $this->put(route('admin.tempat.update', $place), $this->placePayload([
            'nama_tempat' => 'Tempat yang diperbarui', 'biaya' => '12500.50', 'status' => 'nonaktif',
            'foto' => $this->png('pengganti.png'),
            'mapel' => [$this->subject->getKey() => '', $otherSubject->getKey() => 3],
            'jenjang' => [$this->level->getKey() => '', $otherLevel->getKey() => 5],
            'penilaian' => [$criterion->getKey() => '0', $clearedCriterion->getKey() => ''],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.tempat.show', $place));

        $place->refresh();
        $this->assertSame('Tempat yang diperbarui', $place->nama_tempat);
        $this->assertSame('12500.50', $place->biaya);
        $this->assertSame('nonaktif', $place->status);
        $this->assertNotSame($originalPhoto, $place->foto);
        Storage::disk('public')->assertMissing($originalPhoto);
        Storage::disk('public')->assertExists($place->foto);
        $this->assertDatabaseCount('tempat_mapel', 1);
        $this->assertDatabaseCount('tempat_jenjang', 1);
        $this->assertDatabaseHas('tempat_mapel', [
            'id_tempat' => $place->getKey(), 'id_mapel' => $otherSubject->getKey(), 'nilai_kesesuaian' => 3,
        ]);
        $this->assertDatabaseHas('tempat_jenjang', [
            'id_tempat' => $place->getKey(), 'id_jenjang' => $otherLevel->getKey(), 'nilai_kesesuaian' => 5,
        ]);
        $this->assertSame(0.0, $place->penilaian()->sole()->nilai);
        $this->assertDatabaseMissing('penilaian_tempat', ['id_kriteria' => $clearedCriterion->getKey()]);

        $this->delete(route('admin.tempat.destroy', $place))
            ->assertRedirect(route('admin.tempat.index'))->assertSessionHas('success');

        Storage::disk('public')->assertMissing($place->foto);
        $this->assertDatabaseCount('tempat_belajar', 0);
        $this->assertDatabaseCount('tempat_mapel', 0);
        $this->assertDatabaseCount('tempat_jenjang', 0);
        $this->assertDatabaseCount('penilaian_tempat', 0);
    }

    public static function invalidPhotos(): array
    {
        return [
            'text disguised as png' => ['palsu.png', 'This is not an image.', 'text/plain'],
            'svg is not an allowed photo' => ['gambar.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><rect width="1" height="1"/></svg>', 'image/svg+xml'],
        ];
    }

    #[DataProvider('invalidPhotos')]
    public function test_invalid_photo_content_is_rejected_without_persisting_a_place(string $name, string $content, string $mimeType): void
    {
        $this->post(route('admin.tempat.store'), $this->placePayload([
            'foto' => UploadedFile::fake()->createWithContent($name, $content)->mimeType($mimeType),
        ]))->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('tempat_belajar', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_photo_replacement_preserves_existing_photo_and_place_data(): void
    {
        $this->post(route('admin.tempat.store'), $this->placePayload(['foto' => $this->png()]))
            ->assertSessionHasNoErrors();
        $place = TempatBelajar::sole();
        $photo = $place->foto;

        $this->put(route('admin.tempat.update', $place), $this->placePayload([
            'nama_tempat' => 'Tidak boleh tersimpan', 'hapus_foto' => 1,
            'foto' => UploadedFile::fake()->createWithContent('palsu.jpg', 'Not a JPEG.')->mimeType('text/plain'),
        ]))->assertSessionHasErrors('foto');

        $place->refresh();
        $this->assertSame('Tempat CRUD', $place->nama_tempat);
        $this->assertSame($photo, $place->foto);
        Storage::disk('public')->assertExists($photo);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_admin_can_remove_photo_without_deleting_the_place(): void
    {
        $this->post(route('admin.tempat.store'), $this->placePayload(['foto' => $this->png()]))
            ->assertSessionHasNoErrors();
        $place = TempatBelajar::sole();
        $photo = $place->foto;

        $this->put(route('admin.tempat.update', $place), $this->placePayload(['hapus_foto' => 1]))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.tempat.show', $place));

        $this->assertNull($place->fresh()->foto);
        Storage::disk('public')->assertMissing($photo);
        $this->assertDatabaseCount('tempat_belajar', 1);
    }

    public function test_scale_update_propagates_to_both_relations_and_used_scale_cannot_be_deleted(): void
    {
        $place = TempatBelajar::create($this->placePayload());
        $place->mapel()->attach($this->subject, ['nilai_kesesuaian' => 3]);
        $place->jenjang()->attach($this->level, ['nilai_kesesuaian' => 3]);
        $otherPlace = TempatBelajar::create($this->placePayload(['nama_tempat' => 'Tempat lainnya']));
        $otherPlace->mapel()->attach($this->subject, ['nilai_kesesuaian' => 5]);
        $scale = NilaiKesesuaian::where('nilai', 3)->firstOrFail();

        $this->put(route('admin.skala.update', $scale), ['nilai' => '3.5', 'label' => 'Cukup sesuai'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.skala.index'));

        $this->assertSame(3.5, $scale->fresh()->nilai);
        foreach (['tempat_mapel', 'tempat_jenjang'] as $table) {
            $this->assertDatabaseHas($table, ['id_tempat' => $place->getKey(), 'nilai_kesesuaian' => 3.5]);
            $this->assertDatabaseMissing($table, ['nilai_kesesuaian' => 3]);
        }
        $this->assertDatabaseHas('tempat_mapel', ['id_tempat' => $otherPlace->getKey(), 'nilai_kesesuaian' => 5]);

        $this->from(route('admin.skala.index'))->delete(route('admin.skala.destroy', $scale))
            ->assertRedirect(route('admin.skala.index'))->assertSessionHas('error');
        $this->assertDatabaseHas('nilai_kesesuaian', ['id' => $scale->getKey(), 'nilai' => 3.5]);
    }

    public static function levelDependencies(): array
    {
        return ['subject' => ['mapel'], 'school' => ['sekolah'], 'place score' => ['tempat']];
    }

    #[DataProvider('levelDependencies')]
    public function test_education_level_cannot_be_deleted_while_used(string $dependency): void
    {
        $level = JenjangPendidikan::create(['nama_jenjang' => 'Jenjang terpakai']);
        if ($dependency === 'mapel') {
            MataPelajaran::create(['nama_mapel' => 'Seni', 'id_jenjang' => $level->getKey()]);
        } elseif ($dependency === 'sekolah') {
            Sekolah::create([
                'nama_sekolah' => 'Sekolah CRUD', 'alamat' => 'Alamat sekolah',
                'latitude' => -3.8, 'longitude' => 102.2, 'id_jenjang' => $level->getKey(),
            ]);
        } else {
            TempatBelajar::create($this->placePayload())->jenjang()->attach($level, ['nilai_kesesuaian' => 5]);
        }

        $this->from(route('admin.jenjang.index'))->delete(route('admin.jenjang.destroy', $level))
            ->assertRedirect(route('admin.jenjang.index'))->assertSessionHas('error');

        $this->assertDatabaseHas('jenjang_pendidikan', ['id_jenjang' => $level->getKey()]);
    }

    public function test_criterion_crud_persists_values_and_removes_associated_manual_scores(): void
    {
        $this->manualCriterion('C4');
        $payload = [
            'kode_kriteria' => 'C5', 'nama_kriteria' => 'Fasilitas pembelajaran',
            'jenis_kriteria' => 'benefit', 'bobot' => '0.175', 'sumber_nilai' => 'manual',
        ];
        $this->post(route('admin.kriteria.store'), $payload)
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.kriteria.index'));
        $criterion = Kriteria::where('kode_kriteria', 'C5')->firstOrFail();
        $this->assertDatabaseHas('kriteria', $payload);
        $this->get(route('admin.kriteria.edit', $criterion))->assertOk()->assertSee('Fasilitas pembelajaran');

        $place = TempatBelajar::create($this->placePayload());
        $place->penilaian()->create(['id_kriteria' => $criterion->getKey(), 'nilai' => 4.75]);
        $updated = array_replace($payload, [
            'nama_kriteria' => 'Biaya perlengkapan', 'jenis_kriteria' => 'cost', 'bobot' => '0.225',
        ]);
        $this->put(route('admin.kriteria.update', $criterion), $updated)
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.kriteria.index'));
        $this->assertDatabaseHas('kriteria', ['id_kriteria' => $criterion->getKey()] + $updated);
        $this->assertSame(4.75, $place->penilaian()->sole()->nilai);

        $this->delete(route('admin.kriteria.destroy', $criterion))
            ->assertRedirect(route('admin.kriteria.index'))->assertSessionHas('success');
        $this->assertDatabaseMissing('kriteria', ['id_kriteria' => $criterion->getKey()]);
        $this->assertDatabaseCount('penilaian_tempat', 0);
        $this->assertDatabaseCount('tempat_belajar', 1);
    }

    public function test_last_positive_criterion_cannot_be_zeroed_or_deleted(): void
    {
        $criterion = $this->manualCriterion();
        $this->put(route('admin.kriteria.update', $criterion), [
            'kode_kriteria' => 'C4', 'nama_kriteria' => 'Nilai pembelajaran',
            'jenis_kriteria' => 'benefit', 'bobot' => 0, 'sumber_nilai' => 'manual',
        ])->assertSessionHasErrors('bobot');

        $this->from(route('admin.kriteria.index'))->delete(route('admin.kriteria.destroy', $criterion))
            ->assertRedirect(route('admin.kriteria.index'))->assertSessionHas('error');
        $this->assertDatabaseHas('kriteria', ['id_kriteria' => $criterion->getKey(), 'bobot' => 1]);
    }

    public static function malformedPlaceFields(): array
    {
        return array_map(fn ($field) => [$field], [
            'nama_tempat', 'alamat', 'deskripsi', 'karakteristik', 'aktivitas',
            'latitude', 'longitude', 'biaya', 'jam_operasional', 'penilaian',
        ]);
    }

    #[DataProvider('malformedPlaceFields')]
    public function test_place_form_renders_validation_errors_after_array_input(string $field): void
    {
        $criterion = $this->manualCriterion();
        $value = $field === 'penilaian' ? [$criterion->getKey() => ['invalid']] : ['invalid'];
        $error = $field === 'penilaian' ? 'penilaian.'.$criterion->getKey() : $field;
        $this->from(route('admin.tempat.create'))->post(route('admin.tempat.store'), $this->placePayload([
            $field => $value,
        ]))->assertRedirect(route('admin.tempat.create'))->assertSessionHasErrors($error);

        $this->get(route('admin.tempat.create'))->assertOk()->assertSee('alert-danger', false);
        $this->assertDatabaseCount('tempat_belajar', 0);
    }

    public function test_master_form_renders_validation_errors_after_array_input(): void
    {
        $this->from(route('admin.sekolah.create'))->post(route('admin.sekolah.store'), [
            'nama_sekolah' => ['invalid'], 'alamat' => ['invalid'], 'id_jenjang' => ['invalid'],
            'latitude' => ['invalid'], 'longitude' => ['invalid'],
        ])->assertRedirect(route('admin.sekolah.create'))
            ->assertSessionHasErrors(['nama_sekolah', 'alamat', 'id_jenjang', 'latitude', 'longitude']);

        $this->get(route('admin.sekolah.create'))->assertOk()->assertSee('alert-danger', false);
        $this->assertDatabaseCount('sekolah', 0);
    }

    public function test_login_form_renders_validation_errors_after_array_email(): void
    {
        auth('admin')->logout();
        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => ['invalid'], 'password' => 'invalid',
        ])->assertRedirect(route('admin.login'))->assertSessionHasErrors('email');

        $this->get(route('admin.login'))->assertOk()->assertSee('alert-danger', false);
        $this->assertGuest('admin');
    }

    private function manualCriterion(string $code = 'C4'): Kriteria
    {
        return Kriteria::create([
            'kode_kriteria' => $code, 'nama_kriteria' => 'Nilai pembelajaran '.$code,
            'jenis_kriteria' => 'benefit', 'bobot' => 1, 'sumber_nilai' => 'manual',
        ]);
    }

    private function png(string $name = 'tempat.png'): UploadedFile
    {
        // A real 1x1 PNG keeps upload validation exercised without requiring GD.
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aDPsAAAAASUVORK5CYII='
        ));
    }

    private function placePayload(array $overrides = []): array
    {
        return array_replace([
            'nama_tempat' => 'Tempat CRUD', 'alamat' => 'Alamat tempat',
            'deskripsi' => 'Deskripsi pembelajaran pesisir', 'karakteristik' => 'Ekosistem pesisir',
            'aktivitas' => 'Observasi kelompok', 'latitude' => -3.8, 'longitude' => 102.2,
            'biaya' => 0, 'jam_operasional' => '08.00-16.00', 'status' => 'aktif', 'is_prototype' => 1,
        ], $overrides);
    }
}
