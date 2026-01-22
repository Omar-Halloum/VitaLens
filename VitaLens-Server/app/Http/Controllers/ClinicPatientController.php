<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinic;
use App\Services\UserService;
use App\Services\MedicalDocumentService;
use App\Services\ClinicService;
use App\Http\Requests\StoreClinicPatientsRequest;

class ClinicPatientController extends Controller
{
    protected $userService;
    protected $medicalDocumentService;
    protected $clinicService;

    public function __construct(UserService $userService, MedicalDocumentService $medicalDocumentService, ClinicService $clinicService)
    {
        $this->userService = $userService;
        $this->medicalDocumentService = $medicalDocumentService;
        $this->clinicService = $clinicService;
    }

    public function bulkRegister(StoreClinicPatientsRequest $request)
    {
        set_time_limit(600); // Allow 10 minutes for bulk import

        try {
            $validated = $request->validated();
            $folderId = $validated['folder_id'];
            $usersData = $validated['users'];

            $clinic = Clinic::where('drive_folder_id', $folderId)->firstOrFail();

            $createdUsers = $this->userService->importClinicPatients($usersData, $clinic->id);

            return $this->responseJSON([
                'count' => count($createdUsers),
                'clinic' => $clinic->name,
                'users' => $createdUsers
            ], 'Patients registered successfully', 201);

        } catch (\Exception $e) {
            return $this->responseJSON(null, 'Failed to register patients: ' . $e->getMessage(), 500);
        }
    }

    public function matchFolder(Request $request)
    {
        try {
            $request->validate(['folder_id' => ['required', 'string']]);

            $user = User::where('drive_folder_id', $request->folder_id)->firstOrFail();

            return $this->responseJSON([
                'user_id' => $user->id,
                'name' => $user->name,
                'clinic_id' => $user->clinic_id
            ], 'Patient matched successfully');

        } catch (\Exception $e) {
            return $this->responseJSON(null, 'Failed to match folder: ' . $e->getMessage(), 404);
        }
    }

    public function uploadReport(Request $request)
    {
        try {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png']
            ]);

            $targetPatient = User::findOrFail($request->user_id);

            $document = $this->medicalDocumentService->addDocument($targetPatient, $request->file('document'));

            return $this->responseJSON([
                'document_id' => $document->id
            ], 'Report uploaded and processing started', 201);

        } catch (\Exception $e) {
            return $this->responseJSON(null, 'Failed to upload report: ' . $e->getMessage(), 500);
        }
    }

    public function listClinics()
    {
        try {
            $clinics = $this->clinicService->getClinics();

            return $this->responseJSON(
                $clinics, 
                'Clinics retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->responseJSON(null, 'Failed to fetch clinics: ' . $e->getMessage(), 500);
        }
    }
}
