<?php

namespace Database\Seeders;

use App\Models\Classes;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = ['X-A', 'X-B', 'XI-A', 'XI-B', 'XII-A', 'XII-B'];

        foreach ($classes as $class) {
            Classes::create(['name' => $class]);
        }
    }
}
