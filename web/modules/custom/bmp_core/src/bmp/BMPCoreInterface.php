<?php

/**
 * @file
 * Implements inerface for MBP Core service.
 */

namespace Drupal\bmp_core\BMP;

/**
 * Interface BMPCoreInterface.
 */
interface BMPCoreInterface {

  const BAROTRAUMA_APP_ID = 1026340;

  /**
   * Method to get current BuildId of specific app from Steam.
   *
   * @param int $appId
   *  ID of the application. Barotrauma App ID will be used as default value.
   *
   * @return string|null
   */
  public function steamGetBuildId(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID): null|string;

  /**
   * Method which will return last executed command.
   *
   * @return string
   */
  public function getRawSteamCommand(): string;

  /**
   * Method which will return raw output from last executed command.
   *
   * @return string
   */
  public function getRawSteamCommandOutput(): string;
}
