<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when(
                $request->has('username'),
                fn($query) => $query->where('username', 'like', '%' . $request->input('username') . '%')
            )
            ->when(
                $request->has('email'),
                fn($query) => $query->where('email', 'like', '%' . $request->input('email') . '%')
            )
            ->when(
                $request->input('is_trashed') === 'true',
                fn($query) => $query->onlyTrashed()
            )
            ->get();



        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Str::random(8); // Le colocamos una contraseña por defecto

        if (!isset($data['hiring_date'])) {
            $data['hiring_date'] = now();
        }

        $user = User::create($data);

        return response()->json(UserResource::make($user), 201);
    }

    public function show(User $user)
    {
        return UserResource::make($user);
    }

    public function partialUpdate(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'lastname' => ['sometimes', 'string', 'max:255'],
            'hiring_date' => ['sometimes', 'date'],
            'DUI' => ['sometimes', 'string', 'max:255', 'unique:users,DUI', 'regex:/^\d{8}-\d{1}$/'],
            'phone' => ['sometimes', 'string', 'max:255', 'regex:/^\d{8}$/'],
            'birth_date' => ['sometimes', 'date', 'before:today'],
        ]);

        $user->update($data);

        return UserResource::make($user);
    }

    public function update(Request $request, User $user)
    {
        // Validación con 'sometimes' - solo valida los campos enviados
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'lastname' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return UserResource::make($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'El usuario ha sido eliminado correctamente.'
        ]);
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $user->restore();

        return response()->json(['message' => 'Usuario restaurado correctamente.']);
    }


}