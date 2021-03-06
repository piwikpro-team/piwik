<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole\Commands;


use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;

/**
 * @package CoreConsole
 */
class GeneratePluginBase extends ConsoleCommand
{
    public function getPluginPath($pluginName)
    {
        return PIWIK_INCLUDE_PATH . '/plugins/' . ucfirst($pluginName);
    }

    private function createFolderWithinPluginIfNotExists($pluginName, $folder)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        if (!file_exists($pluginName . $folder)) {
            Filesystem::mkdir($pluginPath . $folder, true);
        }
    }

    protected function createFileWithinPluginIfNotExists($pluginName, $fileName, $content)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        if (!file_exists($pluginPath . $fileName)) {
            file_put_contents($pluginPath . $fileName, $content);
        }
    }

    /**
     * @param string $templateFolder  full path like /home/...
     * @param string $pluginName
     * @param array $replace         array(key => value) $key will be replaced by $value in all templates
     * @param array $whitelistFiles  If not empty, only given files/directories will be copied.
     *                               For instance array('/Controller.php', '/templates', '/templates/index.twig')
     */
    protected function copyTemplateToPlugin($templateFolder, $pluginName, array $replace = array(), $whitelistFiles = array())
    {
        $replace['PLUGINNAME'] = $pluginName;

        $files = array_merge(
                Filesystem::globr($templateFolder, '*'),
                // Also copy files starting with . such as .gitignore
                Filesystem::globr($templateFolder, '.*')
        );

        foreach ($files as $file) {
            $fileNamePlugin = str_replace($templateFolder, '', $file);

            if (!empty($whitelistFiles) && !in_array($fileNamePlugin, $whitelistFiles)) {
                continue;
            }

            if (is_dir($file)) {
                $this->createFolderWithinPluginIfNotExists($pluginName, $fileNamePlugin);
            } else {
                $template = file_get_contents($file);
                foreach ($replace as $key => $value) {
                    $template = str_replace($key, $value, $template);
                }

                foreach ($replace as $key => $value) {
                    $fileNamePlugin = str_replace($key, $value, $fileNamePlugin);
                }

                $this->createFileWithinPluginIfNotExists($pluginName, $fileNamePlugin, $template);
            }

        }
    }

    protected function getPluginNames()
    {
        $pluginDirs = \_glob(PIWIK_INCLUDE_PATH . '/plugins/*', GLOB_ONLYDIR);

        $pluginNames = array();
        foreach ($pluginDirs as $pluginDir) {
            $pluginNames[] = basename($pluginDir);
        }

        return $pluginNames;
    }

    protected function getPluginNamesHavingNotSpecificFile($filename)
    {
        $pluginDirs = \_glob(PIWIK_INCLUDE_PATH . '/plugins/*', GLOB_ONLYDIR);

        $pluginNames = array();
        foreach ($pluginDirs as $pluginDir) {
            if (!file_exists($pluginDir . '/' . $filename)) {
                $pluginNames[] = basename($pluginDir);
            }
        }

        return $pluginNames;
    }

}