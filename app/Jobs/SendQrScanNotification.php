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

class SendQrScanNotification implements ShouldQueue
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
        $msg = "Good Day! ".$this->student->first_name." ".$this->student->last_name." has entered our school premises at ".date("l jS \of F Y h:i A").". You will receive a notification once the student leaves the school premises. Please do not reply.";
    
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
