<?php

use Slim\Flash\Messages;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Translation\Translator;

final class IndexAction
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
        return $response->withRedirect('/signup');
    }
}