<?php

/**
 * @file
 * @todo: Refactor this shit.
 */

namespace Drupal\bmp_core\Commands;

use Drupal\bmp_core\BMP\BMPCoreInterface;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\bmp_core\Commands
 *
 * @todo: Set proper name for class and file later.
 */
class BaroTraumaAppInfo extends DrushCommands {

  /**
   * @var BMPCoreInterface $bmpCore - BPMCore service.
   */
  public BMPCoreInterface $bmpCore;

  /**
   * Constructs an object.
   *
   * @param BMPCoreInterface $BMPCore
   *   The BMP Core service.
   */
  public function __construct(BMPCoreInterface $BMPCore) {
    parent::__construct();
    $this->bmpCore = $BMPCore;
  }

  /**
   * Drush command that displays BuildId of steam app.
   *
   * @param int $appId
   *  ID of Steam application to check.
   *
   * @command bmp:app-buildid
   * @aliases bmp-app-buildid
   * @usage bmp:app-buildid
   */
  public function buildid(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID) {
    $buildId = $this->bmpCore->steamGetBuildId($appId);
    if (!empty($buildId)) {
      $this->output()->writeln($buildId);
    }
    else {
      // @todo: Normal error handling.
      $this->output()->writeln('ERROR! Debugging information:');
      $this->output()->writeln($this->bmpCore->getRawSteamCommand());
      $this->output()->writeln($this->bmpCore->getRawSteamCommandOutput());
    }
  }

  /**
   * Drush command that updates specific application.
   *
   * @param int $appId
   *  ID of Steam application to update.
   *
   * @command bmp:app-update
   * @aliases bmp-app-update
   * @usage bmp:app-update
   */
  public function update(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID) {
    $status = $this->bmpCore->steamAppUpdate($appId);
    if (empty($status)) {
      // @todo: Normal error handling.
      $this->output()->writeln('ERROR! Debugging information:');
      $this->output()->writeln($this->bmpCore->getRawSteamCommand());
      $this->output()->writeln($this->bmpCore->getRawSteamCommandOutput());
    }
    else {
      // @todo: Remove.
      $this->output()->writeln($this->bmpCore->getRawSteamCommand());
      $this->output()->writeln($this->bmpCore->getRawSteamCommandOutput());
    }
  }

}
