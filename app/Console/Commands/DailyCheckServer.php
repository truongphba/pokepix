<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DailyCheckServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily server info to discord';

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
        $serverInfo = $this->checkServer();
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

    }

    public function checkServer(){
        $free = shell_exec('free');
        $free = (string) trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $usedmem = $mem[2];
        $usedmemInGB = number_format($usedmem / 1048576, 2) . ' GB';
        $memory1 = $mem[2] / $mem[1] * 100;
        $memory = round($memory1) . '%';
        $fh = fopen('/proc/meminfo', 'r');
        $mem = 0;
        while ($line = fgets($fh)) {
            $pieces = array();
            if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
                $mem = $pieces[1];
                break;
            }
        }
        fclose($fh);
        $totalram = number_format($mem / 1048576, 2) . ' GB';
        $disktotal = disk_total_space('/'); //DISK usage
        $disktotalsize = $disktotal / 1073741824;
        $diskfree  = disk_free_space('/');
        $used = $disktotal - $diskfree;
        $diskusedize = $used / 1073741824;
        $diskuse1   = round(100 - (($diskusedize / $disktotalsize) * 100));
        $diskuse = round(100 - ($diskuse1)) . '%';
        $cpu = shell_exec('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1}\'');

        return [
            'usedmemInGB' => $usedmemInGB,
            'totalram' => $totalram,
            'memory' => $memory,
            'cpu' => $cpu,
            'disktotalsize' => $disktotalsize,
            'diskuse' => $diskuse
        ];
    }
}
