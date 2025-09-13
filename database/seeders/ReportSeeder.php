<?php
// database/seeders/ReportSeeder.php (Updated - Removed ObservationForm)

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\Location;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run()
    {
        $employees = User::where('role', 'employee')->get();
        $hseStaffs = User::where('role', 'hse_staff')->get();

        // Get master data
        $categories = Category::all();
        $contributings = Contributing::all();
        $actions = Action::all();
        $locations = Location::all();

        // Create sample reports with new structure
        $reports = [
            [
                'employee_id' => $employees->first()->id,
                'category_id' => $categories->where('name', 'UNSAFE CONDITION')->first()?->id,
                'contributing_id' => $contributings->where('name', 'Peralatan (hand tool atau alat) yang cacat')->first()?->id,
                'action_id' => $actions->where('name', 'Tumpul')->first()?->id,
                'severity_rating' => 'medium',
                'description' => 'Pahat yang digunakan sudah tumpul dan berbahaya untuk digunakan',
                'location_id' => $locations->where('name', 'Pabrik Bekasi')->first()?->id,
                'status' => 'waiting',
                'action_taken' => null,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'employee_id' => $employees->skip(1)->first()->id,
                'category_id' => $categories->where('name', 'UNSAFE BEHAVIOR')->first()?->id,
                'contributing_id' => $contributings->where('name', 'Gagal menggunakan alat pelindung diri')->first()?->id,
                'action_id' => $actions->where('name', 'Tidak memakai helm keselamatan')->first()?->id,
                'severity_rating' => 'high',
                'description' => 'Pekerja tidak memakai helm saat bekerja di area konstruksi',
                'location_id' => $locations->where('name', 'Site Balikpapan')->first()?->id,
                'status' => 'in-progress',
                'action_taken' => 'Pekerja sudah diberikan teguran dan helm keselamatan baru',
                'start_process_at' => Carbon::now()->subHour(),
                'hse_staff_id' => $hseStaffs->first()->id,
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'employee_id' => $employees->last()->id,
                'category_id' => $categories->where('name', 'ENVIRONMENTAL HAZARD')->first()?->id,
                'contributing_id' => $contributings->where('name', 'Kondisi pencahayaan yang buruk')->first()?->id,
                'action_id' => $actions->where('name', 'Lampu mati atau redup')->first()?->id,
                'severity_rating' => 'low',
                'description' => 'Pencahayaan di area gudang sangat redup karena beberapa lampu mati',
                'location_id' => $locations->where('name', 'Gudang Tangerang')->first()?->id,
                'status' => 'done',
                'action_taken' => 'Semua lampu sudah diganti dengan LED baru dan pencahayaan sudah memadai',
                'start_process_at' => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDay(),
                'hse_staff_id' => $hseStaffs->last()->id,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'employee_id' => $employees->first()->id,
                'category_id' => $categories->where('name', 'EQUIPMENT FAILURE')->first()?->id,
                'contributing_id' => $contributings->where('name', 'Kegagalan sistem mekanik')->first()?->id,
                'action_id' => $actions->where('name', 'Bearing rusak')->first()?->id,
                'severity_rating' => 'critical',
                'description' => 'Bearing pada motor conveyor rusak dan menimbulkan suara keras',
                'location_id' => $locations->where('name', 'Pabrik Bekasi')->first()?->id,
                'status' => 'waiting',
                'action_taken' => null,
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'employee_id' => $employees->skip(1)->first()->id,
                'category_id' => $categories->where('name', 'UNSAFE CONDITION')->first()?->id,
                'contributing_id' => $contributings->where('name', 'Peralatan pelindung diri yang tidak memadai')->first()?->id,
                'action_id' => $actions->where('name', 'APD rusak atau cacat')->first()?->id,
                'severity_rating' => 'medium',
                'description' => 'Sarung tangan safety sobek dan tidak melindungi dengan baik',
                'location_id' => $locations->where('name', 'Pabrik Bekasi')->first()?->id,
                'status' => 'in-progress',
                'action_taken' => 'APD baru sedang dalam proses pengadaan',
                'start_process_at' => Carbon::now()->subHours(3),
                'hse_staff_id' => $hseStaffs->first()->id,
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'employee_id' => $employees->last()->id,
                'category_id' => $categories->where('name', 'EMERGENCY SITUATION')->first()?->id,
                'contributing_id' => $contributings->where('name', 'Mengabaikan prosedur keselamatan')->first()?->id,
                'action_id' => $actions->where('name', 'Skip safety briefing')->first()?->id,
                'severity_rating' => 'high',
                'description' => 'Karyawan langsung bekerja tanpa mengikuti safety briefing',
                'location_id' => $locations->where('name', 'Kantor Cabang Surabaya')->first()?->id,
                'status' => 'done',
                'action_taken' => 'Karyawan sudah diberikan training ulang dan mengikuti safety briefing secara rutin',
                'start_process_at' => Carbon::now()->subDays(3),
                'completed_at' => Carbon::now()->subDays(2),
                'hse_staff_id' => $hseStaffs->first()->id,
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'employee_id' => $employees->first()->id,
                'category_id' => $categories->where('name', 'ENVIRONMENTAL HAZARD')->first()?->id,
                'contributing_id' => $contributings->where('name', 'Kebisingan berlebihan')->first()?->id,
                'action_id' => $actions->where('name', 'Mesin berisik tidak di-maintenance')->first()?->id,
                'severity_rating' => 'medium',
                'description' => 'Mesin kompressor mengeluarkan suara sangat bising melebihi batas normal',
                'location_id' => $locations->where('name', 'Kantor Regional Medan')->first()?->id,
                'status' => 'waiting',
                'action_taken' => null,
                'created_at' => Carbon::now()->subHours(1),
            ]
        ];

        foreach ($reports as $reportData) {
            Report::create($reportData);
        }
    }
}
