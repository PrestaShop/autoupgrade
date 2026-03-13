/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
export interface Destroyable {
  /**
   * @description Method to clean up and perform necessary teardown operations before the page component is destroyed. Should be implemented by subclasses to remove event listeners, clear timers, etc.
   * @returns {void}
   */
  beforeDestroy(): void;
}

export interface Mountable {
  /**
   * @description Method to initialize and mount the page component. Should be implemented by subclasses to set up event listeners, render content, etc.
   * @returns {void}
   */
  mount(): void;
}

/**
 * @interface
 * @description Base abstract class defining the structure for page components, requiring implementation of lifecycle methods for mounting and destruction.
 */
export default interface DomLifecycle extends Destroyable, Mountable {}
