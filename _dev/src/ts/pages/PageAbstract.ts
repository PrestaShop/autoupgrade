/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
import DomLifecycle from '../types/DomLifecycle';

/**
 * @abstract
 * @description Base abstract class defining the structure for page components, requiring implementation of lifecycle methods for mounting and destruction.
 */
export default abstract class PageAbstract implements DomLifecycle {
  /**
   * @abstract
   * @description Method to initialize and mount the page component. Should be implemented by subclasses to set up event listeners, render content, etc.
   * @returns {void}
   */
  abstract mount(): void;

  /**
   * @abstract
   * @description Method to clean up and perform necessary teardown operations before the page component is destroyed. Should be implemented by subclasses to remove event listeners, clear timers, etc.
   * @returns {void}
   */
  abstract beforeDestroy(): void;
}
