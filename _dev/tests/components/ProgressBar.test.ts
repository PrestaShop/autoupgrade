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
import ProgressBar from '../../src/ts/appUI/components/ProgressBar';

describe('ProgressBar', () => {
  let container: HTMLElement;
  let progressBar: ProgressBar;

  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-container">
        <div role="progressbar" data-title-template="{progress_percentage}%" style="width: 0;"></div>
      </div>
    `;
    container = document.getElementById('test-container')!;
    progressBar = new ProgressBar(container);
  });

  it('should update progress bar width and aria-valuenow attribute', () => {
    progressBar.setProgressPercentage(50);

    const progressBarElement = container.querySelector('[role="progressbar"]')! as HTMLDivElement;
    expect(progressBarElement.style.width).toBe('50%');
    expect(progressBarElement.getAttribute('aria-valuenow')).toBe('50');
  });

  it('should update progress bar title and aria-label with the formatted title', () => {
    progressBar.setProgressPercentage(75);

    const progressBarElement = container.querySelector('[role="progressbar"]')! as HTMLDivElement;
    expect(progressBarElement.title).toBe('75%');
    expect(progressBarElement.getAttribute('aria-label')).toBe('75%');
  });
});
