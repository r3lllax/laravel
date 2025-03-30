<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
//Route::prefix('user') ->group(function (){
//    Route::get('/',[\App\Http\Controllers\UserController::class, 'index']);
//    Route::get('/{user}',[\App\Http\Controllers\UserController::class, 'show']);
//});
//вход
Route::post('/login',[\App\Http\Controllers\UserController::class,'login'])->withoutMiddleware(['auth:api']);
//выход
Route::get('/logout',[\App\Http\Controllers\UserController::class,'logout']);


Route::prefix('user')->group(function (){
    //вывод всех юзеров
    Route::get('/',[\App\Http\Controllers\UserController::class,'index'])->name('user.index');
    //добавление нового пользователя
    Route::post('/',[\App\Http\Controllers\UserController::class,'store'])->name('user.store');
});
Route::prefix('work-shift')->group(function (){
    //Создание смены
    Route::post('/',[\App\Http\Controllers\WorkShiftController::class,'index'])->name('work-shift.index');
    //Открытие (номер) смены
    Route::get('/{id}/open',[\App\Http\Controllers\WorkShiftController::class,'show'])->name('work-shift.show');
    //Закрытие (номер) смены
    Route::get('/{id}/close',[\App\Http\Controllers\WorkShiftController::class,'store'])->name('work-shift.store');
    //Добавить сотрудника на смену (номер)
    Route::post('/{id}/user',[\App\Http\Controllers\WorkShiftController::class,'AddUsToWorkShift'])->name('work-shift.AddUsToWorkShift');
    //Заказы за смену (номер)
    Route::get('/{workShift}/order',[\App\Http\Controllers\WorkShiftController::class,'OrdersForWorkShift'])->middleware('role:admin|waiter')->name('work-shift.OrdersForWorkShift');
});
Route::prefix('order')->group(function (){

    //Создание нового заказа для столика
    Route::post('/',[\App\Http\Controllers\OrderController::class,'index'])->name('order.index');
    //Просмотр заказов текущей смены
    Route::get('/taken',[\App\Http\Controllers\OrderController::class,'OrdersInThisShift'])->name('order.OrdersInThisShift');
    //Просмотр конкретного заказа(номер)
    Route::get('/{id}',[\App\Http\Controllers\OrderController::class,'CurrentOrder'])->name('order.CurrentOrder');
    //Смена статуса заказа
    Route::patch('/{id}/change-status',[\App\Http\Controllers\OrderController::class,'ChangeStatus'])->middleware('role:cook|waiter')->name('order.ChangeStatus');
    //Добавление позиции в заказ
    Route::post('/{id}/position',[\App\Http\Controllers\OrderController::class,'AddPosInOrder'])->name('order.AddPosInOrder');
    //Удаление позиции из заказа
    Route::delete('/{id}/position/{delid}',[\App\Http\Controllers\OrderController::class,'DelPosInOrder'])->name('order.DelPosInOrder');

});
