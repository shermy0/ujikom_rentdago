<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerNotificationController;




// routes/api.php
Route::post('/midtrans/callback', [CustomerOrderController::class, 'midtransCallback']);

// notifications managed in web.php for session auth
