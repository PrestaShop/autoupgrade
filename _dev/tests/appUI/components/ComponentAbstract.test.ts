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
import ComponentAbstract from '../../../src/ts/appUI/components/ComponentAbstract';
import TestComponent from '../fixtures/TestComponent';

describe('ComponentAbstract', () => {
  let container: HTMLElement;
  let testComponent: TestComponent;

  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-container">
        <div id="child-element" class="test-class">Test Element</div>
      </div>
    `;
    container = document.getElementById('test-container')!;
    testComponent = new TestComponent(container);
  });

  it('should initialize with a valid HTMLElement', () => {
    expect(testComponent).toBeInstanceOf(ComponentAbstract);
    expect(testComponent.element).toBe(container);
  });

  it('should find a child element within the container using queryElement', () => {
    const childElement = testComponent.getElement<HTMLDivElement>(
      '#child-element',
      'Child element not found'
    );
    expect(childElement).not.toBeNull();
    expect(childElement.id).toBe('child-element');
    expect(childElement.textContent).toBe('Test Element');
  });

  it('should find an element in the global DOM if not in the container', () => {
    const globalElement = document.createElement('div');
    globalElement.id = 'global-element';
    document.body.appendChild(globalElement);

    const foundElement = testComponent.getElement<HTMLDivElement>(
      '#global-element',
      'Global element not found'
    );
    expect(foundElement).not.toBeNull();
    expect(foundElement.id).toBe('global-element');
  });

  it('should throw an error if the element is not found', () => {
    expect(() =>
      testComponent.getElement<HTMLDivElement>('#non-existent', 'Element not found')
    ).toThrow('Element not found');
  });
});
