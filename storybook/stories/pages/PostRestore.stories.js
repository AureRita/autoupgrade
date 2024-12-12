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

import PostRestorePage from "../../../views/templates/pages/rollback.html.twig";
import { PostRestore as Stepper } from "../components/Stepper.stories";

export default {
  component: PostRestorePage,
  id: "42",
  title: "Pages/Rollback",
};

export const PostRestore = {
  args: {
    // Step
    step: {
      code: "post-restore",
      title: "Post-restore checklist",
    },
    psBaseUri: "/",
    upToDate: true,
    noLocalArchive: true,
    currentPrestashopVersion: "8.1.6",
    currentPhpVersion: "8.1",
    backlog_link: "https://myshop.com/my-backlog.txt",
    step_parent_id: "ua_container",
    data_transparency_link: "https://www.prestashop-project.org/data-transparency",
    // Stepper
    ...Stepper.args,
  },
};
