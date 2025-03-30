<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\AddPositionRequest;
use App\Http\Requests\AddRequest;
use App\Models\Order;
use App\Models\ShiftWorker;
use App\Models\StatusOrder;
use App\Models\Table;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AddRequest $request)
    {
        //получаем токен и айдишник пользователя этого токена

        //Тут есть 2 способа, которые не сильно отличаются, первый это мой:

        //$token = $request->bearerToken();
        //$us = (User::where(['api_token'=>$token])->first())->id;

        // Второй это как нужно по идее
        $us = $request->user()->id;


        if ($request->work_shift_id){
            $variable =  WorkShift::where(['id'=>$request->work_shift_id])->first();
            //получаем смену с айдишником смены в которубю добавляем заказ, где в юзер айди есть наш юзер
            $usRes = array(ShiftWorker::where(['work_shift_id'=>$request->work_shift_id,'user_id'=>$us])->first());
            //Если нет результатов где смена = куда добавляем и пользователь = наш пользователь то выкидываем ошибку что его нет в этой смене
            if(is_null($usRes[0])){
                throw new ApiException(403,"Forbidden. You don't work this shift!");
            }
            if($variable->active==1){
                return $request;
            }
        }
        throw new ApiException(403,"Forbidden. The shift must be active!");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function CurrentOrder(Request $request,$id)
    {
        $Workerid = $request->user()->id;
        $Orders = Order::where(['id'=>$id,'shift_worker_id'=>$Workerid])->first();
        if(is_null($Orders) == true){
            throw new ApiException(403,"Forbidden. You did not accept this order!");
        }

        return Order::where(['id'=>$id])->get();


    }
    public function ChangeStatus($id,Request $request)
    {
        $UserToken = $request->bearerToken();
        //Получаем айдишник роли запроса, чтобы в дальнейшем дать разрешения для смены от повара или от официанта
        $userRoleId = User::where(['api_token'=>$UserToken])->get()[0]->role_id;
        
        $orders = Order::where(['id'=>$id])->get();
        $shiftWork = ShiftWorker::where(['id'=>$orders[0]->shift_worker_id])->get();
        $workShift = WorkShift::where(['id'=>$shiftWork[0]->work_shift_id])->get();
        if($workShift[0]->active!=1){
            throw new ApiException(403,"You cannot change the order status of a closed shift!");
        }
        $waiter = $shiftWork[0]->user_id;
        $user = $request->user()->id;
        if($waiter!=$user && $userRoleId==2){
            throw new ApiException(403,"Forbidden! You did not accept this order!");
        }
        $orderPreviousCode = StatusOrder::where(['id'=>$orders[0]->status_order_id])->get()[0]->code;
        $orderNewCode = $request->status;
        switch($userRoleId){
            //Если запрос от официанта то разрешена смена с принят на отменен и с готов на оплачен
            case 2:
                if(($orderPreviousCode == "taken" && $orderNewCode == "canceled") || ($orderPreviousCode=="ready" && $orderNewCode == "paid-up")){
                    $orders = Order::where(['id'=>$id])->get();
        
                    //Сохранение и вывод о успешной операции(пока что визуальная заглушка)
                    $orders[0]->status_order_id = StatusOrder::where(['code'=>$request->status])->get()[0]->id;
                    return $orders;
                }
                else{
                    throw new ApiException(403,"Forbidden! Can't change existing order status");
                }
                break;
            //Если запрос от повара то разрешена смена с принят на готовится и с готовится на готов
            case 3:
                if(($orderPreviousCode == "taken" && $orderNewCode == "preparing") || ($orderPreviousCode=="preparing" && $orderNewCode == "ready")){
                    $orders = Order::where(['id'=>$id])->get();
        
                    //Сохранение и вывод о успешной операции(пока что визуальная заглушка)
                    $orders[0]->status_order_id = StatusOrder::where(['code'=>$request->status])->get()[0]->id;
                    return $orders;
                }
                else{
                    throw new ApiException(403,"Forbidden! Can't change existing order status");
                }
                break;
        }
        
        return $orderPreviousCode;
    }
    public function AddPosInOrder(AddPositionRequest $request,$id)
    {
        $order = Order::where(['id'=>$id])->get();
        $shiftWork = ShiftWorker::where(['id'=>$order[0]->shift_worker_id])->get();
        $workShift = WorkShift::where(['id'=>$shiftWork[0]->work_shift_id])->get();
        if($workShift[0]->active!=1){
            throw new ApiException(403,"Forbidden! You cannot change the order status of a closed shift!");
        }
        $waiter = $shiftWork[0]->user_id;
        $user = $request->user()->id;
        if($waiter!=$user){
            throw new ApiException(403,"Forbidden! You did not accept this order!");
        }
        $OrderStatusCode = StatusOrder::where(['id'=>$order[0]->status_order_id])->get()[0]->code;

        if($OrderStatusCode == 'taken' || $OrderStatusCode == "preparing"){
            //Тут все готово(валидация), осталось сделать вывод(напоминалка про ресурсы)
            //Вывод информации о смене и заказе(с изменениями)
            return $order;

        }
        throw new ApiException(403,"Forbidden! Cannot be added to an order with this status ");

    }
    public function DelPosInOrder(Order $id,$delid,Request $request)
    {
        $workShiftActiveStatus = $id->workShift()->get()[0]->active;
        //Проверка активности смены
        if($workShiftActiveStatus!=1){
            throw new ApiException(403,"Forbidden! You cannot change the order status of a closed shift!");
        }
        $workshifter = $id->user()->get()[0];
        //Проверка принадлежность заказа к ползователю запроса
        if($request->user()->id!=$workshifter->id){
            throw new ApiException(403,"Forbidden! You did not accept this order!");
        }
        //Проверка статуса(если принят то продолжаем, если какой то другой - ошибку)
        if($id->status_order_id!=1){
            throw new ApiException(403,"Forbidden! Cannot be added to an order with this status ");
        }
        $pos = $id->positions()->get();
        $usPosInOtherPos = false;
        for($i = 0;$i<count($pos);$i++){
            if($pos[$i]->id==$delid){
                $usPosInOtherPos = true;
                break;
            }
        }
        if($usPosInOtherPos!= true){
            throw new ApiException(403,"Forbidden! Order havent this position!");
        }
        //Все проверки пройдены, удаление...
        return $delid;
    }
    public function OrdersInThisShift(Request $request)
    {
        //Активная смена(id)
        $workShiftID = WorkShift::where(['active'=>1])->get()[0]->id;

        $ordersThisWS = $workShift->orders()->get();
        $us = $request->user()->id;
        $usRes = array(ShiftWorker::where(['work_shift_id'=>$workShift->id,'user_id'=>$us])->first());
        if(is_null($usRes[0]) && $request->user()->role_id != 1){
            throw new ApiException(403,"Forbidden. You don't work this shift!");
        }


            //Добавить подсчет прибыли за смену
            // <-----------------> \\
            $cost = 0;

            //цикл заказов всех
            for ($i = 0; $i < count($ordersThisWS); $i++) {
                $menuInOrder = OrderMenu::where(['order_id' => $ordersThisWS[$i]->id])->get();
                // Цикл конкретного заказа
                $currenCost = 0;
                for ($j = 0; $j < count($menuInOrder); $j++) {
                    $count = $menuInOrder[$j]->count;
                    $dishName = Menu::where(['id' => $menuInOrder[$j]->menu_id])->get();
                    $cost += $dishName[0]->price * $count;
                    //Цена за заказ(не за все)!
                    $currenCost += $dishName[0]->price * $count;
                }
                $StrOrder = json_encode($ordersThisWS[$i]);

                $StrOrder = substr($StrOrder, 0, strlen($StrOrder) - 1);

                //Добавляю ключ заказов с массивом заказов
                $StrOrder .= ",\"price\":$currenCost}";

                //Обратно в json
                $ordersThisWS[$i] = json_decode($StrOrder);
            }
            //массив заказов за смену в строку
            $ordersSTR = strval($ordersThisWS);
            //json в строку
            $workShift = json_encode($workShift);
            //обрезаю } в конце чтобы добавить ключ
            $workShift = substr($workShift, 0, strlen($workShift) - 1);
            //Добавляю ключ заказов с массивом заказов
            $workShift .= ",\"orders\":$ordersSTR,\"amount_for_all\":$cost}";
            //Обратно в json
            $json = json_decode($workShift);
            return $json;
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
