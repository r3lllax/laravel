<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\AddUsToWorkshiftRequest;
use App\Http\Requests\CloseRequest;
use App\Http\Requests\WorkShiftRequest;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderMenu;
use App\Models\Role;
use App\Models\ShiftWorker;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Type;

class WorkShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(WorkShiftRequest $request)
    {
        $start_time = $request->start;
        $end_time = $request->end;
        $date_now = date("Y-m-d H:i");
        $date_now = explode(' ',$date_now);
        $date_now[1] = explode(':',$date_now[1]);
        $date_now[1][0] =strval(intval($date_now[1][0])+3);
        $date_now[1][0] = strlen($date_now[1][0])==1?"0" . $date_now[1][0]:$date_now[1][0];
        $date_now[1] = implode(":",$date_now[1]);
        $date_now = implode(" ",$date_now);
        if($start_time>$date_now){
            if($end_time>$start_time){
                return $request;
            }
        }
        throw new ApiException(422,"Workshift start date-time must be greater then now date-time");

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
        $res = WorkShift::where(['id'=>$id])->first();
        if($res->active == 0){
            throw new ApiException(403,"Forbidden. The shift is already closed!");
        }
        return $res;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $res = WorkShift::where(['active'=>1])->first();
        if(is_null($res) == false){
            throw new ApiException(403,"Forbidden. There are open shifts!");
        }
        //Айдишнки смены для открытия
        return $id;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function AddUsToWorkShift($id,AddUsToWorkshiftRequest $request)
    {


        $res = WorkShift::all();
        $resTemp = array();
        $resTempIndex =0;
        for($i=0;$i<count($res);$i++){
            if($res[$i]->id==$id){
                $resTemp[$resTempIndex] = $res[$i];
                $resTempIndex++;
            }
        }

        $res = $resTemp;
        unset($resTemp);
        unset($resTempIndex);
        if(count($res)==0){
            throw new ApiException(403,"Forbidden. The workshift doesnt exists!");
        }

        //Получение количества записей шифтворкерс
        $arr = ShiftWorker::all();
        $arr2 = array();
        $arr2Index = 0;
        for($i=0;$i<count($arr);$i++){
            if($arr[$i]->work_shift_id==$id){
                $arr2[$arr2Index] = $arr[$i];
                $arr2Index++;
            }
        }
        $arr = $arr2;
        unset($arr2);
        unset($arr2Index);
        foreach ($arr as $item){
            if ($item->user_id==$request->user_id){
                throw new ApiException(403,"Forbidden. The worker is already on shift!");
            }
        }

        $request = ["user_id" => $request->user_id, "status" => "added"];

        return $request;
    }
    public function OrdersForWorkShift(WorkShift $workShift,Request $request)
    {
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
