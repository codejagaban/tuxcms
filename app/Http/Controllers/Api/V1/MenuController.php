<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Resources\MenuResource;
use App\Http\Resources\MenuItemResource;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        $menus = Menu::with(['items' => function ($query) {
            $query->with(['children' => function ($q) {
                $q->orderBy('order');
            }])->whereNull('parent_id')->orderBy('order');
        }])->get();

        return response()->json([
            'data' => MenuResource::collection($menus),
        ]);
    }

    public function show($slugOrId): MenuResource
    {
        $menu = Menu::with(['items' => function ($query) {
            $query->with(['children' => function ($q) {
                $q->orderBy('order');
            }])->whereNull('parent_id')->orderBy('order');
        }])
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        return new MenuResource($menu);
    }

    public function store(StoreMenuRequest $request): MenuResource
    {
        $menu = Menu::create($request->only('name', 'location'));

        if ($request->has('items')) {
            $this->createMenuItems($menu, $request->items);
        }

        return new MenuResource($menu->load(['items']));
    }

    public function update(Request $request, Menu $menu): MenuResource
    {
        $this->authorize('update', $menu);

        $menu->update($request->only('name', 'location'));

        if ($request->has('items')) {
            MenuItem::where('menu_id', $menu->id)->delete();
            $this->createMenuItems($menu, $request->items);
        }

        return new MenuResource($menu->load(['items']));
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $this->authorize('delete', $menu);

        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully',
        ], 204);
    }

    private function createMenuItems(Menu $menu, array $items, ?int $parentId = null): void
    {
        foreach ($items as $index => $item) {
            $menuItem = MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $parentId,
                'title' => $item['title'],
                'url' => $item['url'] ?? null,
                'target' => $item['target'] ?? '_self',
                'type' => $item['type'] ?? 'custom',
                'order' => $item['order'] ?? $index,
            ]);

            if (isset($item['children']) && is_array($item['children'])) {
                $this->createMenuItems($menu, $item['children'], $menuItem->id);
            }
        }
    }
}
