<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronJobTask extends Model
{
    use HasFactory;

	protected $table = 'cron_jobs_taks';

	public const PROCESSING = 'processing';
	public const SUCCESS = 'success';
	public const ERROR = 'error';

}
