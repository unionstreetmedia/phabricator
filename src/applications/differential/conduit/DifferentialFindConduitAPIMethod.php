<?php

final class DifferentialFindConduitAPIMethod
  extends DifferentialConduitAPIMethod {

  public function getAPIMethodName() {
    return 'differential.find';
  }

  public function getMethodStatus() {
    return self::METHOD_STATUS_DEPRECATED;
  }

  public function getMethodStatusDescription() {
    return pht("Replaced by '%s'.", 'differential.query');
  }

  public function getMethodDescription() {
    return pht('Query Differential revisions which match certain criteria.');
  }

  protected function defineParamTypes() {
    $types = array(
      'open',
      'committable',
      'revision-ids',
      'phids',
    );

    return array(
      'query' => 'required '.$this->formatStringConstants($types),
      'guids' => 'required nonempty list<guids>',
    );
  }

  protected function defineReturnType() {
    return 'nonempty list<dict>';
  }

  protected function execute(ConduitAPIRequest $request) {
    $type = $request->getValue('query');
    $guids = $request->getValue('guids');

    $results = array();
    if (!$guids) {
      return $results;
    }

    $query = id(new DifferentialRevisionQuery())
      ->setViewer($request->getUser());

    switch ($type) {
      case 'open':
        $query
          ->withIsOpen(true)
          ->withAuthors($guids);
        break;
      case 'committable':
        $query
          ->withStatuses(DifferentialRevisionStatus::ACCEPTED)
          ->withAuthors($guids);
        break;
      case 'revision-ids':
        $query
          ->withIDs($guids);
        break;
      case 'owned':
        $query->withAuthors($guids);
        break;
      case 'phids':
        $query
          ->withPHIDs($guids);
        break;
    }

    $revisions = $query->execute();

    foreach ($revisions as $revision) {
      $diff = $revision->loadActiveDiff();
      if (!$diff) {
        continue;
      }
      $id = $revision->getID();
      $results[] = array(
        'id'          => $id,
        'phid'        => $revision->getPHID(),
        'name'        => $revision->getTitle(),
        'uri'         => PhabricatorEnv::getProductionURI('/D'.$id),
        'dateCreated' => $revision->getDateCreated(),
        'authorPHID'  => $revision->getAuthorPHID(),
        'statusName'  => $revision->getStatusDisplayName(),
        'sourcePath'  => $diff->getSourcePath(),
      );
    }

    return $results;
  }

}
