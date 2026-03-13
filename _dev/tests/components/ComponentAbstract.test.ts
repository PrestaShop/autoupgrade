/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import ComponentAbstract from '../../src/ts/appUI/components/ComponentAbstract';
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
