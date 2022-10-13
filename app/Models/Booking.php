<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Object_;

class Booking extends BaseModel
{
    protected $table = 'bookings';

    protected $primaryKey = 'booking_id';

    protected $guarded =[];

    /**
     * @param $event object
     * @param $data[booking_date] string Format: Y-m-d
     * @param $data['booking_time] string Format H:i:s
     * @return string
     */
    public static function validateBookingData($event, $data){
        $booking_date = $data['booking_date'];
        $booking_time = $data['booking_time'];

        if(date('Y-m-d H:i:s') > ($booking_date.' '.$booking_time)){
            return 'Invalid date slot selected';
        }

        if(!$event->checkMaxBooking($event)){
            return 'Booking slot over';
        }
        if(!$event->checkValidDateForEvent($booking_date))
            return 'Booking cannot be done for this date';

        if(!$event->checkValidSlotForEvent($booking_time))
            return 'Selected slot is available for booking. Please select different slot';

        return true;
    }


    /**
     * @param $event Object_
     * @param $data[booking_date] string
     * @param $data['booking_time] string
     * @param $data['first_name] string
     * @param $data['last_name] string
     * @param $data['email'] string
     * @return string
     */
    public static function createBooking($event, $data){
        try{
            DB::table('booking_count')->insertOrIgnore([
                'count_id' => 0,
                'event_id' => $data['event_id'],
                'date_time' => $data['booking_date'].' '.$data['booking_time']
            ]);
    
            $affected = DB::table('booking_count')
                ->where('event_id', $data['event_id'])
                ->where('date_time', $data['booking_date'].' '.$data['booking_time'])
                ->where('count', '<', $event->max_bookings)
                ->update(['count' => DB::raw('count+1')]);
    
            if($affected > 0){
                $booking_start = $data['booking_time'];
                $booking_end = date('H:i:s', strtotime('+'.($event->session_duration??0).' minutes', strtotime($booking_start)));
                $booking = Booking::create([
                    'event_id' => $event->event_id,
                    'booking_date' => $data['booking_date'],
                    'booking_start_time' => $booking_start,
                    'booking_end_time' => $booking_end,
                    'customer_email' => $data['email'],
                    'customer_first_name' => $data['first_name'],
                    'customer_last_name' => $data['last_name'],
                    'booked_on' => date('Y-m-d H:i:s')
                ]);
                return $booking;
            }else{
                return 'already_booked';
            }
        }
        catch(Exception $e){
            return $e;
        }
    }


    /*
     * This function returns availability of slots which are booked for future dates
     */
    public static function getBookingsStatus(){
        $date_start = date('Y-m-d').' 00:00:00';

        $occupied_slots = Event::join('booking_count', 'booking_count.event_id','=', 'events.event_id')
            ->select('events.event_id', 'date_time', 'count', 'max_bookings')
            ->where('date_time', '>=', $date_start)
            ->get();

        $event_occupied_slots =[];

        foreach($occupied_slots as $slot){
            if(!isset($event_occupied_slots[$slot->event_id]))
                $event_occupied_slots[$slot->event_id] = [];
            $date_time = explode(' ', $slot->date_time);
            $date = $date_time[0];
            $time = $date_time[1];
            if(!isset($event_occupied_slots[$slot->event_id][$date]))
                $event_occupied_slots[$slot->event_id][$date] = [];
            $event_occupied_slots[$slot->event_id][$date][]=[
                'time' => $time,
                'available_count' => $slot->max_bookings-($slot->count??0)
            ];
        }

        return $event_occupied_slots;

    }

}
