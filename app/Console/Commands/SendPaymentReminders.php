<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReminderMail;

class SendPaymentReminders extends Command
{
    protected $signature = 'email:send-payment-reminder 
                            {--send : Actually dispatch emails to all parents} 
                            {--preview= : Send a single test email to this address for preview}';

    protected $description = 'Stage, preview, or dispatch the monthly payment reminder email to parents.';

    public function handle()
    {
        // 1. Query unique parent emails from the database
        $parentEmails = DB::table('enrollment_applicants')
            ->whereNotNull('parent_email')
            ->where('parent_email', '<>', '')
            ->distinct()
            ->pluck('parent_email')
            ->toArray();

        $totalFamilies = count($parentEmails);

        $this->info("==================================================");
        $this->info("      AL MUNAWWARA MONTHLY PAYMENT REMINDER      ");
        $this->info("==================================================");
        $this->info("Found total: {$totalFamilies} unique parent email(s) in database.");

        // 2. Handle Preview Mode
        $previewAddress = $this->option('preview');
        if ($previewAddress) {
            $this->info("Sending test/preview email to: {$previewAddress}...");
            try {
                Mail::to($previewAddress)->send(new PaymentReminderMail());
                $this->info("SUCCESS: Test email successfully sent to {$previewAddress}!");
            } catch (\Exception $e) {
                $this->error("ERROR sending preview email: " . $e->getMessage());
            }
            return Command::SUCCESS;
        }

        // 3. Handle Actual Sending
        $actuallySend = $this->option('send');
        if ($actuallySend) {
            if ($totalFamilies === 0) {
                $this->warn("No parent emails found to send to.");
                return Command::FAILURE;
            }

            if (!$this->confirm("Are you absolutely sure you want to send this payment reminder to ALL {$totalFamilies} families?")) {
                $this->info("Operation cancelled.");
                return Command::SUCCESS;
            }

            $this->info("Starting bulk email dispatch...");
            $successCount = 0;
            $failCount = 0;

            foreach ($parentEmails as $email) {
                try {
                    Mail::to($email)->send(new PaymentReminderMail());
                    $this->info("Sent reminder to: {$email}");
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to send to {$email}: " . $e->getMessage());
                    $failCount++;
                }
            }

            $this->info("--------------------------------------------------");
            $this->info("Bulk Dispatch Completed!");
            $this->info("Success: {$successCount} | Failed: {$failCount}");
            $this->info("--------------------------------------------------");
            return Command::SUCCESS;
        }

        // 4. Default: Dry Run Mode
        $this->warn("\n*** CURRENTLY IN DRY-RUN / PREVIEW MODE ***");
        $this->info("No emails were dispatched to parents (wag muna send).");
        $this->info("To send a test copy to yourself/finance to see how it looks, run:");
        $this->comment("  php artisan email:send-payment-reminder --preview=your-email@example.com");
        $this->info("\nTo dispatch this reminder to all {$totalFamilies} families, run:");
        $this->comment("  php artisan email:send-payment-reminder --send");
        $this->info("==================================================");

        return Command::SUCCESS;
    }
}
