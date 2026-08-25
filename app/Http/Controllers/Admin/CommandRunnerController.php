<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandRunnerController extends Controller
{
    /**
     * Display hidden Artisan Command Runner interface
     */
    public function index()
    {
        $presets = [
            'view:clear'            => 'Clear Compiled Views (php artisan view:clear)',
            'route:clear'           => 'Clear Route Cache (php artisan route:clear)',
            'cache:clear'           => 'Clear Application Cache (php artisan cache:clear)',
            'config:clear'          => 'Clear Configuration Cache (php artisan config:clear)',
            'optimize:clear'        => 'Clear All Caches (php artisan optimize:clear)',
            'migrate'               => 'Run Database Migrations (php artisan migrate)',
            'migrate:status'        => 'Check Migration Status (php artisan migrate:status)',
            'storage:link'          => 'Create Storage Symlink (php artisan storage:link)',
            'db:seed --class=SupportDepartmentSeeder' => 'Seed Support Departments',
            'queue:restart'         => 'Restart Queue Worker (php artisan queue:restart)',
            'schedule:run'          => 'Run Scheduled Tasks (php artisan schedule:run)',
        ];

        return view('admin.command_runner', compact('presets'));
    }

    /**
     * Run selected or typed Artisan command
     */
    public function run(Request $request)
    {
        $command = trim($request->input('command'));

        if (empty($command)) {
            return back()->with('error', 'অনুগ্রহ করে একটি কমাণ্ড নির্বাচন করুন অথবা কাস্টম কমাণ্ড লিখুন।');
        }

        // Sanitize: remove leading 'php artisan' if user typed it
        $cleanCommand = preg_replace('/^php\s+artisan\s+/', '', $command);

        // Security check: block dangerous system shell injections
        if (str_contains($cleanCommand, ';') || str_contains($cleanCommand, '&&') || str_contains($cleanCommand, '||') || str_contains($cleanCommand, '|')) {
            return back()->with('error', 'নিরাপত্তাজনিত কারণে মাল্টিপল কমাণ্ড বা পাইপ (;) গ্রহণযোগ্য নয়।');
        }

        $outputBuffer = new BufferedOutput();

        try {
            $startTime = microtime(true);
            $exitCode = Artisan::call($cleanCommand, [], $outputBuffer);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $output = $outputBuffer->fetch();
            if (empty($output)) {
                $output = "Command executed successfully with exit code: {$exitCode}";
            }

            return back()->with([
                'command_executed' => 'php artisan ' . $cleanCommand,
                'command_output'   => $output,
                'exit_code'        => $exitCode,
                'execution_time'   => $executionTime,
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'command_executed' => 'php artisan ' . $cleanCommand,
                'command_output'   => "Error executing command:\n" . $e->getMessage(),
                'exit_code'        => 1,
            ]);
        }
    }
}
