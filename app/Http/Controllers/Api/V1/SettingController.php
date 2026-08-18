<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $group = $request->query('group');

        if ($group) {
            $settings = Setting::where('group', $group)->get();
        } else {
            $settings = Setting::all();
        }

        return response()->json([
            'data' => SettingResource::collection($settings),
        ]);
    }

    /**
     * Bulk update. The settings screen saves a whole group at once, and doing
     * that as one request keeps it atomic instead of N partial writes.
     */
    public function updateMany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $existing = Setting::where('key', $key)->first();
            Setting::set($key, $value, $existing?->group ?? 'general');
        }

        return response()->json([
            'data' => $this->flatten(),
            'message' => 'Settings updated successfully.',
        ]);
    }

    /** key => value map, which is what the dashboard and templates want. */
    private function flatten(): array
    {
        return Setting::all()
            ->mapWithKeys(fn(Setting $s) => [
                $s->key => is_array($s->value) ? (reset($s->value) ?: '') : $s->value,
            ])
            ->all();
    }

    public function show($key): SettingResource
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return new SettingResource($setting);
    }

    public function update(UpdateSettingRequest $request, $key): SettingResource
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        $setting->update($request->only('value', 'group'));

        return new SettingResource($setting);
    }

    public function store(Request $request): SettingResource
    {
        $request->validate([
            'key' => 'required|string|unique:settings',
            'value' => 'required',
            'group' => 'nullable|string',
        ]);

        $setting = Setting::create($request->only('key', 'value', 'group'));

        return new SettingResource($setting);
    }

    public function destroy($key): JsonResponse
    {
        Setting::where('key', $key)->firstOrFail()->delete();

        return response()->json([
            'message' => 'Setting deleted successfully',
        ], 204);
    }

    public function getGrouped(): JsonResponse
    {
        $grouped = Setting::all()->groupBy('group')->mapWithKeys(function ($items, $group) {
            return [$group => $items->pluck('value', 'key')];
        });

        return response()->json([
            'data' => $grouped,
        ]);
    }
}
