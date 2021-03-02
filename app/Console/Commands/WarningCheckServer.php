<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WarningCheckServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warning:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warning when server get problem';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $serverInfo = (new DailyCheckServer())->checkServer();
        $ram = floatval($serverInfo['memory']);
        $cpu = floatval($serverInfo['cpu']);
        $disk = floatval($serverInfo['diskuse']);

        if ($ram > 75 || $cpu > 75 || $disk > 75){
            $message = "RAM: ".$serverInfo['usedmemInGB']."/".$serverInfo['totalram']."(".$serverInfo['memory'].")\nCPU: ".$serverInfo['cpu']."%\nDISK: ".round($serverInfo['disktotalsize'],2)." GB (".$serverInfo['diskuse'].")";
            $webhookurl = "https://discord.com/api/webhooks/814325321100427314/tnUdAmVixO8lGv2LCB_tF2H5EM8kvlDGsCjplP9FHMYk2ChrlrI-JNus_gr5MedzrM_t";
            $json_data = json_encode([
                "content" => $message,
                "username" => "Server notify",
                "tts" => false,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $ch = curl_init($webhookurl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            curl_close($ch);

            $this->info('Message is sent');
        } else {
            return;
        }

    }
}
