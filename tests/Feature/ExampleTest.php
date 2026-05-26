<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UksDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Guest users must authenticate before opening the dashboard.
     *
     * @return void
     */
    public function test_guest_is_redirected_from_dashboard()
    {
        $this->get('/')->assertRedirect('/login');
    }

    /**
     * Authenticated users can open the UKS dashboard.
     *
     * @return void
     */
    public function test_authenticated_user_can_open_dashboard()
    {
        $user = User::firstOrCreate(
            ['email' => 'admin-test@example.test'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard Pelayanan UKS');
    }

    /**
     * Dashboard export endpoints provide Excel and PDF downloads.
     *
     * @return void
     */
    public function test_authenticated_user_can_export_dashboard_reports()
    {
        $user = User::firstOrCreate(
            ['email' => 'admin-test@example.test'],
            [
                'name' => 'Admin Test',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        foreach (['kunjungan', 'riwayat', 'pemeriksaan'] as $report) {
            $this->actingAs($user)
                ->get(route('export.' . $report, ['format' => 'excel']))
                ->assertOk()
                ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');

            $this->actingAs($user)
                ->get(route('export.' . $report, ['format' => 'pdf']))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    /**
     * Staff can open a student health profile and download the MCU report.
     *
     * @return void
     */
    public function test_authenticated_user_can_open_student_health_profile_and_mcu_report()
    {
        $this->seed(UksDemoSeeder::class);

        $user = User::where('email', 'admin@sekolah.sch.id')->firstOrFail();
        $anggota = \App\Models\Anggota::where('tipe', 'siswa')->whereHas('pemeriksaan')->firstOrFail();
        $pemeriksaan = $anggota->pemeriksaan()->firstOrFail();

        $this->actingAs($user)
            ->get(route('anggota.show', $anggota))
            ->assertOk()
            ->assertSee('Profil Kesehatan Siswa')
            ->assertSee($anggota->nama);

        $this->actingAs($user)
            ->get(route('pemeriksaan.raport', $pemeriksaan))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Staff can save a student's semester MCU from the profile modal.
     *
     * @return void
     */
    public function test_authenticated_user_can_save_student_mcu_from_profile_context()
    {
        $this->seed(UksDemoSeeder::class);

        $user = User::where('email', 'admin@sekolah.sch.id')->firstOrFail();
        $anggota = \App\Models\Anggota::where('tipe', 'siswa')->firstOrFail();

        $this->actingAs($user)
            ->post(route('pemeriksaan.store'), [
                'redirect_to' => 'anggota.show',
                'anggota_id' => $anggota->id,
                'semester' => 2,
                'tahun_ajaran' => 2025,
                'berat_badan' => 42,
                'tinggi_badan' => 150,
                'penglihatan_kiri' => '1.0',
                'penglihatan_kanan' => '1.0',
                'pendengaran' => 'normal',
                'kondisi_gigi' => 'baik',
                'catatan' => 'Kondisi umum baik.',
            ])
            ->assertRedirect(route('anggota.show', $anggota));

        $this->assertDatabaseHas('pemeriksaan_kesehatan', [
            'anggota_id' => $anggota->id,
            'semester' => 2,
            'tahun_ajaran' => 2025,
        ]);
    }
}
