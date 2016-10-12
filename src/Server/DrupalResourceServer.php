<?php

namespace Drupal\oauth2server\Server;

use League\OAuth2\Server\ResourceServer;
use Drupal\oauth2server\Repository\Session;
use Drupal\oauth2server\Repository\AccessToken;
use Drupal\oauth2server\Repository\Client;
use Drupal\oauth2server\Repository\Scope;


/**
 * Provides an OAuth2 resource server.
 *
 */
class DrupalResourceServer extends ResourceServer {

  /**
   * Creates a resource server, using Drupal for the storage systems.
   *
   * A resource server can verify the presented access tokens, and sits in front
   * of the actual resource being accessed.
   *
   * @return DrupalResourceServer
   *
   */
  public static function configure() {

    $session = new Session();
    $access  = new AccessToken();
    $client  = new Client();
    $scope   = new Scope();
    $server  = new static($session, $access, $client, $scope);

    return $server;

  }

}