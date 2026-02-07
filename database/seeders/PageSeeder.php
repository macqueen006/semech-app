<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'south-west-geo-data-integration',
                'title' => 'South West Geo Data Integration',
                'content' => '<p>Default content for South West Geo Data Integration page.</p>',
                'is_published' => true,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => '<p>Default content for Privacy Policy page.</p>',
                'is_published' => true,
            ],
            [
                'slug' => 'advertise',
                'title' => 'Advertise With Us',
                'content' => '<p>Default content for Advertise page.</p>',
                'is_published' => true,
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'content' => '<p>Default content for Terms and Conditions page.</p>',
                'is_published' => true,
            ]
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }
    }
}
