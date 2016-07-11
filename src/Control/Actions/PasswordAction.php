<?php

use Curl\Curl;
use JeremyKendall\Slim\Auth\Authenticator;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

final class PasswordAction
{
    private $view;
    private $translator;
    private $logger;
    private $flash;
    private $auth;

    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash, Translator $translator, Authenticator $auth)
    {
        $this->view = $view;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->flash = $flash;
        $this->auth = $auth;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();

        if ($request->isPost()) {

            // Form validation
            $validator = new ValidationHelper();
            $validator->validation_rules([
                'password'     => 'required|max_len,255|min_len,8',
                'password_confirmation' => 'required|max_len,255|min_len,8',
            ]);
            if (!$validator->run($body)) {
                $validator->addErrorsToFlashMessage($this->flash);
                return $response->withRedirect('password');
            }

            $password = $body['password'];
            $passwordConfirmation = $body['password_confirmation'];

            $this->logger->debug($password);
            $this->logger->debug($passwordConfirmation);

            if ($password != $passwordConfirmation) {
                $this->flash->addMessage('error', $this->translator->trans('password.flash.not_the_same'));
                return $response->withRedirect('password');
            }

            // update password
            $username = $this->auth->getIdentity()['identity'];

            $curl = new Curl();
            $curl->setBasicAuthentication(getenv('xmpp_curl_auth_admin_username'), getenv('xmpp_curl_auth_admin_password'));
            $curl->patch(getenv('xmpp_curl_uri') . '/user/' . $username . '/password', json_encode(['password' => $password]));
            $curl->close();

            if ($curl->http_status_code == 200) {
                $this->flash->addMessage('success', $this->translator->trans('password.flash.success', ['%username%' => $username]));
                $this->logger->info($this->translator->trans('log.password.success', ['%username%' => $username]));
                return $response->withRedirect('logout');
            } else {
                $this->flash->addMessage('error', $this->translator->trans('password.flash.unknown_error', ['%username%' => $username]));
                $this->logger->error($this->translator->trans('log.password.unknown_error'), ['username' => $username, 'code' => $curl->http_status_code, 'message' => $curl->http_error_message]);
                return $response->withRedirect('password');
            }
        }

        // render GET
        $this->view->render($response, 'password.twig', [
            'title'     => $this->translator->trans('password.title'),
        ]);

        return $response;
    }
}