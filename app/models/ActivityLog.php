<?php

namespace app\models;

class ActivityLog extends BaseModel
{
    protected $tableName = 'activity_logs';
    protected $fillable = ['user_id', 'appliedleave_id', 'action', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appliedleave()
    {
        return $this->belongsTo(AppliedLeave::class, 'appliedleave_id');
    }

    public static function record($userId, $appliedLeaveId, $action, $description)
    {
        return (new self())->create([
            'user_id' => $userId,
            'appliedleave_id' => $appliedLeaveId,
            'action' => $action,
            'description' => $description,
        ]);
    }

    public static function forUser($userId)
    {
        return self::model()->where(['user_id' => $userId])->orderBy('created_at', 'DESC')->get();
    }
}
