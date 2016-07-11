<?php

use Carbon\Carbon;

require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';

/*
 * Bootstrap environment, configs and database
 */
$env = EnvironmentHelper::getAppEnvironment();
$config = Config::$CONFIG;
$db = DatabaseHelper::getAppDatabase();
$translator = TranslationHelper::getAppTranslator();
$logger = LoggerHelper::getAppLogger();

// handle all users awaiting verification and notify them
$users = UserAwaitingVerification::all();
$now = Carbon::now();
$now->modify('+' . getenv('verification_cleanup_time'));

foreach ($users as $user) {
    $createdAt = DateHelper::convertToCarbon($user->created_at);

    if (!empty($createdAt)) {
        if ($createdAt->lt($now)) {
            $mailer = new PHPMailer();
            $mailer->CharSet = 'UTF-8';
            $mailer->ContentType = 'text/plain';
            $mailer->isSMTP();
            $mailer->SMTPSecure = getenv('mail_secure');
            $mailer->SMTPAuth = getenv('mail_auth');

            $mailer->Host = getenv('mail_host');
            $mailer->Port = getenv('mail_port');
            $mailer->Username = getenv('mail_username');
            $mailer->Password = getenv('mail_password');
            $mailer->From = getenv('mail_from');
            $mailer->FromName = getenv('mail_from_name');

            $mailer->addAddress($user->email);

            $mailer->Subject = $translator->trans('cleanup.mail.subject', ['%server%' => getenv('site_xmpp_server_displayname')]);
            $mailer->Body = $translator->trans('cleanup.mail.body', ['%username%' => $user->username, '%server%' => getenv('site_xmpp_server_displayname')]);
            $mailer->send();

            $logger->info($translator->trans('log.verification.cleanup', ['%username%' => $user->username]));
            $user->delete();
        }
    }
}