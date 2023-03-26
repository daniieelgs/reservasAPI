<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservaFreeBookingRequest;
use App\Http\Requests\ReservaRequest;
use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {

        $r = new ReservaFreeBookingRequest();

        $r->merge($req->all());

        try{
            
            $r->validate($r->rules());

            if(!$this->checkDateTimeRequest($r)) return response('The datetimes is not applicable', 406)->header('Content-Type', 'text/plain');

            return response(json_encode(
                $this->findBusyBookings(
                    strtotime($r->input('data_inici')." ".$r->input('hora_inici')), 
                    strtotime($r->input('data_final')." ".$r->input('hora_final')))),
                200)->header('Content-Type', 'application/json');
    
        }catch (\Illuminate\Validation\ValidationException $e) {
        
            $reserves = Reserva::all();

            return response(json_encode($reserves), 200)->header('Content-Type', 'application/json');

        }
    }

    private function checkDateTimeRequest(ReservaFreeBookingRequest $req) : Bool {

        $dateInit = $req->input('data_inici');
        $timeInit = $req->input('hora_inici');
        $dateFinal = $req->input('data_final');
        $timeFinal = $req->input('hora_final');

        $initDateTime = strtotime("$dateInit $timeInit");
        $finalDateTime = strtotime("$dateFinal $timeFinal");

        return $initDateTime < $finalDateTime;
    }

    private function findBusyBookings($initDateTime, $finalDateTime){

        $all = Reserva::all()->toArray();
        
        return array_values(array_filter($all, fn($n) => 
            strtotime($n['data_final']." ".$n['hora_final']) > $initDateTime && strtotime($n['data_inici']." ".$n['hora_inici']) < $finalDateTime)); 

    }

    private function findFreeBookings($initDateTime, $finalDateTime){

        $jsonDateTime = fn($init, $final) => [
            'data_inici' => date('Y-m-d', $init),
            'hora_inici' => date('H:i:s', $init),
            'data_final' => date('Y-m-d', $final),
            'hora_final' => date('H:i:s', $final)
        ];

        $busyBookings = $this->findBusyBookings($initDateTime, $finalDateTime);

        $freeBookings = [];

        if(!empty($busyBookings)){

            $init = $initDateTime;

            for($i = 0; $i < count($busyBookings); $i++){

                array_push($freeBookings, $jsonDateTime($init, strtotime($busyBookings[$i]['data_inici']." ".$busyBookings[$i]['hora_inici'])));

                $init = strtotime($busyBookings[$i]['data_final']." ".$busyBookings[$i]['hora_final']);

            }

            if($finalDateTime > $init) array_push($freeBookings, $jsonDateTime($init, $finalDateTime));

        } else $freeBookings = [$jsonDateTime($initDateTime, $finalDateTime)];
        

        return $freeBookings;

    }

    private function checkBusyBooking(ReservaFreeBookingRequest $req, ?Reserva ...$bookingExcepted){

        $busy = $this->findBusyBookings(
            strtotime($req->input('data_inici')." ".$req->input("hora_inici")), 
            strtotime($req->input('data_final')." ".$req->input("hora_final")));

            
        if(!empty($bookingExcepted)){
            $except = array_map(fn($n) => $n->id, $bookingExcepted);

            $busy = array_filter($busy, fn($n) => !in_array($n['id'], $except));
        }

        return !empty($busy);
    }

    public function showFreeBookings(ReservaFreeBookingRequest $request){

        if(!$this->checkDateTimeRequest($request)) return response('The datetimes is not applicable', 406)->header('Content-Type', 'text/plain');

        return response(json_encode(
            $this->findFreeBookings(
                strtotime($request->input('data_inici')." ".$request->input('hora_inici')), 
                strtotime($request->input('data_final')." ".$request->input('hora_final')))),
            200)->header('Content-Type', 'application/json');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReservaRequest $request)
    {

        if(!$this->checkDateTimeRequest($request)) return response('The datetimes is not applicable', 406)->header('Content-Type', 'text/plain');
        if($this->checkBusyBooking($request)) return response('The datetimes is busy', 406)->header('Content-Type', 'text/plain');

        $newReserva = new Reserva();

        $newReserva->data_inici = $request->input('data_inici');
        $newReserva->hora_inici = $request->input('hora_inici');
        $newReserva->data_final = $request->input('data_final');
        $newReserva->hora_final = $request->input('hora_final');
        $newReserva->mail = $request->input('mail');

        $newReserva->save();

        return response(json_encode($newReserva), 201)->header('Content-Type', 'application/json');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reserva $reserva)
    {
        return response(json_encode($reserva), 200)->header('Content-Type', 'application/json');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReservaRequest $request, Reserva $reserva)
    {
        
        if(!$this->checkDateTimeRequest($request)) return response('The datetimes is not applicable', 406)->header('Content-Type', 'text/plain');
        if($this->checkBusyBooking($request, $reserva)) return response('The datetimes is busy', 406)->header('Content-Type', 'text/plain');

        $reserva->data_inici = $request->input('data_inici');
        $reserva->hora_inici = $request->input('hora_inici');
        $reserva->data_final = $request->input('data_final');
        $reserva->hora_final = $request->input('hora_final');
        $reserva->mail = $request->input('mail');

        $reserva->save();

        return response(json_encode($reserva), 200)->header('Content-Type', 'application/json');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reserva $reserva)
    {
        $reserva->delete();

        return response(json_encode($reserva), 200)->header('Content-Type', 'application/json');
    }
}
