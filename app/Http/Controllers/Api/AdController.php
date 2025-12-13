<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Http\Resources\AdResource;
use App\Http\Services\Api\AdService;
use Illuminate\Http\Request;

class AdController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    public function store(StoreAdRequest $request)
    {
        $user = $request->user();

        $ad = $this->adService->createAd($user, $request->validated());

        return response()->json(
            generate_response(new AdResource($ad), 1, false, "Ad created successfully")
        );
    }

    public function myAds(Request $request)
    {
        $user = $request->user();

        $ads = $this->adService->getUserAds($user);

        return response()->json(
            generate_response(AdResource::collection($ads), 1, false, "My ads retrieved successfully")
        );
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            return response()->json(
                generate_response([], 0, true, "Invalid ad ID"),
                400
            );
        }

        $ad = $this->adService->getAdById($id);

        if (!$ad) {
            return response()->json(
                generate_response([], 0, true, "Ad not found"),
                404
            );
        }

        return response()->json(
            generate_response(new AdResource($ad), 1, false, "Ad retrieved successfully")
        );
    }
}
