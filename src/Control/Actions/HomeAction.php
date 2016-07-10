<?php

use Slim\Flash\Messages;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Translation\Translator;

final class HomeAction
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
        return $this->view->render($response, 'home.twig', [
            'title'     => $this->translator->trans('home.title'),
            'content'   => $this->translator->trans('home.text', ['%server%' => getenv('site_xmpp_server_displayname')])
        ]);
    }
}