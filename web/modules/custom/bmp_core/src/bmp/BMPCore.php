<?php
/**
 * @file
 * Source code of the service "bmp_core.core".
 */

namespace Drupal\bmp_core\BMP;

/**
 * Class BMPCore for main BMP Core service.
 */
class BMPCore implements BMPCoreInterface {

  /**
   * @var string|null Contains last steam executed command or NULL.
   */
  protected null|string $rawSteamCommand = NULL;

  /**
   * @var string|null Contains output from last steam executed command or NULL.
   */
  protected null|string $rawSteamCommandOutput = NULL;

  /**
   * @inheritDoc
   */
  public function steamGetBuildId(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID): null|string {
    // Check if we have output from steam.
    if ($output = $this->steamRunCommand('+app_info_update 1 +app_info_print "' . $appId . '"')) {
      // Parse response.
      $regexp = '/"branches"\s*{\s*"public"\s*{\s*"buildid"\s*"(?<buildid>\d*)"/ms';
      preg_match_all($regexp, $output, $matches, PREG_SET_ORDER);
      // Return numeric build id value.
      if (!empty($matches[0]['buildid'])) {
        return intval($matches[0]['buildid']);
      }
    }
    // Return NULL otherwise.
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function getRawSteamCommand(): string {
    return $this->rawSteamCommand;
  }

  /**
   * @inheritDoc
   */
  public function getRawSteamCommandOutput(): string {
    return $this->rawSteamCommandOutput;
  }

  /**
   * Method to run steam-cmd command.
   *
   * @param string $command
   *  Command to run.
   *
   * @param bool $login
   *  Will log in as anonymous user.
   *
   * @return string|null
   */
  protected function steamRunCommand(string $command, bool $login = TRUE): string|null {
    // Prepare command prefix and suffix.
    $prefix = 'steamcmd +@ShutdownOnFailedCommand 1 +@NoPromptForPassword 1 ';
    if ($login) {
      $prefix .= '+login anonymous ';
    }
    $suffix = ' +quit';

    // Prepare and run command.
    $run = $prefix . $command . $suffix;
    $this->rawSteamCommand = $run;
    $this->rawSteamCommandOutput = shell_exec($run);
    // @TODO: Errors handling.
    return $this->rawSteamCommandOutput;
  }
}
