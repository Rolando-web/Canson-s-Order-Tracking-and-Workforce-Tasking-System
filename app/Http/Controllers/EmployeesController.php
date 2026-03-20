<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeesController extends Controller
{
    public function index()
    {
        $colors = ['bg-emerald-500', 'bg-blue-500', 'bg-purple-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'];

        $allEmployees = User::orderBy('name')->get();

        $employees = $allEmployees->map(function ($emp, $index) use ($colors) {
            $nameParts = explode(' ', $emp->name, 2);
            return [
                'id'      => $emp->User_Id,
                'first'   => $nameParts[0] ?? $emp->name,
                'last'    => $nameParts[1] ?? '',
                'role'    => $emp->role,
                'contact' => $emp->contact_number ?? '—',
                'avatar'  => $emp->profile_image_url,
                'status'  => 'Active',
                'color'   => $colors[$index % count($colors)],
            ];
        });

        $totalEmployees = $allEmployees->count();
        $activeCount    = $totalEmployees;
        $inactiveCount  = 0;

        return view('pages.employees', compact('employees', 'totalEmployees', 'activeCount', 'inactiveCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'empFirstName' => 'required|string|max:100',
            'empLastName'  => 'required|string|max:100',
            'empRole'      => 'required|in:employee,admin,super_admin',
            'empContact'   => ['required', 'string', 'regex:/^09\d{9}$/'],
            'password'     => 'nullable|string|min:6',
        ]);

        $name = trim($validated['empFirstName'] . ' ' . $validated['empLastName']);

        $user = User::create([
            'name'           => $name,
            'role'           => $validated['empRole'],
            'contact_number' => $validated['empContact'],
            'password'       => Hash::make($validated['password'] ?? 'password123'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'employee' => $user]);
        }

        return redirect()->back()->with('success', 'Employee added successfully.');
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'empFirstName' => 'required|string|max:100',
            'empLastName'  => 'required|string|max:100',
            'empRole'      => 'required|in:employee,admin,super_admin',
            'empContact'   => ['required', 'string', 'regex:/^09\d{9}$/'],
            'password'     => 'nullable|string|min:6',
        ]);

        $name = trim($validated['empFirstName'] . ' ' . $validated['empLastName']);
        $updateData = [
            'name'           => $name,
            'role'           => $validated['empRole'],
            'contact_number' => $validated['empContact'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $employee->update($updateData);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'employee' => $employee]);
        }

        return redirect()->back()->with('success', 'Employee updated successfully.');
    }

    public function destroy(Request $request, User $employee)
    {
        $employee->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Employee removed successfully.');
    }
}