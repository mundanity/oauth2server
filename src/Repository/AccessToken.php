<?php

namespace Drupal\oauth2server\Repository;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AccessTokenInterface;


class AccessToken extends AbstractStorage implements AccessTokenInterface {

  /**
   * Get an instance of Entity\AccessTokenEntity
   *
   * @param string $token The access token
   *
   * @return \League\OAuth2\Server\Entity\AccessTokenEntity
   */
  public function get($token) {

    $params = [':token' => $token];
    $result = db_query("
      SELECT
        t.token, t.expire_time
      FROM
        {oauth2_access_tokens} t
      WHERE
        t.token = :token
    ", $params)->fetchObject();
    $token = NULL;

    if ($result) {
      $token = new AccessTokenEntity($this->getServer());
      $token->setId($result->token);
      $token->setExpireTime($result->expire_time);
    }

    return $token;

  }

  /**
   * Get the scopes for an access token
   *
   * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
   *
   * @return array Array of \League\OAuth2\Server\Entity\ScopeEntity
   */
  public function getScopes(AccessTokenEntity $token) {

    $params = [':token_id' => $token->getId()];
    $result = db_query("
      SELECT
        s.id, s.description
      FROM
        {oauth2_scopes} s
      INNER JOIN
        {oauth2_access_token_scopes} t ON t.scope_id = s.id
      WHERE
        t.access_token = :token_id
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
   * Creates a new access token
   *
   * @param string         $token      The access token
   * @param integer        $expireTime The expire time expressed as a unix timestamp
   * @param string|integer $sessionId  The session ID
   *
   * @return void
   */
  public function create($token, $expireTime, $sessionId) {

    $fields = [
      'token'       => $token,
      'session_id'  => $sessionId,
      'expire_time' => $expireTime,
    ];

    db_insert('oauth2_access_tokens')
      ->fields($fields)
      ->execute();

  }

  /**
   * Associate a scope with an acess token
   *
   * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
   * @param \League\OAuth2\Server\Entity\ScopeEntity       $scope The scope
   *
   * @return void
   */
  public function associateScope(AccessTokenEntity $token, ScopeEntity $scope) {

    $fields = [
      'access_token' => $token->getId(),
      'scope_id'     => $scope->getId(),
    ];

    db_insert('oauth2_access_token_scopes')
      ->fields($fields)
      ->execute();

  }

  /**
   * Delete an access token
   *
   * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token to delete
   *
   * @return void
   */
  public function delete(AccessTokenEntity $token) {

    db_delete('oauth2_access_token')
      ->condition('token', $token->getId())
      ->execute();

    db_delete('oauth2_access_token_scopes')
      ->condition('access_token', $token->getId())
      ->execute();

  }

}