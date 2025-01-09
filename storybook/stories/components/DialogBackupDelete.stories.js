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

import DialogBackupDelete from "../../../views/templates/dialogs/dialog-delete-backup.html.twig";

export default {
  title: "Components/Dialog",
  component: DialogBackupDelete,
};

export const BackupDelete = {
  args: {
    backup_name: "autoupgrade_save_8.1.6",
    backup_date: "15/07/2024 8:00",
    only_backup: true,
    form_name: "delete-backup",
    form_route_to_confirm_delete: "/",
    form_fields: {
      BACKUP_NAME: "backup_name",
    },
  },
  play: async () => {
    const dialog = document.querySelector('.dialog');
    dialog.showModal();
  },
};
