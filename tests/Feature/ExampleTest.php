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
     * Dashboard accepts the school level and class filters.
     *
     * @return void
     */
    public function test_dashboard_can_be_filtered_by_jenjang_and_kelas()
    {
        $this->seed(UksDemoSeeder::class);

        $user = User::where('email', 'admin@sekolah.sch.id')->firstOrFail();
        $siswa = \App\Models\Anggota::where('nis_nip', 'SD007')->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard', ['jenjang_id' => $siswa->jenjang_id, 'kelas' => $siswa->kelas]))
            ->assertOk()
            ->assertSee('value="' . $siswa->kelas . '" selected', false)
            ->assertSee('Anisa Kirana');
    }

    /**
     * The member summary counts the distinct non-empty student classes.
     *
     * @return void
     */
    public function test_member_summary_counts_student_classes()
    {
        $this->seed(UksDemoSeeder::class);

        $user = User::where('email', 'admin@sekolah.sch.id')->firstOrFail();

        $this->actingAs($user)
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertViewHas('ringkasan', fn ($ringkasan) => $ringkasan['kelas'] > 0);
    }

    /**
     * The examination summary shows students not examined in the active semester.
     *
     * @return void
     */
    public function test_examination_summary_counts_students_not_examined_this_semester()
    {
        $this->seed(UksDemoSeeder::class);

        $user = User::where('email', 'admin@sekolah.sch.id')->firstOrFail();
        $semester = now()->month >= 7 ? 1 : 2;
        $tahunAjaran = now()->month >= 7 ? now()->year : now()->year - 1;
        $expected = \App\Models\Anggota::where('aktif', true)
            ->where('tipe', 'siswa')
            ->whereDoesntHave('pemeriksaan', fn ($query) => $query
                ->where('semester', $semester)
                ->where('tahun_ajaran', $tahunAjaran))
            ->count();

        $this->actingAs($user)
            ->get(route('pemeriksaan.index'))
            ->assertOk()
            ->assertViewHas('ringkasan', fn ($ringkasan) => $ringkasan['belum_diperiksa'] === $expected);
    }

    /**
     * Promotion advances only the agreed levels, graduates SMP grade 9, and keeps TK unchanged.
     *
     * @return void
     */
    public function test_yearly_promotion_applies_the_school_rules()
    {
        $this->seed(UksDemoSeeder::class);

        $this->artisan('uks:promote-students', [
            '--execute' => true,
            '--date' => '2026-07-01',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('anggota', ['nis_nip' => 'SD001', 'kelas' => '2A', 'aktif' => true]);
        $this->assertDatabaseHas('anggota', ['nis_nip' => 'SMP001', 'kelas' => '8A', 'aktif' => true]);
        $this->assertDatabaseHas('anggota', ['nis_nip' => 'SMP005', 'kelas' => '9A', 'aktif' => false]);
        $this->assertDatabaseHas('anggota', ['nis_nip' => 'TK001', 'kelas' => 'TK B', 'aktif' => true]);
        $this->assertDatabaseHas('riwayat_kelas', ['tahun_ajaran' => 2025, 'aksi' => 'lulus']);
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
     * MCU reports respect the selected school level and class.
     *
     * @return void
     */
    public function test_mcu_export_can_be_filtered_by_jenjang_and_kelas()
    {
        $this->seed(UksDemoSeeder::class);

        $user = User::where('email', 'admin@sekolah.sch.id')->firstOrFail();
        $siswaKelas4A = \App\Models\Anggota::where('nis_nip', 'SD007')->firstOrFail();

        $response = $this->actingAs($user)->get(route('export.pemeriksaan', [
            'format' => 'excel',
            'jenjang_id' => $siswaKelas4A->jenjang_id,
            'kelas' => $siswaKelas4A->kelas,
        ]));

        $response->assertOk()
            ->assertSee('Anisa Kirana')
            ->assertDontSee('Bagas Mahendra');
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
