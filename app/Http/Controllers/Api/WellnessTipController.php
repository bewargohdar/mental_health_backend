<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\WellnessTip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WellnessTipController extends BaseApiController
{
    /**
     * Get wellness tips (public)
     */
    public function index(Request $request): JsonResponse
    {
        $language = $request->get('language', $request->header('Accept-Language', 'en'));
        
        // Normalize language code
        $language = substr($language, 0, 2);
        if (!in_array($language, ['en', 'ar', 'ku'])) {
            $language = 'en';
        }

        $tips = WellnessTip::active()
            ->byLanguage($language)
            ->ordered()
            ->limit($request->get('limit', 5))
            ->get();

        return $this->success($tips, 'Wellness tips retrieved');
    }

    /**
     * Get random wellness tip
     */
    public function random(Request $request): JsonResponse
    {
        $language = $request->get('language', $request->header('Accept-Language', 'en'));
        $language = substr($language, 0, 2);
        
        if (!in_array($language, ['en', 'ar', 'ku'])) {
            $language = 'en';
        }

        $tip = WellnessTip::active()
            ->byLanguage($language)
            ->inRandomOrder()
            ->first();

        if (!$tip) {
            // Fallback to English if no tip in requested language
            $tip = WellnessTip::active()
                ->byLanguage('en')
                ->inRandomOrder()
                ->first();
        }

        return $this->success($tip, 'Random tip retrieved');
    }

    /**
     * Get tips by category
     */
    public function byCategory(string $category, Request $request): JsonResponse
    {
        $language = $request->get('language', $request->header('Accept-Language', 'en'));
        $language = substr($language, 0, 2);

        $tips = WellnessTip::active()
            ->where('category', $category)
            ->byLanguage($language)
            ->ordered()
            ->get();

        return $this->success($tips, 'Tips by category retrieved');
    }
}
