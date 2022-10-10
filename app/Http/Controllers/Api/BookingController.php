<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{

    /**
     * List all events, with parameters and slots availability
     * @param Request $request
     * @return array
     */
    public function view(Request $request){

        $events = Event::with(['breaks', 'holidays'])->get();

        $occupied_slots = Booking::getBookingsStatus();

        foreach($events as $e){
            $e->occupied_slots = $occupied_slots[$e->event_id]??[];
        }

        return [
            'success' => true,
            'event_detail' => $events
        ];

    }


    /**
     * Book a slot
     *
     * It books a time slot from available slots. It validates the time slot to be booked with barber configured parameters
     *
     * @bodyParam event_id integer required
     * @bodyParam booking_date string required Date in Y-m-d format.
     * @bodyParam booking_time string required Start time in H:i format.
     * @return void
     */
    public function book(Request $request){
        $request = ($request->json()->all());
        foreach($request as $data){
            //dd($data);
            // $request->validate([
            //     //'event_id' => 'required|integer',
            //     'booking_date' => 'required|date_format:Y-m-d',
            //     'booking_time' => 'required|date_format:H:i:s',
            //     'email' => 'required|email',
            //     'first_name' => 'required|string',
            //     'last_name' => 'nullable|string'
            //  ]);
     
             //check valid event
             $event = Event::getValidEvent($data['event_id']);
             if( !($event instanceof Event))
                 return response()->json([
                     'success' => false,
                     'message' => $event
                 ], 400);
            $bookingData = ['booking_date'=>$data['booking_date'],'booking_time'=>$data['booking_time']];
            $result = Booking::validateBookingData($event, $bookingData);
     
             if($result !== true){
                 return response()->json([
                     'success' => false,
                     'message' => $result
                 ], 400);
             }
     
             // initiate booking
             $bookingData = ['event_id'=>$data['event_id'],
             'booking_date'=>$data['booking_date'],
             'booking_time'=>$data['booking_time'],
             'email'=>$data['email'],
             'first_name'=>$data['first_name'],
             'last_name'=>$data['last_name']
            ];
             $booking = Booking::createBooking($event, $bookingData);
     
             if($booking === 'already_booked')
                 return response()->json([
                     'success' => false,
                     'message' => 'Time slot is full. Please select another time'
                 ], 400);
             else
                return [
                    'success' => true,
                    'booking_detail' => $booking
                ];
        }
    }
}
