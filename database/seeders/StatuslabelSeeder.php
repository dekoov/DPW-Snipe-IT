<?php

namespace Database\Seeders;

use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Database\Seeder;

class StatuslabelSeeder extends Seeder
{
    public function run()
    {
        Statuslabel::truncate();

        $admin = User::where('permissions->superuser', '1')->first() ?? User::factory()->firstAdmin()->create();

        Statuslabel::factory()->rtd()->create([
            'name' => 'Asignable',
            'created_by' => $admin->id,
        ]);

        Statuslabel::factory()->pending()->create([
            'name' => 'En Mantenimiento',
            'created_by' => $admin->id,
        ]);

        Statuslabel::factory()->archived()->create([
            'name' => 'Dañado/Archivado',
            'created_by' => $admin->id,
            'notes' => 'Equipos dañados sin reparacion y por ende, en proceso de baja'
        ]);

        Statuslabel::factory()->archived()->create([
            'name' => 'De Baja',
            'created_by' => $admin->id,
            'notes' => 'Equipos listos o ya dados de baja, ya no existen en la empresa'
        ]);

        Statuslabel::factory()->undeployable()->create([
            'name' => 'Robado/Perdido',
            'created_by' => $admin->id,
        ]);
    }
}
