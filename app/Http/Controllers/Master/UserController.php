<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreUserRequest;
use App\Http\Requests\Master\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $data = User::with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('master.users.index', compact('data', 'roles'));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'jabatan'  => $request->jabatan,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => $request->status,
        ]);

        $user->syncRoles([$request->role]);

        return response()->json(['success' => true]);
    }

    public function edit(User $user): JsonResponse
    {
        return response()->json([
            'id'      => $user->id,
            'name'    => $user->name,
            'jabatan' => $user->jabatan,
            'phone'   => $user->phone,
            'email'   => $user->email,
            'status'  => $user->status,
            'role'    => $user->roles->first()?->name ?? '',
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = [
            'name'    => $request->name,
            'jabatan' => $request->jabatan,
            'phone'   => $request->phone,
            'email'   => $request->email,
            'status'  => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return response()->json(['success' => true]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(['success' => true]);
    }
}
