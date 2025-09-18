<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Shortlink;
use App\Models\Click;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample domains
        $domain1 = Domain::firstOrCreate(
            ['name' => 'short.ly'],
            ['is_active' => true]
        );
        
        $domain2 = Domain::firstOrCreate(
            ['name' => 'link.it'],
            ['is_active' => true]
        );
        
        // Create sample shortlinks - only using columns that exist
        $shortlinks = [
            [
                'domain_id' => $domain1->id,
                'original_url' => 'https://laravel.com',
                'short_code' => 'laravel',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(30),
            ],
            [
                'domain_id' => $domain1->id,
                'original_url' => 'https://github.com',
                'short_code' => 'github',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(25),
            ],
            [
                'domain_id' => $domain2->id,
                'original_url' => 'https://www.google.com/search?q=url+shortener',
                'short_code' => 'search',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(20),
            ],
            [
                'domain_id' => $domain1->id,
                'original_url' => 'https://stackoverflow.com/questions/tagged/php',
                'short_code' => 'phpq',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(15),
            ],
        ];

        foreach ($shortlinks as $shortlinkData) {
            Shortlink::firstOrCreate(
                [
                    'domain_id' => $shortlinkData['domain_id'],
                    'short_code' => $shortlinkData['short_code']
                ],
                $shortlinkData
            );
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Created ' . Domain::count() . ' domains');
        $this->command->info('Created ' . Shortlink::count() . ' shortlinks');
    }
}