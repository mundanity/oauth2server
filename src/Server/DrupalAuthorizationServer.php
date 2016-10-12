<?php

namespace Drupal\oauth2server\Server;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use Drupal\oauth2server\Repository\Session;
use Drupal\oauth2server\Repository\AccessToken;
use Drupal\oauth2server\Repository\Client;
use Drupal\oauth2server\Repository\Scope;


/**
 * Provides an OAuth2 authorization server.
 *
 */
class DrupalAuthorizationServer extends AuthorizationServer {

  /**
   * Creates an authorization server with the Password Grant flow, using Drupal
   * for the storage systems.
   *
   * This server should be used to verify clients in order to return access and
   * / or refresh tokens.
   *
   * @param callable $callback
   *   A callback for authenticating the user.
   * @param int $access_token_ttl
   *   The time (in seconds) that the token will be valid for (default 3600).
   *
   * @return DrupalAuthorizationServer
   *
   */
  public static function configureForPasswordGrant(callable $callback, $access_token_ttl = 3600) {

    $server = new static();
    $server->setSessionStorage(new Session());
    $server->setAccessTokenStorage(new AccessToken());
    $server->setClientStorage(new Client());
    $server->setScopeStorage(new Scope());

    $grant = new PasswordGrant();
    $grant->setVerifyCredentialsCallback($callback);

    $server->setAccessTokenTTL($access_token_ttl);
    $server->addGrantType($grant);

    /* If we want refresh tokens
    $refresh = new \Drupal\oauth2server\Storage\RefreshToken;
    $server->setRefreshTokenStorage($refresh);
    $grant2 = new \League\OAuth2\Server\Grant\RefreshTokenGrant();
    $server->addGrantType($grant2);
    */

    return $server;

  }



}