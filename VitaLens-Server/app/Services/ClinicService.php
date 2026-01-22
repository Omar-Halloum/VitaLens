<?php

namespace App\Services;

use App\Models\Clinic;

class ClinicService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getClinics()
{
    $clinic = new Clinic();

    return $clinic->select(['id', 'name', 'drive_folder_id'])
        ->whereNotNull('drive_folder_id')
        ->get();
}
}
