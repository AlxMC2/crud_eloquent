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
        $users=User::query()
            ->when(
                $request->has('username'), 
                fn ($query) => $query->where('username','like','%'.$request->input('username').'%')
            )
            ->when(
                $request->has('username'), 
                fn ($query) => $query->where('email','like','%'.$request->input('email').'%')
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

        $user = User::create($data);
        
        return response()->json(UserResource::make($user), 201);
    }

    public function show(User $user){
        return UserResource::make($user);
    }

    public function update(Request $request, User $user)
{
    $data = $request->validate([
        'username' => ['sometimes', 'string', 'max:255', 'unique:users,username,' . $user->id],
        'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        'name' => ['sometimes', 'string', 'max:255'],
        'lastname' => ['sometimes', 'string', 'max:255']
    ]);

    $user->update($data);

    return UserResource::make($user);
}
}
