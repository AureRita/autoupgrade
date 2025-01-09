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

import BackupSelectionPage from "../../../views/templates/pages/restore.html.twig";
import { Default as BackupSelectionComponent } from "../components/BackupSelection.stories";
import { BackupSelection as Stepper } from "../components/Stepper.stories";

export default {
  component: BackupSelectionPage,
  id: "40",
  title: "Pages/Rollback",
};

export const BackupSelection = {
  args: {
    // Step
    step: {
      code: "backup-selection",
      title: "Backup selection",
    },
    step_parent_id: "ua_container",
    data_transparency_link: "https://www.prestashop-project.org/data-transparency",
    // Backup
    ...BackupSelectionComponent.args,
    // Stepper
    ...Stepper.args,
  },
};
