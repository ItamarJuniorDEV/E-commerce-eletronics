<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
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
}
