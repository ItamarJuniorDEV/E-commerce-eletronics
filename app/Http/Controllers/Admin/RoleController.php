<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/roles', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Role::create($data);

        return to_route('admin.roles.index');
    }
}
