<?php

namespace Drupal\oauth2server\Repository;

use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AuthCodeInterface;


/**
 * Auth code storage interface
 */
class AuthCode extends AbstractStorage implements AuthCodeInterface {

  /**
   * Get the auth code
   *
   * @param string $code
   *
   * @return \League\OAuth2\Server\Entity\AuthCodeEntity
   */
  public function get($code) {

    $params = [
      ':auth_code' => $code,
      ':time'      => time(),
    ];
    $result = db_query("
      SELECT
        a.auth_code, a.expire_time, a.client_redirect_uri
      FROM
        {oauth2_auth_codes} a
      WHERE
        a.auth_code = :auth_code
      AND
        a.expire_time >= :time
    ", $params)->fetchObject();
    $token = NULL;

    if ($result) {
      $token = new AuthCodeEntity($this->getServer());
      $token->setId($result->auth_code);
      $token->setExpireTime($result->expire_time);
      $token->setRedirectUri($result->client_redirect_uri);
    }

    return $token;

  }

  /**
   * Create an auth code.
   *
   * @param string  $token       The token ID
   * @param integer $expireTime  Token expire time
   * @param integer $sessionId   Session identifier
   * @param string  $redirectUri Client redirect uri
   *
   * @return void
   */
  public function create($token, $expireTime, $sessionId, $redirectUri) {

    $fields = [
      'auth_code'   => $token,
      'expire_time' => $expireTime,
      'session_id'  => $sessionId,
      'client_redirect_uri' => $redirectUri,
    ];

    db_insert('oauth2_auth_codes')
      ->fields($fields)
      ->execute();

  }

  /**
   * Get the scopes for an access token
   *
   * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
   *
   * @return array Array of \League\OAuth2\Server\Entity\ScopeEntity
   */
  public function getScopes(AuthCodeEntity $token) {

    $params = [':auth_code' => $token->getId()];
    $result = db_query("
      SELECT
        s.id, s.description
      FROM
        {oauth2_scopes} s
      INNER JOIN
        {oauth2_auth_code_scopes} a ON a.scope_id = s.id
      WHERE
        a.auth_code = :auth_code
    ", $params);
    $scopes = [];

    foreach($result as $record) {
      $scopes[] = (new ScopeEntity($this->getServer()))->hydrate([
        'id'          => $record->id,
        'description' => $record->description,
      ]);
    }

    return $scopes;

  }

  /**
   * Associate a scope with an acess token
   *
   * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
   * @param \League\OAuth2\Server\Entity\ScopeEntity    $scope The scope
   *
   * @return void
   */
  public function associateScope(AuthCodeEntity $token, ScopeEntity $scope) {

    $fields = [
      'auth_code' => $token->getId(),
      'scope_id'  => $scope->getId(),
    ];

    db_insert('oauth2_auth_code_scopes')
      ->fields($fields)
      ->execute();

  }

  /**
   * Delete an access token
   *
   * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The access token to delete
   *
   * @return void
   */
  public function delete(AuthCodeEntity $token) {

    db_delete('oauth2_auth_codes')
      ->condition('auth_code', $token->getId())
      ->execute();

    db_delete('oauth2_auth_code_scopes')
      ->condition('auth_code', $token->getId())
      ->execute();

  }

}