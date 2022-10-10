<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends BaseModel
{
    protected $table = 'events';

    protected $primaryKey = 'event_id';


    public function holidays(){
        return $this->belongsToMany('App\Models\PublicHoliday', 'event_holidays', 'event_id', 'holiday_id');
    }

    public function breaks(){
        return $this->hasMany('App\Models\BreakTime', 'event_id');
    }

    /**
     * This function return event object if event is valid
     *
     * @param $event_id
     * @return string|object
     */
    public static function getValidEvent($event_id){
        $event = Event::find($event_id);
        if(!$event)
            return 'No such event exists';
        return $event;
    }

    /**
     * This function checks if selected slot is valid for selected event
     * @param $book_time string Time in H:i:s format
     * @return bool
     */
    public function checkValidSlotForEvent($book_time){
        $slot_start_time = $this->opening_time;
        $total_time_taken_per_slot = ($this->session_duration ?? 0) + ($this->break_duration ?? 0);
        $breaks = $this->breaks;
        
        $slotTime = strtotime($slot_start_time);
        $slotTimeHour = date('H:i',$slotTime);
        
        $bookingTime = strtotime($book_time);
        $bookingTimeHour = date('H:i',$bookingTime);
        
        while($slot_start_time < $this->closing_time){
            
            if($slotTimeHour == $bookingTimeHour && $this->checkBreakSafe($breaks, $book_time)){
                return true;
            }
            $slot_start_time = date('H:i:s', strtotime('+'.$total_time_taken_per_slot.' minutes', strtotime($slot_start_time)));
        }

        return false;
    }

    /*
     * Checks id a slot is not overlapping in break
     */
    public function checkBreakSafe($breaks, $book_time){
        $start_time = $book_time;
        $end_time = date('H:i:s', strtotime('+'.($this->session_duration??0).' minutes', strtotime($start_time)));
        
        foreach($breaks as $b){
              if($end_time <= $b->break_start_time){
                  continue;
              }else if($start_time >= $b->break_end_time){
                  continue;
              }else
                  return false;
        }
        return true;
    }


    /*
     * Check for valid booking date
     */

    public function checkValidDateForEvent($booking_date){
        if($booking_date > date('Y-m-d', strtotime('+'.($this->future_days??0).' days')))
            return false;

        $holidays = $this->holidays;
        if(!$this->checkPublicHolidaySafe($holidays, $booking_date))
            return false;

        return true;
    }


    /*
     * Checks if date is not in public holiday
     */
    public function checkPublicHolidaySafe($holidays, $booking_date){

        foreach($holidays as $h){
            if($h->date == $booking_date){
                return false;
            }
        }
        return true;
    }

}
