<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::get('/', function () {
    return response()->json(['message' => 'Hello World!']);
});

function hasFailed($key, $rules)
{
    $validator = Validator::make(request()->all(), [$key => $rules]);
    return $validator->fails();
}

Route::group(['prefix' => 'auth'], function () {
    Route::get('/register', function () {
        $emailValidator = Validator::make(request()->all(), [
            'email' => 'required|string|max:255|unique:users',
        ]);

        if ($emailValidator->fails()) {
            return response()->json(['data' => 'unique'], 200);
        }

        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true], 422);
        }

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'pass' => request('password'),
        ]);

        return response()->json(['data' => "$user->id"]);
    });

    Route::get('/login', function () {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true], 422);
        }

        $user = User::where('email', request('email'))
            ->where('pass', request('password'))
            ->first();

        if (!$user) {
            return response()->json(['data' => 'not found'], 404);
        }

        if (!request('password') === $user->pass) {
            return response()->json(['error' => true], 401);
        }

        return response()->json(['data' => "$user->id"]);
    });

    Route::get('/update', function () {
        $user = User::find((int) request('id'));

        if (!$user) {
            return response()->json(['error' => true], 404);
        }

        $validator = Validator::make(request()->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => true], 422);
        }

        if (request('name')) {
            $user->name = request('name');
        }

        if (request('email')) {
            $user->email = request('email');
        }

        if (request('password')) {
            $user->pass = request('password');
        }

        $user->save();

        return response()->json(['data' => $user->id]);
    });

    Route::get('/get', function () {
        $user = User::find((int) request('id'));

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
        ]);
    });

    Route::get('/delete', function () {
        User::where('id', (int) request('id'))->delete();
        return response()->json(['data' => true]);
    });
});
