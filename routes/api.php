<?php

use App\Models\Task;
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
      'pass' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => 'invalid data'], 200);
    }

    $user = User::create([
      'name' => request('name'),
      'email' => request('email'),
      'pass' => request('pass'),
    ]);

    return response()->json(['data' => "$user->id"]);
  });

  Route::get('/login', function () {
    $validator = Validator::make(request()->all(), [
      'email' => 'required|string|max:255',
      'pass' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => 'invalid email or password'], 200);
    }

    $user = User::where('email', request('email'))
      ->where('pass', request('pass'))
      ->first();

    if (!$user) {
      return response()->json(['data' => 'not found'], 200);
    }

    if (!request('pass') === $user->pass) {
      return response()->json(['error' => 'not found'], 200);
    }

    return response()->json(['data' => "$user->id"]);
  });

  Route::get('/update', function () {
    $user = User::find((int) request('id'));

    if (!$user) {
      return response()->json(['error' => true], 200);
    }

    $validator = Validator::make(request()->all(), [
      'name' => 'nullable|string|max:255',
      'email' => 'nullable|string|email|max:255',
      'pass' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => true], 200);
    }

    if (request('name')) {
      $user->name = request('name');
    }

    if (request('email')) {
      $user->email = request('email');
    }

    if (request('pass')) {
      $user->pass = request('pass');
    }

    $user->save();

    return response()->json(['data' => [
      'name' => $user->name,
      'email' => $user->email,
      'pair-id' => $user->partner_id ?? "",
      'code' => $user->code,
    ]]);
  });

  Route::get('/get', function () {
    $user = User::find((int) request('id'));

    return response()->json([
      'name' => $user->name,
      'email' => $user->email,
      'pair-id' => $user->partner_id ?? "",
      'code' => $user->code,
    ]);
  });

  Route::get('/delete', function () {
    User::where('id', (int) request('id'))->delete();
    return response()->json(['data' => 'deleted']);
  });
});

Route::group(['prefix' => 'task'], function () {
  Route::get('get-all', function () {
    $user = User::find((int) request('id'));
    return response()->json($user->getTasks());
  });

  Route::get('get', function () {
    $task = Task::find((int) request('id'));

    return response()->json([
      'name' => $task->name,
      'status' => $task->status,
      'user_id' => $task->user_id,
      'partner_id' => $task->partner_id,
    ]);
  });

  Route::get('create', function () {
    $validator = Validator::make(request()->all(), [
      'name' => 'required|string|max:255',
      'user_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => 'invalid data'], 200);
    }

    $user = User::find((int) request('user_id'));
    $task = $user->createTask(request('name'));

    return response()->json(['data' => "$task->id"]);
  });

  Route::get('update', function () {
    $task = Task::find((int) request('id'));

    if (!$task) {
      return response()->json(['error' => 'not found'], 200);
    }

    $validator = Validator::make(request()->all(), [
      'name' => 'nullable|string|max:255',
      'status' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => 'invalid data'], 200);
    }

    if (request('name')) {
      $task->name = request('name');
    }

    if (request('status')) {
      $task->status = request('status');
    }

    $task->save();

    return response()->json(['data' => "$task->id"]);
  });

  Route::get('delete', function () {
    $task = Task::find((int) request('id'));

    if (!$task) {
      return response()->json(['error' => 'not found'], 200);
    }

    $task->delete();

    return response()->json(['data' => "deleted"]);
  });
});

Route::get('pair', function () {
  $user = User::find((int) request('user_id'));
  $pairUser = User::where('code', request('pair_code'))->first();

  if (!$pairUser) {
    return response()->json(['error' => 'not found'], 200);
  }

  if ($user->id === $pairUser->id) {
    return response()->json(['error' => 'self pair'], 200);
  }

  $user->setPartner($pairUser);

  return response()->json(['data' => "worked"]);
});
