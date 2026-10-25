<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Managing admin accounts is limited to super admins. */
class AdminsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()?->super_admin, 403, 'Only super admins can manage admin accounts.');

            return $next($request);
        });
    }

    public function index()
    {
        return view('dashboard.admins.index', [
            'admins' => Admin::orderByDesc('super_admin')->orderBy('name')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('dashboard.admins.form', ['admin' => new Admin(['status' => 'active'])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['password'] = Hash::make($data['password']);
        $data['super_admin'] = $request->boolean('super_admin');
        $admin = Admin::create($data);

        return redirect()->route('admins.index')->with('success', "Admin {$admin->name} created.");
    }

    public function edit(Admin $admin)
    {
        return view('dashboard.admins.form', ['admin' => $admin]);
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $request->validate($this->rules($admin));
        $data['super_admin'] = $request->boolean('super_admin');

        if ($admin->id === Auth::id()) {
            // You cannot lock yourself out or drop your own privileges.
            $data['super_admin'] = true;
            $data['status'] = 'active';
        } elseif (! $data['super_admin'] && $admin->super_admin && Admin::where('super_admin', true)->count() <= 1) {
            return back()->withInput()->with('danger', 'At least one super admin is required.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $admin->update($data);

        return redirect()->route('admins.index')->with('success', "Admin {$admin->name} updated.");
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === Auth::id()) {
            return back()->with('danger', 'You cannot delete your own account.');
        }
        if ($admin->super_admin && Admin::where('super_admin', true)->count() <= 1) {
            return back()->with('danger', 'At least one super admin is required.');
        }
        $admin->delete();

        return back()->with('success', "Admin {$admin->name} deleted.");
    }

    private function rules(?Admin $admin = null): array
    {
        $id = $admin?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($id)],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('admins', 'username')->ignore($id)],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('admins', 'phone_number')->ignore($id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => [$admin ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }
}
