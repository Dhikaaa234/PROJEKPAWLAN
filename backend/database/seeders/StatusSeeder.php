<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'Dikirim',
            'Diproses',
            'Selesai',
            'Dibatalkan',
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(
                ['name' => $status],
                ['name' => $status]
            );
        }
    }
}
