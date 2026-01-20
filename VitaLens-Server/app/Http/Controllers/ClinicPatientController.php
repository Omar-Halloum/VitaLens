<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserService;
use App\Services\MedicalDocumentService;
use App\Http\Requests\StoreBulkClinicPatientsRequest;

class ClinicPatientController extends Controller
{
    protected $userService;
    protected $medicalDocumentService;

    public function __construct(UserService $userService, MedicalDocumentService $medicalDocumentService)
    {
        $this->userService = $userService;
        $this->medicalDocumentService = $medicalDocumentService;
    }

    public function bulkRegister(StoreBulkClinicPatientsRequest $request)
    {
        try {
            $usersData = $request->validated()['users'];
            $clinicId = $request->user()->clinic_id;

            $createdUsers = $this->userService->importClinicPatients($usersData, $clinicId);

            return $this->responseJSON([
                'count' => count($createdUsers),
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
            $clinicManager = $request->user();

            if ($targetPatient->clinic_id !== $clinicManager->clinic_id) {
                return $this->responseJSON(null, 'Unauthorized: Patient does not belong to your clinic', 403);
            }

            $document = $this->medicalDocumentService->addDocument($targetPatient, $request->file('document'));

            return $this->responseJSON([
                'document_id' => $document->id
            ], 'Report uploaded and processing started', 201);

        } catch (\Exception $e) {
            return $this->responseJSON(null, 'Failed to upload report: ' . $e->getMessage(), 500);
        }
    }
}
