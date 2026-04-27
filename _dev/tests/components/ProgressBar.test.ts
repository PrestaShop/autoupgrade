/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
