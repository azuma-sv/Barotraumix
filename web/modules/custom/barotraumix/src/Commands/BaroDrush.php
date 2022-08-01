<?php

/**
 * @file
 * Drush commands for Barotraumix.
 */

namespace Drupal\barotraumix\Commands;

use Drupal\barotraumix\Core;
use Drupal\barotraumix\SteamCMD;
use Drush\Commands\DrushCommands;

/**
 * Class BaroDrush.
 */
class BaroDrush extends DrushCommands {

  /**
   * @var Core $core - Barotraumix core service.
   */
  protected Core $core;

  /**
   * @var SteamCMD $steamCMD - Barotraumix SteamCMD service.
   */
  protected SteamCMD $steamCMD;

  /**
   * Constructs an object.
   *
   * @param Core $core
   *  Barotraumix core service.
   * @param SteamCMD $steamCMD
   *  SteamCMD service.
   */
  public function __construct(Core $core, SteamCMD $steamCMD) {
    parent::__construct();
    $this->core = $core;
    $this->steamCMD = $steamCMD;
  }

  /**
   * Drush command that displays current Build ID of steam app.
   *
   * @param int $appId
   *  ID of Steam application to check.
   *
   * @command barotraumix:buildid
   * @aliases baro-bid
   * @usage barotraumix:buildid 1026340
   */
  public function buildId(int $appId = Core::BAROTRAUMA_APP_ID) {
    $buildId = $this->steamCMD->buildId($appId);
    $msg = $buildId ?? '[ERROR] Unable to get Build ID. Please check server logs.';
    $this->output()->writeln($msg);
  }

  /**
   * Drush command that installs specific steam app.
   *
   * This command will trigger automatically to install updates for Barotrauma.
   * But it will not be triggered automatically to install mods.
   *
   * @param int $appId
   *  ID of Steam application.
   *
   * @command barotraumix:install
   * @aliases baro-install
   * @usage barotraumix:install 1026340
   */
  public function install(int $appId = Core::BAROTRAUMA_APP_ID) {
    // Attempt to install app.
    $buildId = $this->steamCMD->appInstall($appId);

    // In case of success - create application on the website.
    if ($buildId) {
      $this->core->createApp();
    }

    // Validate results.
    if (isset($buildId)) {
      if ($buildId) {
        $msg = '[SUCCESS] Application has been installed.';
      }
      else {
        $msg = '[WARNING] Failed to install application, becaise it\'s already installed.';
      }
    }
    else {
      $msg = '[ERROR] Failed to install application, please check logs.';
    }

    // Debugging.
    if (empty($buildId)) {
      $this->output()->writeln($this->steamCMD->lastCommand());
      $this->output()->writeln($this->steamCMD->lastCommandOutput());
    }

    // Print status.
    $this->output()->writeln($msg);
  }

  /**
   * Drush command that verifies files of specific steam app.
   *
   * This command is created to trigger it manually.
   *
   * @todo: Refactor to work with node id.
   *
   * @param int $nodeId
   *  Drupal Node ID of the application to verify.
   *
   * @command barotraumix:verify
   * @aliases baro-verify
   * @usage barotraumix:verify 1
   */
  public function verify(int $nodeId) {
    // Attempt to verify.
    $buildId = $this->steamCMD->appInstall(NULL, TRUE);

    // @todo: Remove app creation.
    // In case of success - create application on the website.
    if ($buildId) {
      $this->core->createApp($this->core->scan(Core::BAROTRAUMA_APP_ID, $buildId))->save();
    }

    // Debugging.
    if (empty($buildId)) {
      $this->output()->writeln($this->steamCMD->lastCommand());
      $this->output()->writeln($this->steamCMD->lastCommandOutput());
    }

    // Print status.
    $msg = empty($buildId) ? '[ERROR] Verification has filed.' : '[SUCCESS] Verification successful.';
    $this->output()->writeln($msg);
  }

}
