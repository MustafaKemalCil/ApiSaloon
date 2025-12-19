<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
/**
 * @OA\Tag(
 *     name="Employee",
 *     description="Çalışan yönetimi API"
 * )
 */
class EmployeeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/employees",
     *     summary="Tüm çalışanları listeler",
     *     tags={"Employee"},
     *     @OA\Response(
     *         response=200,
     *         description="Çalışan listesi",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string", nullable=true),
     *                 @OA\Property(property="position", type="string", nullable=true)
     *             )
     *         )
     *     )
     * )
     */
    // Çalışan listeleme
    public function index()
    {
        $employees = User::where('position', '!=', 'Admin')
                         ->orderBy('first_name')
                         ->get();

        return response()->json($employees);
    }

    // Yeni çalışan ekleme
     /**
     * @OA\Post(
     *     path="/api/employees",
     *     summary="Yeni çalışan ekler",
     *     tags={"Employee"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","password","password_confirmation"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="position", type="string"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Çalışan başarıyla eklendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="employee", type="object")
     *         )
     *     )
     * )
     */
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
     /**
     * @OA\Put(
     *     path="/api/employees/{id}",
     *     summary="Çalışan günceller",
     *     tags={"Employee"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="position", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Çalışan güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="employee", type="object")
     *         )
     *     )
     * )
     */
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
     /**
     * @OA\Delete(
     *     path="/api/employees/{id}",
     *     summary="Çalışanı siler",
     *     tags={"Employee"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Çalışan silindi",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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
