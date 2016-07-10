<?php

use Curl\Curl;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

final class DeleteAction
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
        $body = $request->getParsedBody();

        if ($request->isPost()) {

            // Form validation
            $validator = new Validator();
            $validator->filter_rules([
                'username'     => 'trim|sanitize_string',
                'delete_code'  => 'trim|sanitize_string',
            ]);
            $validator->validation_rules([
                'username'     => 'required|alpha_numeric|max_len,64|min_len,3',
                'delete_code'  => 'required|exact_len,64',
            ]);
            if (!$validator->run($body)) {
                $validator->addErrorsToFlashMessage($this->flash);
                return $response->withRedirect('/delete');
            }

            $username = $body['username'];
            $deleteCode = $body['delete_code'];

            // check if combination matches
            $usersRegistered = UserRegistered::with([])->where('username', $username)->where('delete_code', $deleteCode)->get();

            if (empty($usersRegistered) || $usersRegistered->count() == 0) {
                $this->flash->addMessage('error', $this->translator->trans('delete.flash.combination_not_found'));
                return $response->withRedirect('/delete');
            } else {
                $userRegistered = $usersRegistered->pop();

                $curl = new Curl();
                $curl->setBasicAuthentication(getenv('xmpp_curl_auth_admin_username'), getenv('xmpp_curl_auth_admin_password'));
                $curl->delete(getenv('xmpp_curl_uri') . '/user/' . $username);
                $curl->close();

                if ($curl->http_status_code == 200 || $curl->http_status_code == 404) {
                    $userRegistered->delete();

                    $this->flash->addMessage('success', $this->translator->trans('delete.flash.success', ['%username%' => $username, '%server%' => getenv('site_xmpp_server_displayname')]));
                    $this->logger->info($this->translator->trans('log.delete.success', ['%username%' => $username]));
                    return $response->withRedirect('/');
                } else {
                    $this->flash->addMessage('error', $this->translator->trans('delete.flash.unknown_error', ['%username%' => $username]));
                    $this->logger->error($this->translator->trans('log.delete.flash.unknown_error'), ['username' => $username, 'code' => $curl->http_status_code, 'message' => $curl->http_error_message]);
                    return $response->withRedirect('/delete');
                }
            }
        }

        // render GET
        $this->view->render($response, 'delete.twig', [
            'title'     => $this->translator->trans('delete.title'),
        ]);

        return $response;
    }
}