<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BloodGroup;
use App\Models\Religion;
use App\Models\Division;
use App\Models\District;
use App\Models\AppSetting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Blood Groups ─────────────────────────────────
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'];
        foreach ($bloodGroups as $i => $bg) {
            BloodGroup::firstOrCreate(['name' => $bg], ['sort_order' => $i, 'is_active' => true]);
        }

        // ── Religions ────────────────────────────────────
        $religions = ['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Others'];
        foreach ($religions as $i => $r) {
            Religion::firstOrCreate(['name' => $r], ['sort_order' => $i, 'is_active' => true]);
        }

        // ── Divisions & Districts ─────────────────────────
        $data = [
            'ঢাকা'       => ['ঢাকা','গাজীপুর','নারায়ণগঞ্জ','নরসিংদী','মুন্সীগঞ্জ','মানিকগঞ্জ','কিশোরগঞ্জ','টাঙ্গাইল','ফরিদপুর','গোপালগঞ্জ','মাদারীপুর','রাজবাড়ী','শরীয়তপুর'],
            'চট্টগ্রাম'  => ['চট্টগ্রাম','কক্সবাজার','রাঙ্গামাটি','বান্দরবান','খাগড়াছড়ি','ফেনী','লক্ষ্মীপুর','নোয়াখালী','কুমিল্লা','চাঁদপুর','ব্রাহ্মণবাড়িয়া'],
            'রাজশাহী'    => ['রাজশাহী','নওগাঁ','নাটোর','চাঁপাইনবাবগঞ্জ','বগুড়া','পাবনা','সিরাজগঞ্জ','জয়পুরহাট'],
            'খুলনা'      => ['খুলনা','বাগেরহাট','সাতক্ষীরা','যশোর','ঝিনাইদহ','মাগুরা','নড়াইল','কুষ্টিয়া','চুয়াডাঙ্গা','মেহেরপুর'],
            'বরিশাল'     => ['বরিশাল','পটুয়াখালী','ভোলা','পিরোজপুর','বরগুনা','ঝালকাঠি'],
            'সিলেট'      => ['সিলেট','সুনামগঞ্জ','হবিগঞ্জ','মৌলভীবাজার'],
            'রংপুর'      => ['রংপুর','গাইবান্ধা','কুড়িগ্রাম','লালমনিরহাট','নীলফামারী','পঞ্চগড়','ঠাকুরগাঁও','দিনাজপুর'],
            'ময়মনসিংহ'  => ['ময়মনসিংহ','জামালপুর','শেরপুর','নেত্রকোনা'],
        ];

        foreach ($data as $divName => $districts) {
            $div = Division::firstOrCreate(['name' => $divName]);
            foreach ($districts as $distName) {
                District::firstOrCreate(['division_id' => $div->id, 'name' => $distName]);
            }
        }

        // ── Global App Settings ───────────────────────────
        $settings = [
            ['key' => 'institute_name',    'value' => 'Islamic Online Madrasah', 'label' => 'Institute Name',    'type' => 'text',     'group' => 'general'],
            ['key' => 'institute_tagline', 'value' => 'Through Knowledge, Towards Jannah', 'label' => 'Tagline', 'type' => 'text',     'group' => 'general'],
            ['key' => 'contact_phone',     'value' => '09638-113322',             'label' => 'Contact Phone',    'type' => 'text',     'group' => 'contact'],
            ['key' => 'contact_email',     'value' => 'info@iom.edu.bd',          'label' => 'Contact Email',    'type' => 'text',     'group' => 'contact'],
            ['key' => 'contact_address',   'value' => 'West Joynagar, Ati Bajar, Keraniganj', 'label' => 'Address', 'type' => 'textarea', 'group' => 'contact'],
            ['key' => 'facebook_url',      'value' => '',                         'label' => 'Facebook URL',     'type' => 'text',     'group' => 'contact'],
            ['key' => 'admission_open',    'value' => '1',                        'label' => 'Admission Open',   'type' => 'boolean',  'group' => 'admission'],
            ['key' => 'admission_terms',   'value' => "১. ভর্তির বাপরে বৈধ অভিভাবকের সম্মতি থাকা।\n২. রাষ্ট্রবিরোধী কোন দল বা কর্মকর্তার সাথে জড়িত থাকা যাবে না।\n৩. রাজনীতি বা সরকার কর্তৃক নিষিদ্ধ কোন সংগঠনের সাথে জড়িত থাকা যাবে না।", 'label' => 'Admission Terms & Conditions', 'type' => 'textarea', 'group' => 'admission'],
        ];

        foreach ($settings as $s) {
            AppSetting::firstOrCreate(['key' => $s['key']], $s);
        }

        // ── Default Academic Year & Session ────────────────
        $year = \App\Models\AcademicYear::firstOrCreate(
            ['name' => '2026-2027'],
            ['start_date' => '2026-01-01', 'end_date' => '2027-12-31', 'is_active' => true]
        );

        \App\Models\AcademicSession::firstOrCreate(
            ['name' => 'Session 2026-2027'],
            ['academic_year_id' => $year->id, 'is_active' => true]
        );
    }
}
