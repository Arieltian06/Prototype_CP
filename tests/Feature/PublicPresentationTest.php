<?php

namespace Tests\Feature;

use App\Models\TempatBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_explain_an_empty_database(): void
    {
        $this->get('/')->assertOk()->assertSee('Tempat belajar segera hadir.');
        $this->get('/tempat-belajar')->assertOk()->assertSee('Belum ada tempat belajar yang ditemukan.');
        $this->get('/rekomendasi')->assertOk()->assertSee('Data jenjang, pelajaran, atau sekolah belum lengkap.');
    }

    public function test_malformed_search_filters_are_validated_before_rendering(): void
    {
        $this->from('/tempat-belajar')->get('/tempat-belajar?q%5B%5D=invalid')
            ->assertRedirect('/tempat-belajar')->assertSessionHasErrors('q');

        $this->from('/tempat-belajar')->get('/tempat-belajar?id_mapel=not-an-id')
            ->assertRedirect('/tempat-belajar')->assertSessionHasErrors('id_mapel');
    }

    public function test_place_names_cannot_break_out_of_leaflet_json_or_html(): void
    {
        $this->seed();
        $place = TempatBelajar::firstOrFail();
        $payload = '</script><script>alert("place-name")</script>';
        $place->update(['nama_tempat' => $payload]);

        $this->get(route('places.show', $place))->assertOk()
            ->assertDontSee($payload, false)
            ->assertSee(e($payload), false)
            ->assertSee(json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), false);
    }

    public static function malformedRecommendationFields(): array
    {
        return array_map(fn ($field) => [$field], [
            'id_jenjang', 'id_mapel', 'id_sekolah', 'use_location', 'latitude', 'longitude',
        ]);
    }

    #[DataProvider('malformedRecommendationFields')]
    public function test_recommendation_form_renders_after_rejecting_array_input(string $field): void
    {
        $this->seed();
        $this->from('/rekomendasi')->post('/rekomendasi', [$field => ['invalid']])
            ->assertRedirect('/rekomendasi')->assertSessionHasErrors($field);

        $this->get('/rekomendasi')->assertOk()->assertSee('Periksa kembali isian Anda.');
        $this->assertDatabaseCount('perhitungan_saw', 0);
    }
}
