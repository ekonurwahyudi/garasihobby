<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();

        // Structured permissions grouped by module
        $structured = $this->getStructuredPermissions();

        return view('roles.index', compact('roles', 'structured'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    public function edit(Role $role): JsonResponse
    {
        return response()->json([
            'id'          => $role->id,
            'name'        => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Role masih digunakan oleh user.'], 422);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(['success' => true]);
    }

    private function getStructuredPermissions(): array
    {
        $permissions = Permission::orderBy('name')->get();

        // Definisi module → page mapping
        $groups = [
            'Dashboard' => [
                ['page' => 'dashboard', 'label' => 'Dashboard', 'modules' => ['dashboard'], 'actions' => []],
            ],
            'Master Data' => [
                ['page' => 'users', 'label' => 'User', 'modules' => ['users'], 'actions' => []],
                ['page' => 'roles', 'label' => 'Role & Permission', 'modules' => ['roles'], 'actions' => []],
                ['page' => 'checklist', 'label' => 'Item Checklist', 'modules' => ['checklist'], 'actions' => []],
                ['page' => 'materials', 'label' => 'Material', 'modules' => ['materials'], 'actions' => []],
                ['page' => 'promo', 'label' => 'Paket Promo', 'modules' => ['promo'], 'actions' => []],
                ['page' => 'finance-master', 'label' => 'Kategori & Item Keuangan', 'modules' => ['finance-master'], 'actions' => []],
                ['page' => 'bank-accounts', 'label' => 'Rekening Bank', 'modules' => ['bank-accounts'], 'actions' => []],
            ],
            'Operasional' => [
                ['page' => 'customers', 'label' => 'Pelanggan', 'modules' => ['customers'], 'actions' => []],
                ['page' => 'orders', 'label' => 'Order', 'modules' => ['orders'], 'actions' => []],
                ['page' => 'purchases', 'label' => 'Pembelian Material', 'modules' => ['purchases'], 'actions' => []],
            ],
            'Keuangan' => [
                ['page' => 'finance-transactions', 'label' => 'Input Keuangan', 'modules' => ['finance-transactions'], 'actions' => []],
            ],
            'Pengaturan' => [
                ['page' => 'notifications', 'label' => 'Notifikasi', 'modules' => ['notifications'], 'actions' => []],
            ],
        ];

        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name);
            $module = $parts[0] ?? '';
            $action = $parts[1] ?? '';

            foreach ($groups as $groupName => &$pages) {
                foreach ($pages as &$pg) {
                    if (in_array($module, $pg['modules'])) {
                        $pg['actions'][] = ['name' => $perm->name, 'action' => $action];
                    }
                }
            }
        }

        // Remove pages with no actions, remove 'modules' key from output
        foreach ($groups as $groupName => &$pages) {
            $pages = array_values(array_filter($pages, fn($pg) => count($pg['actions']) > 0));
            foreach ($pages as &$pg) {
                unset($pg['modules']);
            }
        }

        return array_filter($groups, fn($pages) => count($pages) > 0);
    }
}
