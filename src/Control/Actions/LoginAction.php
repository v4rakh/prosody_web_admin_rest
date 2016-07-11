<?php

use JeremyKendall\Slim\Auth\Authenticator;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Translation\Translator;

final class LoginAction
{
    private $view;
    private $logger;
    private $flash;
    private $translator;
    private $auth;

    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash, Translator $translator, Authenticator $auth)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;
        $this->translator = $translator;
        $this->auth = $auth;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();

        if ($request->isPost()) {

            // Form validation
            $validator = new ValidationHelper();
            $validator->filter_rules([
                'username'     => 'trim|sanitize_string',
            ]);
            $validator->validation_rules([
                'username'     => 'required|alpha_numeric|max_len,64|min_len,3',
                'password'     => 'required|max_len,255|min_len,8',
            ]);
            if (!$validator->run($body)) {
                $validator->addErrorsToFlashMessage($this->flash);
                return $response->withRedirect('/login');
            }

            $username = $body['username'];
            $password = $body['password'];

            $result = $this->auth->authenticate($username, $password);

            if ($result->isValid()) {

                if (empty(UserRegistered::with([])->find($username))) {
                    $userRegistered = new UserRegistered();
                    $userRegistered->username = $username;
                    $userRegistered->generateDeleteCode();
                    $userRegistered->save();
                }

                $this->flash->addMessage('success', $this->translator->trans('login.flash.success', ['%username%' => $username, '%server%' => getenv('site_xmpp_server_displayname')]));
                $this->logger->info($this->translator->trans('log.login', ['%username%' => $username]));
                return $response->withRedirect('/');
            }

            $this->flash->addMessage('error', $this->translator->trans('login.flash.wrong_credentials'));
            return $response->withRedirect('login');
        }

        // render GET
        $this->view->render($response, 'login.twig', [
            'title'     => $this->translator->trans('login.title'),
        ]);
    }
}