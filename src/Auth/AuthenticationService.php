<?php

namespace Drupal\oauth2server\Auth;

use Drupal\restapi\Auth\AbstractAuthenticationService;
use Drupal\oauth2server\Server\DrupalResourceServer;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerAwareTrait;
use Drupal\Log\WatchdogLogger;


/**
 * Provides an OAuth2 validation service.
 *
 * @see restapi_access_callback()
 */
class AuthenticationService extends AbstractAuthenticationService {

  use LoggerAwareTrait;

  /**
   * A resource server.
   *
   * @var DrupalResourceServer
   *
   */
  protected $server = NULL;


  /**
   * {@inheritdoc}
   *
   */
  public function __construct(\StdClass $user, Request $request) {
    parent::__construct($user, $request);

    // Ensure that we have a logger set. This can be overridden by
    // LoggerAwareTrait::setLogger().
    $this->logger = new WatchdogLogger();
  }


  /**
   * {@inheritdoc}
   *
   */
  public function isValid() {
    try {
      $this->getServer()->isValidRequest(FALSE);
    }
    catch (\Exception $e) {
      $this->logger->warning($e->getMessage());
      return FALSE;
    }
    return TRUE;
  }


  /**
   * Sets a resource server for us to utilize in our checks.
   *
   * @param DrupalResourceServer $server
   *   A resource server.
   *
   */
  public function setServer(ResourceServer $server) {
    $this->server = $server;
  }


  /**
   * Retrieves a resource server.
   *
   * @return DrupalResourceServer
   *
   */
  protected function getServer() {
    if (!$this->server) {
      $this->server = DrupalResourceServer::configure();
    }

    return $this->server;
  }

}