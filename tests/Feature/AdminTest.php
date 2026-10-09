<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JenjangPendidikan;
use App\Models\Kriteria;
use App\Models\MataPelajaran;
use App\Models\Sekolah;
use App\Models\TempatBelajar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_all_admin_data_requires_authentication(): void
    {
        foreach (['dashboard', 'tempat', 'jenjang', 'mapel', 'sekolah', 'kriteria', 'kesesuaian'] as $path) {
            $this->get('/admin/'.$path)->assertRedirect(route('admin.login'));
        }
        $this->post('/admin/jenjang', ['nama_jenjang' => 'Ilegal'])->assertRedirect(route('admin.login'));
        $this->assertDatabaseMissing('jenjang_pendidikan', ['nama_jenjang' => 'Ilegal']);
    }

    public function test_demo_password_is_hashed_and_admin_can_login_and_logout(): void
    {
        $admin = Admin::firstOrFail();
        $this->assertNotSame('password123', $admin->password);
        $this->assertTrue(Hash::check('password123', $admin->password));
        $this->post(route('admin.login.store'), ['email' => 'admin@edupesisir.test', 'password' => 'wrong'])
            ->assertSessionHasErrors();
        $this->assertGuest('admin');
        $this->post(route('admin.login.store'), ['email' => 'admin@edupesisir.test', 'password' => 'password123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->post(route('admin.logout'))->assertRedirect();
        $this->assertGuest('admin');
    }

    public function test_admin_pages_and_forms_render(): void
    {
        $this->actingAs(Admin::first(), 'admin');
        foreach (['dashboard', 'tempat', 'jenjang', 'mapel', 'sekolah', 'kriteria', 'kesesuaian'] as $path) {
            $this->get('/admin/'.$path)->assertOk();
        }
        foreach (['tempat', 'jenjang', 'mapel', 'sekolah', 'kriteria'] as $path) {
            $this->get('/admin/'.$path.'/create')->assertOk();
        }
        foreach (['tempat' => TempatBelajar::first(), 'jenjang' => JenjangPendidikan::first(), 'mapel' => MataPelajaran::first(), 'sekolah' => Sekolah::first(), 'kriteria' => Kriteria::first()] as $path => $model) {
            $this->get('/admin/'.$path.'/'.$model->getKey().'/edit')->assertOk();
        }
    }

    public function test_admin_can_create_update_and_delete_an_unused_education_level(): void
    {
        $this->actingAs(Admin::first(), 'admin');
        $this->post('/admin/jenjang', ['nama_jenjang' => 'Demo Baru'])->assertSessionHasNoErrors()->assertRedirect();
        $level = JenjangPendidikan::where('nama_jenjang', 'Demo Baru')->firstOrFail();
        $this->put('/admin/jenjang/'.$level->getKey(), ['nama_jenjang' => 'Demo Edit'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('jenjang_pendidikan', ['id_jenjang' => $level->getKey(), 'nama_jenjang' => 'Demo Edit']);
        $this->delete('/admin/jenjang/'.$level->getKey())->assertRedirect();
        $this->assertDatabaseMissing('jenjang_pendidikan', ['id_jenjang' => $level->getKey()]);
    }

    public function test_invalid_place_input_is_rejected_by_backend(): void
    {
        $this->actingAs(Admin::first(), 'admin');
        $this->post('/admin/tempat', ['nama_tempat' => 'Tempat Baru', 'latitude' => 200, 'longitude' => -181, 'biaya' => -1, 'status' => 'invalid'])
            ->assertSessionHasErrors(['latitude', 'longitude', 'biaya', 'status']);
    }
}
