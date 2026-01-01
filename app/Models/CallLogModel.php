<?php

namespace App\Models;

use CodeIgniter\Model;

class CallLogModel extends Model
{
    protected $table = 'call_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'call_sid',
        'phone_number',
        'lead_name',
        'status',
        'duration',
        'conversation_summary',
        'lead_interested',
        'lead_email',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
