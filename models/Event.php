<?php namespace KurtJensen\MyCalendar\Models;

use Carbon\Carbon;
use Model;
use RainLab\User\Models\User as UserModel;
use System\Classes\PluginManager;

/**
 * event Model
 */
class Event extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'kurtjensen_mycal_events';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['*'];

    protected $dates = ['date'];

    /**
     * @var array Relations
     */

    public $belongsToMany = [
        'categorys' => ['KurtJensen\MyCalendar\Models\Category',
            'table' => 'kurtjensen_mycal_categorys_events',
            'key' => 'event_id',
            'otherKey' => 'category_id',
        ],
    ];

    public $attributes = [
        'day' => '',
        'month' => '',
        'year' => '',
        'human_time' => '',
    ];


    public function getDayAttribute()
    {
        return $this->date->day;
    }

    public function getMonthAttribute()
    {
        return $this->date->month;
    }

    public function getYearAttribute()
    {
        return $this->date->year;
    }

    public function getHumanTimeAttribute()
    {
        if (!$this->time) {
            return '';
        }
        list($h, $m) = explode(':', $this->time);
        $time = ($h > 12 ? ($h - 12) : intval($h)) . ':' . $m . ($h > 11 ? 'pm' : 'am');
        return $time;
    }

    public function beforeSave()
    {
        unset($this->attributes['human_time']);
    }

    public function getDayOptions($month)
    {
        if ($this->month && $this->year) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
            $days = range(1, $daysInMonth);
            return array_combine($days, $days);
        }
        return [0 => 'Pick a Month AND Year'];
    }

    public function getMonthOptions()
    {
        $months = ['0', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        unset($months[0]);
        return $months;
    }

    public function getYearOptions()
    {
        $year = date('Y');
        $years = range($year, $year + 5);
        return array_combine($years, $years);
    }

    public function getUserIdOptions($keyValue = null)
    {
        $manager = PluginManager::instance();
        if ($manager->exists('rainlab.user')) {
            $Users = array();
            foreach (UserModel::orderBy('surname')->
                orderBy('name')->get() as $user) {
                $Users[$user->id] = $user->surname . ', ' . $user->name;
            }

            return $Users;
        }
        return [0 => 'Rainlab User Model Not Installed'];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Restricts to dates after $days days before today.
     * @param  object $query
     * @param  integer $days
     * @return object $query
     */
    public function scopePast($query, $days)
    {
        $date = new Carbon();
        $date->subDays($days);
        return $query->where('date', '>=', $date);
    }

    /**
     * Restricts to dates after $days days from today.
     * @param  object $query
     * @param  integer $days
     * @return object $query
     */
    public function scopeFuture($query, $days)
    {
        $date = new Carbon();
        $date->addDays($days);
        return $query->where('date', '<=', $date);
    }
}
