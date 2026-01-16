<?php

namespace Database\Factories;

use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatuslabelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Statuslabel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'      => $this->faker->sentence(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
            'created_by' => User::factory()->superuser(),
            'deleted_at' => null,
            'deployable' => 0,
            'pending' => 0,
            'archived' => 0,
            'notes' => '',
        ];
    }

    public function rtd()
    {
        return $this->state(function () {
            return [
                'notes' => 'Estos activos ESTAN LISTOS para ser asignados a usuarios, lugares u otros equipos',
                'deployable' => 1,
                'default_label' => 1,
            ];
        });
    }

    public function readyToDeploy()
    {
        return $this->rtd();
    }

    public function pending()
    {
        return $this->state(function () {
            return [
                'notes' => 'Estos activos TODAVIA NO pueden ser asignados debido a una falta de software o hardware, se espera que esten en circulación',
                'pending' => 1,
                'default_label' => 1,
            ];
        });
    }

    public function archived()
    {
        return $this->state(function () {
            return [
                'notes' => 'Estos activos estan APARTADOS del inventario activo o ya NO EXISTEN en la empresa',
                'archived' => 1,
                'default_label' => 0,
            ];
        });
    }

    public function undeployable()
    {
        return $this->state(function () {
            return [
                'notes' => 'Estos activos NO PUEDEN ser asignados por varias razones o estan en PROCESO DE BAJA',
                'default_label' => 0,
            ];
        });
    }


    public function outForDiagnostics()
    {
        return $this->state(function () {
            return [
                'name' => 'Out for Diagnostics',
                'default_label' => 0,
            ];
        });
    }

    public function outForRepair()
    {
        return $this->state(function () {
            return [
                'name'      => 'Out for Repair',
                'default_label' => 0,
            ];
        });
    }

    public function broken()
    {
        return $this->state(function () {
            return [
                'name'      => 'Broken - Not Fixable',
                'default_label' => 0,
            ];
        });
    }

    public function lost()
    {
        return $this->state(function () {
            return [
                'name'      => 'Lost/Stolen',
                'default_label' => 0,
            ];
        });
    }
}
