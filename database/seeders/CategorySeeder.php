<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Note',
            'vuetify_theme_color_name' => null,
            'mdi_name' => 'mdi-note-text-outline'
        ]);
        Category::create([
            'name' => 'To-Do',
            'vuetify_theme_color_name' => 'primary',
            'mdi_name' => 'mdi-format-list-checks'
        ]);
        Category::create([
            'name' => 'Event',
            'vuetify_theme_color_name' => 'secondary',
            'mdi_name' => 'mdi-calendar-clock'
        ]);
    }
}
