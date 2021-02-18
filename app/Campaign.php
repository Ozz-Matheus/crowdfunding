<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    public $guarded = [];

    protected $dates = ['end_date'];

    public function get_category(){
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function feature_media(){
        return $this->belongsTo(Media::class, 'feature_image');
    }

    public function amount_prefilled(){
        $amount = $this->amount_prefilled;
        if ($amount){
            return explode(',', $amount);
        }
        return false;
    }
    
    public function feature_img_url($full_size = false){
        return media_image_uri($this->feature_media);
    }

    public function rewards(){
        return $this->hasMany(Reward::class);
    }

    public function updates(){
        return $this->hasMany(Update::class)->orderBy('created_at', 'desc');
    }

    public function faqs(){
        return $this->hasMany(Faq::class);
    }

    public function days_left(){
        $diff = strtotime($this->end_date)-time();//time returns current time in seconds

        if ($diff > 0){
            return floor($diff/(60*60*24));//seconds/minute*minutes/hour*hours/day)
        }
        return 0;
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function payments(){
        return $this->hasMany(Payment::class);
    }
    public function success_payments(){
        return $this->hasMany(Payment::class)->whereStatus('success');
    }
    
    public function percent_raised(){
        $raised = $this->success_payments()->sum('amount');
        $goal = $this->goal;

        $percent = 0;
        if ($raised > 0){
            $percent = round(($raised * 100) / $goal, 0, PHP_ROUND_HALF_DOWN);
        }
        return $percent;
    }

    public function amount_raised(){
        $raised = $this->success_payments()->sum('amount');
        $user_commission_percent = $this->campaign_owner_commission;

        $user_commission = 0;
        $platform_owner_commission = 0;

        if ($raised > 0 && $user_commission_percent != null){
            $user_commission = ($raised * $user_commission_percent) / 100;
            $platform_owner_commission = $raised - $user_commission;
        }
        
        return (object) array(
            'amount_raised'                 => $raised,
            'campaign_owner_commission'     => $user_commission,
            'platform_owner_commission'     => $platform_owner_commission,
        );
    }

    /**
     * @return bool
     * Is has active campaign
     */
    public function is_ended(){
        if ($this->end_method == 'end_date'){
            if ($this->end_date < Carbon::today()->toDateString()){
                return true;
            }
        }elseif ($this->end_method == 'goal_achieve'){
            $raised = $this->success_payments()->sum('amount');
            if ($this->goal <= $raised){
                return true;
            }
        }

        return false;
    }
    

    public function scopeActive($query){
        return $query->where('status', 1);
    }
    public function scopeBlocked($query){
        return $query->where('status', 2);
    }
    public function scopePending($query){
        return $query->where('status', 0);
    }
    public function scopeExpired($query){
        return $query->whereDate('end_date', '<', Carbon::today()->toDateString());
    }
    public function scopeFunded($query){
        return $query->where('is_funded', 1);
    }
    public function scopeStaff_picks($query){
        return $query->where('is_staff_picks', 1);
    }

    public function requested_withdrawal(){
        return $this->hasOne(Withdrawal_request::class);
    }


    public function statusContext(){
        $status = '';
        switch ($this->status){
            case 0:
                $status = '<div class="pl-2 pr-2 bg-success text-white">'.__('app.pending').'</div>';
                break;
            case 1:
                $status = '<div class="pl-2 pr-2 bg-success text-white">'.__('app.approved').'</div>';
                break;
            case 2:
                $status = '<div class="pl-2 pr-2 bg-warning text-white">'.__('app.blocked').'</div>';
                break;
        }
    }

}
