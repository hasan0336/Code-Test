<?php
namespace DTApi\Helpers;

use Carbon\Carbon;
use DTApi\Models\Job;
use DTApi\Models\User;
use DTApi\Models\Language;
use DTApi\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeHelper
{
    public static function fetchLanguageFromJobId($id)
    {
        $language = Language::findOrFail($id);
        return $language1 = $language->language;
    }

    public static function getUsermeta($user_id, $key = false)
    {
        return $user = UserMeta::where('user_id', $user_id)->first()->$key;
        if (!$key)
            return $user->usermeta()->get()->all();
        else {
            $meta = $user->usermeta()->where('key', '=', $key)->get()->first();
            if ($meta)
                return $meta->value;
            else return '';
        }
    }

    public static function convertJobIdsInObjs($jobs_ids)
    {

        $jobs = array();
        foreach ($jobs_ids as $job_obj) {
            $jobs[] = Job::findOrFail($job_obj->id);
        }
        return $jobs;
    }

    public static function willExpireAt($due_time, $created_at)
    {
        $due_time = Carbon::parse($due_time);
        $created_at = Carbon::parse($created_at);

        $difference = $due_time->diffInHours($created_at);


        if($difference <= 90)
            $time = $due_time;
        elseif ($difference <= 24) {
            $time = $created_at->addMinutes(90);
        } elseif ($difference > 24 && $difference <= 72) {
            $time = $created_at->addHours(16);
        } else {
            $time = $due_time->subHours(48);
        }

        return $time->format('Y-m-d H:i:s');

    }

    public function testWillExpireAt()
    {
        // Test with due time less than 90 hours from created_at
        $dueTime = Carbon::now()->addHours(80);
        $createdAt = Carbon::now();
        $result = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals($dueTime, $result);

        // Test with due time more than 24 but less than 72 hours from created_at
        $dueTime = Carbon::now()->addHours(50);
        $createdAt = Carbon::now();
        $expected = $createdAt->copy()->addHours(16);
        $result = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals($expected, $result);

        // Test with due time more than 72 hours from created_at
        $dueTime = Carbon::now()->addHours(100);
        $createdAt = Carbon::now();
        $expected = $dueTime->copy()->subHours(48);
        $result = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals($expected, $result);

        // Test edge case exactly at 72 hours
        $dueTime = Carbon::now()->addHours(72);
        $createdAt = Carbon::now();
        $expected = $createdAt->copy()->addHours(16);
        $result = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals($expected, $result);

        // Test edge case exactly at 24 hours
        $dueTime = Carbon::now()->addHours(24);
        $createdAt = Carbon::now();
        $expected = $createdAt->copy()->addHours(16);
        $result = TeHelper::willExpireAt($dueTime, $createdAt);
        $this->assertEquals($expected, $result);
    }


}

