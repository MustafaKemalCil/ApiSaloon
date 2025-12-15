<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    // Çalışan listeleme
    public function index()
    {
        $employees = User::where('position', '!=', 'Admin')
                         ->orderBy('first_name')
                         ->get();

        return response()->json($employees);
    }

    // Yeni çalışan ekleme
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:50',
            'position'   => 'nullable|string|max:100',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        $data = $request->only(['first_name', 'last_name', 'email', 'phone', 'position']);
        $data['password'] = Hash::make($request->password);

        $employee = User::create($data);

        return response()->json([
            'success' => true,
            'employee' => $employee
        ], 201);
    }

    // Çalışan güncelleme
    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $id,
            'phone'      => 'nullable|string|max:50',
            'position'   => 'nullable|string|max:100',
        ]);

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'employee' => $employee
        ]);
    }

    // Çalışan silme
    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Çalışan silindi'
        ]);
    }
}
