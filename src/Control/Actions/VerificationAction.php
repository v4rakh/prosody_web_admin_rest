<?php

use Curl\Curl;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

final class VerificationAction
{
    private $view;
    private $translator;
    private $logger;
    private $flash;

    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash, Translator $translator)
    {
        $this->view = $view;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->flash = $flash;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $verificationCode = $args['verificationCode'];

        $usersAwaiting = UserAwaitingVerification::with([])->where('verification_code', $verificationCode)->get();

        if (empty($usersAwaiting) || $usersAwaiting->count() == 0) {
            $this->flash->addMessage('error', $this->translator->trans('verification.code.invalid', ['%verificationCode%' => $verificationCode]));
            $this->logger->info($this->translator->trans('log.verification.invalid', ['%verificationCode%' => $verificationCode]));
            return $response->withRedirect('/signup');
        }

        $userAwaiting = $usersAwaiting->pop();

        $curl = new Curl();
        $curl->setBasicAuthentication(getenv('xmpp_curl_auth_admin_username'), getenv('xmpp_curl_auth_admin_password'));
        $curl->setHeader('Content-Type', 'application/json');
        $curl->post(getenv('xmpp_curl_uri') . '/user/' . $userAwaiting->username, json_encode(['password' => $userAwaiting->password]));
        $curl->close();

        if ($curl->http_status_code == 409) {
            $this->flash->addMessage('error', $this->translator->trans('verification.flash.already_in_use_username', ['%username%' => $userAwaiting->username]));

            $userAwaiting->delete();
            return $response->withRedirect('signup');
        } else if ($curl->http_status_code == 201) {
            $this->flash->addMessage('success', $this->translator->trans('verification.flash.success', ['%username%' => $userAwaiting->username]));
            $this->logger->info($this->translator->trans('log.verification.sucess', ['%username%' => $userAwaiting->username]));

            if (getenv('mail_notify') == true) {
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
                $mailer->addAddress(getenv('mail_notify_to'));
                $mailer->Subject = $this->translator->trans('verification.mail.success.notify.subject', ['%server%' => getenv('site_xmpp_server_displayname')]);
                $mailer->Body = $this->translator->trans('verification.mail.success.notify.body', ['%username%' => $userAwaiting->username, '%server%' => getenv('site_xmpp_server_displayname'), '%email%' => $userAwaiting->email]);
                $mailer->send();
            }

            $userRegistered = new UserRegistered();
            $userRegistered->username = $userAwaiting->username;
            $userRegistered->generateDeleteCode();
            $userRegistered->save();

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
            $mailer->addAddress($userAwaiting->email);
            $mailer->Subject = $this->translator->trans('verification.mail.success.subject', ['%server%' => getenv('site_xmpp_server_displayname')]);
            $mailer->Body = $this->translator->trans('verification.mail.success.body', ['%username%' => $userAwaiting->username, '%server%' => getenv('site_xmpp_server_displayname'), '%password%' => $userAwaiting->password, '%deleteCode%' => $userRegistered->delete_code]);
            $mailer->send();

            $userAwaiting->delete();
            return $response->withRedirect('/');
        } else {
            $this->flash->addMessage('error', $this->translator->trans('verification.flash.unknown_error', ['%username%' => $userAwaiting->username]));
            $this->logger->warning($this->translator->trans('log.verification.unknown_error'), ['username' => $userAwaiting->username, 'code' => $curl->http_status_code, 'message' => $curl->http_error_message]);
            return $response->withRedirect('/');
        }
    }
}