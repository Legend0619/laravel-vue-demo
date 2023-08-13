<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeHistory;

class UserController extends Controller
{
    // get all time history entries by user id
    public function index(): \Illuminate\Http\JsonResponse
    {
        $user = Auth()->user();

        $history = TimeHistory::where('user_id', '=', $user->id)->get();
        return response()->json($history);
    }

    // update login and logout time by user id
    public function update_time(Request $request)
    {
        $id = $request->id;
        $result = TimeHistory::where('id', '=', $id)->first();
        $result->login = $request->login;
        $result->logout = $request->logout;
        $result->save();
        return response()->json("success");
    }
}
