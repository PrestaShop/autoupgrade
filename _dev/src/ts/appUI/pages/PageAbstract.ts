/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DomLifecycle from '../../types/DomLifecycle';

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
