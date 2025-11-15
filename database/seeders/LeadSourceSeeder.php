<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    public function run()
    {
        LeadSource::create(['name' => 'Website', 'code' => 'website']);
        LeadSource::create(['name' => 'Google Ads', 'code' => 'google_ads']);
        LeadSource::create(['name' => 'Meta Ads', 'code' => 'meta_ads']);
        LeadSource::create(['name' => 'WhatsApp', 'code' => 'whatsapp']);
    }
}
