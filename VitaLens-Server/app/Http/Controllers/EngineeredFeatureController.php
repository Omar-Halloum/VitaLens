<?php

namespace App\Http\Controllers;

use App\Services\EngineeredFeatureService;
use Illuminate\Http\Request;

class EngineeredFeatureController extends Controller
{
    protected $engineeredFeatureService;

    public function __construct(EngineeredFeatureService $engineeredFeatureService)
    {
        $this->engineeredFeatureService = $engineeredFeatureService;
    }

    public function engineerFeatures(Request $request)
    {
        try {
            $user = $request->user();
            $features = $this->engineeredFeatureService->prepareUserFeatures($user);
            
            return $this->responseJSON($features, "Features engineered successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to engineer features: " . $e->getMessage(), 500);
        }
    }

    public function getUserFeatures(Request $request)
    {
        try {
            $user = $request->user();
            $features = $this->engineeredFeatureService->getUserFeatures($user);
            
            return $this->responseJSON($features, "Features retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve features: " . $e->getMessage(), 500);
        }
    }

    public function getFeatureHistory(Request $request, ?string $featureName = null)
    {
        try {
            $user = $request->user();
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            
            $history = $this->engineeredFeatureService->getFeatureHistory(
                $user, 
                $featureName,
                $startDate,
                $endDate
            );
            
            return $this->responseJSON($history, "Feature history retrieved successfully");
            
        } catch (\Exception $e) {
            return $this->responseJSON(null, "Failed to retrieve feature history: " . $e->getMessage(), 500);
        }
    }
}