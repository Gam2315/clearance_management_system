<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NfcController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public NFC endpoint (no authentication required for external devices)
Route::post('/nfc-tap', [NfcController::class, 'storeTap'])
    ->middleware('throttle:nfc');

// Public NFC check endpoint (for checking if card is detected)
Route::get('/nfc-check', [NfcController::class, 'checkNfcCard'])
    ->middleware('throttle:60,1');

// Test endpoint to simulate NFC tap (for development/testing)
Route::post('/nfc-test', function(Request $request) {
    $uid = $request->input('uid', 'TEST' . strtoupper(substr(md5(time()), 0, 8)));

    DB::table('nfc_taps')->delete();
    DB::table('nfc_taps')->insert([
        'uid' => $uid,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Test NFC tap created',
        'uid' => $uid
    ]);
})->middleware('throttle:10,1');

// Protected API routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Internal NFC endpoint for authenticated users
    Route::post('/nfc-store-uid', [NfcController::class, 'storeUid']);
});
