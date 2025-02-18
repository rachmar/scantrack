<?php

namespace App\Jobs;

use App\Models\Student;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendConsecutiveAbsentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $student;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Student $student)
    {
        $this->student = $student;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $msg = "Good Day! Mr/Ms. ".$this->student->first_name." ".$this->student->last_name." has been absent for three consecutive days. The student is required to submit a written excuse letter signed by a guardian, along with any supporting documents as evidence for the absence.";
    
        $client = new Client();
        
        $client->request('POST', 'https://semaphore.co/api/v4/messages',[
            'form_params' =>  [
                'apikey' => env('SEMAPHORE_API_KEY'),
                'number' => '09564814797',
                'message' => $msg,
                'sendername' => 'Brokenshire'
            ]
        ]);
    }
}
