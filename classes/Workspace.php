<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;

class Workspace
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var string[] List of paths used by autoupgrade
     */
    private $paths;

    /**
     * @param string[] $paths
     */
    public function __construct(Translator $translator, array $paths)
    {
        $this->translator = $translator;
        $this->paths = $paths;
    }

    public function createFolders(): void
    {
        foreach ($this->paths as $path) {
            if (!file_exists($path) && !@mkdir($path)) {
                throw new IOException($this->translator->trans('Unable to create directory %s', [$path]));
            }
            if (!is_writable($path)) {
                throw new IOException($this->translator->trans('Cannot write to the directory. Please ensure you have the necessary write permissions on "%s".', [$path]));
            }

            $this->createPhpIndex($path);
        }
    }

    /**
     * @throws IOException
     */
    public function createPhpIndex(string $directoryPath): void
    {
        $filePath = $directoryPath . DIRECTORY_SEPARATOR . 'index.php';
        if (!file_exists($filePath)) {
            if (!copy(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'index.php', $filePath)) {
                throw new IOException($this->translator->trans('Unable to create file %s', [$filePath]));
            }
        }
    }

    /**
     * @throws IOException
     */
    public function createHtAccess(string $modulePath): void
    {
        $filePath = $modulePath . DIRECTORY_SEPARATOR . '.htaccess';
        $content = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Si on accède au dossier "log", on autorise l'accès
    RewriteCond %{REQUEST_URI} ^/log/ [NC]
    RewriteRule ^ - [L]

    # Sinon, on interdit l'accès
    RewriteRule ^ - [F]
</IfModule>
HTACCESS;
        if (!file_exists($filePath)) {
            if (!file_put_contents($filePath, $content)) {
                throw new IOException($this->translator->trans('Unable to create file %s', [$filePath]));
            }
        }
    }

    /**
     * @throws IOException
     */
    public function init($modulePath): void
    {
        $this->createFolders();
        $this->createHtAccess($modulePath);
    }
}
