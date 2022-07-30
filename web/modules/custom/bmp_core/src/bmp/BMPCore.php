<?php
/**
 * @file
 * Source code of the service "bmp_core.core".
 */

namespace Drupal\bmp_core\BMP;

use Drupal;

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
  public function steamAppUpdate(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID): bool {
    // Get app build id from Steam.
    // @todo: Uncomment.
//    $buildId = $this->steamGetBuildId($appId);
    // @todo: Remove.
    $buildId = 9118874;
    if (empty($buildId)) {
      return FALSE;
    }

    // Ensure that available update is not a current one.
    $query = Drupal::entityQuery('node');
    $query->condition('type', 'application');
    $query->condition('field_app_id', $appId, '=');
    $query->condition('field_build_id', $buildId,'=');
    $query->count();
    $exists = $query->execute();

    // We already have an update.
    if (!empty($exists)) {
      // @todo: Print a message somehow.
      return TRUE;
    }

    // Download an update.
    // @todo: Dependency injection for this block of code.
    $uri = 'baro://' . $appId . '/' . $buildId;
    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');
    $dirStatus = $fileSystem->prepareDirectory($uri, Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
    if (!$dirStatus) {
      // @todo: Print a message somehow.
      return FALSE;
    }

    // Get real path for steamcmd and prepare command.
    $path = $fileSystem->realpath($uri);
    $command = '+force_install_dir ' . $path;
    $command .= ' +login anonymous';
    $command .= ' +app_update ' . $appId . ' validate';
    // @todo: Uncomment later.
    $this->steamRunCommand($command, FALSE);
//    $command = 'find ' . $path . ' -type d -exec chmod 0755 {} +';
//    shell_exec('find ' . $path . ' -type d -exec chown 0755 {} +');
//    shell_exec('find ' . $path . ' -type d -exec chmod 0755 {} +');

    // todo: Move to another method.
    /** @var \Drupal\bmp_core\BMP\Parser $parser */
    $parser = new Parser($appId, $buildId);
    $contentPackage = $parser->contentPackage();

    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function getRawSteamCommand(): null|string {
    return $this->rawSteamCommand;
  }

  /**
   * @inheritDoc
   */
  public function getRawSteamCommandOutput(): null|string {
    return $this->rawSteamCommandOutput;
  }

  /**
   * Method to run steam-cmd command.
   *
   * @todo: Implement static cache for executed commands.
   *
   * @param string $command
   *  Command to run.
   *
   * @param bool $login
   *  Will log in as anonymous user.
   *
   * @return null|string
   */
  protected function steamRunCommand(string $command, bool $login = TRUE): null|string {
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
