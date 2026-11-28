<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\SectionTypes;
use App\Support\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionTypeController extends Controller
{
    /**
     * The section types a page with this template may hold, with the schema
     * the editor builds its menu and side panel from: the built-in types,
     * then the ones the rendering theme declares in its theme.json.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'template' => ['nullable', 'string', Rule::in(Site::templates())],
        ]);

        $theme = Site::pageTheme($request->input('template'));

        return response()->json([
            'data' => [
                'theme' => $theme,
                'types' => SectionTypes::schema($theme),
            ],
        ]);
    }
}
