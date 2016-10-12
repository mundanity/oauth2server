<?php

namespace Drupal\oauth2server\Repository;

use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ScopeInterface;


class Scope extends AbstractStorage implements ScopeInterface {

  /**
   * Return information about a scope
   *
   * @param string $scope     The scope
   * @param string $grantType The grant type used in the request (default = "null")
   * @param string $clientId  The client sending the request (default = "null")
   *
   * @return \League\OAuth2\Server\Entity\ScopeEntity
   */
  public function get($scope, $grantType = null, $clientId = null) {

    $params = [':id' => $scope];
    $result = db_query("
      SELECT
        s.id, s.description
      FROM
        {oauth2_scopes} s
      WHERE
        s.id = :id
    ", $params)->fetchObject();
    $scope = NULL;

    if ($result) {
      $scope = (new ScopeEntity($this->getServer()))->hydrate([
        'id' => $result->id,
        'description' => $result->description,
      ]);
    }

    return $scope;

  }

}