<?php

abstract class PhabricatorProjectBoardController
  extends PhabricatorProjectController {

  protected function getProfileMenu() {
    $menu = parent::getProfileMenu();

    $request = $this->getRequest();
    $menu->selectFilter(PhabricatorProject::ITEM_WORKBOARD, null, $request);
    $menu->addClass('project-board-nav');

    return $menu;
  }
}
