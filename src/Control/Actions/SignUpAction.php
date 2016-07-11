<?php

use Curl\Curl;
use Slim\Flash\Messages;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Translation\Translator;

final class SignUpAction
{
    private $view;
    private $translator;
    private $logger;
    private $flash;
    private $router;

    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash, Translator $translator, RouterInterface $router)
    {
        $this->view = $view;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->flash = $flash;
        $this->router = $router;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();

        if ($request->isPost()) {

            // Form validation
            $validator = new Validator();
            $validator->filter_rules([
                'username'     => 'trim|sanitize_string',
                'email'        => 'trim|sanitize_email',
            ]);
            $validator->validation_rules([
                'username'     => 'required|alpha_numeric|max_len,64|min_len,3',
                'email'        => 'required|valid_email|max_len,64|min_len,5',
                'password'     => 'required|max_len,255|min_len,8',
            ]);
            if (!$validator->run($body)) {
                $validator->addErrorsToFlashMessage($this->flash);
                return $response->withRedirect('signup');
            }

            $username = $body['username'];
            $email = $body['email'];
            $password = $body['password'];

            // waiting queue
            if ((UserAwaitingVerification::with([])->where('email', $email)->get()->count() > 0)) {
                $this->flash->addMessage('error', $this->translator->trans('sign.up.flash.already_in_use_email', ['%email%' => $email]));
                return $response->withRedirect('signup');
            }
            if ((UserAwaitingVerification::with([])->where('username', $username)->get()->count() > 0)) {
                $this->flash->addMessage('error', $this->translator->trans('sign.up.flash.already_in_use_username', ['%username%' => $username]));
                return $response->withRedirect('signup');
            }

            // xmpp accounts
            $curl = new Curl();
            $curl->setBasicAuthentication(getenv('xmpp_curl_auth_admin_username'), getenv('xmpp_curl_auth_admin_password'));
            $curl->get(getenv('xmpp_curl_uri') . '/user/' . $username);
            $curl->close();

            if ($curl->http_status_code != 404) {
                $this->flash->addMessage('error', $this->translator->trans('sign.up.flash.already_in_use_email_and_username', ['%email%' => $email, '%username%' => $username]));
                return $response->withRedirect('signup');
            }

            $userAwaiting = new UserAwaitingVerification();
            $userAwaiting->username = $username;
            $userAwaiting->email = $email;
            $userAwaiting->password = $password;

            $generatedCode = NULL;
            $found = false;

            while (!$found) {
                $generatedCode = hash('crc32', time() . $email . rand());
                if (UserAwaitingVerification::with([])->where('verification_code', '=', $generatedCode)->get()->count() === 0) $found = true;
            }

            $userAwaiting->verification_code = $generatedCode;
            $userAwaiting->save();

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

            $verificationLink = $request->getUri()->getScheme();
            $verificationLink .= '://';
            $verificationLink .= $request->getUri()->getHost();
            $verificationLink .= (!empty($p = $request->getUri()->getPort()) ? ':' .$p : '');
            $verificationLink .= $this->router->pathFor('verification', ['verificationCode' => $userAwaiting->verification_code]);

            $mailer->Subject = $this->translator->trans('verification.mail.subject', ['%server%' => getenv('site_xmpp_server_displayname')]);
            $mailer->Body = $this->translator->trans('verification.mail.body', ['%username%' => $userAwaiting->username, '%verificationLink%' => $verificationLink, '%server%' => getenv('site_xmpp_server_displayname')]);
            $mailer->send();

            $this->flash->addMessage('success', $this->translator->trans('sign.up.flash.success'));
            $this->logger->info($this->translator->trans('log.signed.up', ['%username%' => $userAwaiting->username]));
            return $response->withRedirect('signup');
        }

        // render GET
        $this->view->render($response, 'signup.twig', [
            'title'     => $this->translator->trans('sign.up.title'),
        ]);

        return $response;
    }
}