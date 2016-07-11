<?php

use JeremyKendall\Slim\Auth\Authenticator;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Translation\Translator;

final class LogoutAction
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
        $this->flash->addMessage('success', $this->translator->trans('logout.flash.success'));
        $this->logger->info($this->translator->trans('log.logout', ['%username%' => $this->auth->getIdentity()['identity']]));

        $this->auth->logout();
        return $response->withRedirect('login');
    }
}