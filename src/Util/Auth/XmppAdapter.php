<?php

namespace Auth;

use ACL;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Exception\Stream\AuthenticationErrorException;
use Fabiang\Xmpp\Options;
use Psr\Log\LoggerInterface;
use UserRegistered;
use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result;

class XmppAdapter extends AbstractAdapter
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * XmppAdapter constructor.
     *
     * @param $host
     * @param $port
     * @param LoggerInterface $logger
     */
    public function __construct($host, $port, LoggerInterface $logger, $connectionType = 'tcp')
    {
        $this->host = $host;
        $this->port = $port;
        $this->logger = $logger;
        $this->connectionType = $connectionType;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $address = "$this->connectionType://$this->host:$this->port";
        $options = new Options($address);
        $options->setLogger($this->logger)
            ->setUsername($this->getIdentity())
            ->setPassword($this->getCredential());
        $client = new Client($options);

        try {
            /*$userRegistered = UserRegistered::with([])->find($this->getIdentity());

            if (empty($userRegistered)) {
                throw new AuthenticationErrorException;
            }*/

            $client->connect();

        } catch (AuthenticationErrorException $e) {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                array(),
                array('Invalid username or password provided')
            );
        }

        $user = ['identity' => $this->getIdentity(), 'role' => ACL::$ROLE_MEMBER];

        return new Result(Result::SUCCESS, $user, array());
    }
}