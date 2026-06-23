<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
 
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('rentals');
 
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
 
        $users = $query->latest()->paginate(15)->withQueryString();
 
        return view('admin.users.index', compact('users'));
    }
 
    public function create()
    {
        return view('admin.users.create');
    }
 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:admin,staff,user',
            'phone'    => 'nullable|string|max:20',
        ]);
 
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
 
        AuditLog::write('USER_CREATED', "User \"{$user->name}\" ({$user->role}) created.", auth()->id());
 
        return redirect()->route('admin.users.index')
                         ->with('success', "User \"{$user->name}\" created.");
    }
 
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
 
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => 'required|in:admin,staff,user',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);
 
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $validated['password'] = Hash::make($request->password);
        }
 
        $old = $user->only(['name', 'email', 'role', 'is_active']);
        $user->update($validated);
 
        AuditLog::write('USER_UPDATED', "User \"{$user->name}\" updated.", auth()->id(),
            User::class, $user->id, $old, $user->fresh()->only(['name', 'email', 'role', 'is_active']));
 
        return redirect()->route('admin.users.index')
                         ->with('success', "User updated.");
    }
 
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
 
        $name = $user->name;
        AuditLog::write('USER_DELETED', "User \"{$name}\" deleted.", auth()->id());
        $user->delete();
 
        return redirect()->route('admin.users.index')
                         ->with('success', "User \"{$name}\" deleted.");
    }
}
