<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserMailConfiguration;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class UserMailService
{
    /**
     * Resolve the Mailer instance configured for the specified or logged-in user.
     * Falls back to default system mailer if user has no active personal configuration.
     */
    public static function getMailerForUser(?User $user = null): Mailer
    {
        $user = $user ?: auth()->user();

        if (!$user) {
            return Mail::mailer();
        }

        $config = $user->relationLoaded('mailConfiguration')
            ? $user->mailConfiguration
            : $user->mailConfiguration()->first();

        if (!$config || !$config->is_configured) {
            return Mail::mailer();
        }

        $mailerName = self::registerDynamicMailer($user, $config);

        return Mail::mailer($mailerName);
    }

    /**
     * Get the 'from' address and name for the user.
     */
    public static function getFromDetailsForUser(?User $user = null): array
    {
        $user = $user ?: auth()->user();

        $defaultAddress = config('mail.from.address', 'noreply@bhadrechatransport.com');
        $defaultName = config('mail.from.name', config('app.name', 'Bhadrecha Transport'));

        if (!$user) {
            return ['address' => $defaultAddress, 'name' => $defaultName];
        }

        $config = $user->relationLoaded('mailConfiguration')
            ? $user->mailConfiguration
            : $user->mailConfiguration()->first();

        if ($config && $config->is_configured) {
            return [
                'address' => $config->mail_from_address ?: ($config->mail_username ?: $user->email),
                'name' => $config->mail_from_name ?: ($user->full_name ?: $defaultName),
            ];
        }

        return [
            'address' => $user->email ?: $defaultAddress,
            'name' => $user->full_name ?: $defaultName,
        ];
    }

    /**
     * Send an email on behalf of the user using their individual mailer.
     */
    public static function send($to, $mailable, ?User $user = null)
    {
        $user = $user ?: auth()->user();
        $mailer = self::getMailerForUser($user);
        $from = self::getFromDetailsForUser($user);

        if ($mailable instanceof Mailable) {
            // Apply from address if not explicitly set on mailable
            if (empty($mailable->from)) {
                $mailable->from($from['address'], $from['name']);
            }
            return $mailer->to($to)->send($mailable);
        }

        // If closure or raw message callback
        return $mailer->to($to)->send($mailable);
    }

    /**
     * Register a dynamic SMTP mailer configuration for the given user.
     */
    public static function registerDynamicMailer(User $user, UserMailConfiguration $config, ?string $overridePassword = null): string
    {
        $mailerKey = "user_smtp_{$user->id}";
        $password = $overridePassword ?: $config->decrypted_password;

        $encryption = $config->mail_encryption;
        if (in_array(strtolower((string)$encryption), ['none', 'null', ''], true)) {
            $encryption = null;
        }

        Config::set("mail.mailers.{$mailerKey}", [
            'transport' => 'smtp',
            'host' => $config->mail_host,
            'port' => (int) ($config->mail_port ?: 587),
            'encryption' => $encryption,
            'username' => $config->mail_username,
            'password' => $password,
            'timeout' => 15,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ]);

        return $mailerKey;
    }

    /**
     * Test the user's mail connection by attempting to deliver a test email.
     */
    public static function testConnection(UserMailConfiguration $config, string $testRecipient, ?string $rawPassword = null): array
    {
        $user = $config->user ?: User::find($config->user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $passwordToUse = $rawPassword ?: $config->decrypted_password;
        if (empty($passwordToUse)) {
            return ['success' => false, 'message' => 'SMTP password is missing or could not be decrypted.'];
        }

        try {
            $mailerKey = self::registerDynamicMailer($user, $config, $passwordToUse);
            $mailer = Mail::mailer($mailerKey);

            $fromAddress = $config->mail_from_address ?: $config->mail_username;
            $fromName = $config->mail_from_name ?: ($user->full_name ?: 'Transport ERP');

            $subject = 'Test Email Connection - Transport ERP (' . date('d M Y H:i:s') . ')';
            $body = "Hello {$user->full_name},\n\nThis is a confirmation test email sent from Transport ERP using your personalized user email configuration ({$config->mail_username} on {$config->mail_host}:{$config->mail_port}).\n\nYour user-wise email sending is verified and active!";

            $mailer->raw($body, function ($msg) use ($testRecipient, $fromAddress, $fromName, $subject) {
                $msg->to($testRecipient)
                    ->from($fromAddress, $fromName)
                    ->subject($subject);
            });

            $config->last_tested_at = now();
            $config->last_test_status = 'success';
            $config->last_test_message = 'Connection test successful. Test email dispatched to ' . $testRecipient . '.';
            $config->save();

            return [
                'success' => true,
                'message' => 'Email configuration tested successfully! Test mail sent to ' . $testRecipient . '.',
            ];
        } catch (TransportExceptionInterface $e) {
            $errorMsg = 'SMTP Connection Error: ' . $e->getMessage();
            if (str_contains(strtolower($errorMsg), 'badcredentials') || str_contains(strtolower($errorMsg), 'username and password not accepted') || str_contains(strtolower($errorMsg), '535')) {
                $errorMsg .= ' Hint: For Gmail accounts with 2-Step Verification enabled, use a 16-character Google App Password instead of your regular Google password.';
            }

            $config->last_tested_at = now();
            $config->last_test_status = 'failed';
            $config->last_test_message = $errorMsg;
            $config->save();

            Log::error('User mail configuration test failed', [
                'user_id' => $user->id,
                'error' => $errorMsg,
            ]);

            return ['success' => false, 'message' => $errorMsg];
        } catch (Throwable $e) {
            $errorMsg = 'Email Error: ' . $e->getMessage();
            $config->last_tested_at = now();
            $config->last_test_status = 'failed';
            $config->last_test_message = $errorMsg;
            $config->save();

            Log::error('User mail configuration test failed with exception', [
                'user_id' => $user->id,
                'error' => $errorMsg,
            ]);

            return ['success' => false, 'message' => $errorMsg];
        }
    }
}
