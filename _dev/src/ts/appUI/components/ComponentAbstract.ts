/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
export default abstract class ComponentAbstract {
  readonly #element: HTMLElement;

  public constructor(element: HTMLElement) {
    this.#element = element;
  }

  public get element(): HTMLElement {
    return this.#element;
  }

  protected queryElement = <T extends HTMLElement>(selector: string, errorMessage: string): T => {
    const element = (this.element.querySelector(selector) as T) ?? document.querySelector(selector);
    if (!element) {
      throw new Error(errorMessage);
    }
    return element;
  };
}
