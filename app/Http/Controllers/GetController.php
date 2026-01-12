<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class GetController extends Controller
{
    public function getData(Request $request, $dsn_id)
    {
        // Fetch courses based on department_id
        $courses = Position::where('designation_id', $dsn_id)->get(['id', 'position_title']); // Adjust column names

        // Return as JSON response
        return response()->json($courses);
    }
}
