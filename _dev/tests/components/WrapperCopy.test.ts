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
import WrapperCopy from '../../src/ts/appUI/components/WrapperCopy';

describe('WrapperCopy', () => {
  const mockClipboard = () => {
    Object.assign(navigator, {
      clipboard: {
        writeText: jest.fn().mockResolvedValue(undefined)
      }
    });
  };

  const createCopyButton = (targetId?: string): HTMLButtonElement => {
    const button = document.createElement('button');
    button.className = 'copy-button';

    if (targetId) {
      button.dataset.targetId = targetId;
    }

    document.body.appendChild(button);
    return button;
  };

  const createPreWithContents = (id: string, text: string): HTMLElement => {
    const pre = document.createElement('pre');
    pre.id = id;
    pre.innerText = text;
    document.body.appendChild(pre);
    return pre;
  };

  describe('Lifecycle', () => {
    let wrapper: WrapperCopy;

    beforeEach(() => {
      wrapper = new WrapperCopy();
      document.body.innerHTML = '';
      jest.clearAllMocks();
    });

    it('attempts to remove click listener on beforeDestroy()', () => {
      const spy = jest.spyOn(document, 'removeEventListener');

      wrapper.mount();
      wrapper.beforeDestroy();

      expect(spy).toHaveBeenCalledWith('click', expect.any(Function));
    });

    it('not reacts to clicks after beforeDestroy()', async () => {
      mockClipboard();
      wrapper.mount();
      wrapper.beforeDestroy();

      createPreWithContents('foo', 'copied text');
      const button = createCopyButton('foo');

      button.click();

      await Promise.resolve();

      expect(navigator.clipboard.writeText).not.toHaveBeenCalled();
    });
  });

  describe('Click handling', () => {
    let wrapper: WrapperCopy;
    jest.useFakeTimers();

    beforeEach(() => {
      wrapper = new WrapperCopy();
      document.body.innerHTML = '';
      jest.clearAllMocks();
      mockClipboard();
      wrapper.mount();
    });

    it('does nothing when clicking outside a copy button', () => {
      const div = document.createElement('div');
      document.body.appendChild(div);

      div.click();

      expect(navigator.clipboard.writeText).not.toHaveBeenCalled();
    });

    it('copies text when clicking directly on a copy button', async () => {
      createPreWithContents('foo', 'hello world');
      const button = createCopyButton('foo');

      const blurSpy = jest.spyOn(button, 'blur');

      button.click();
      await Promise.resolve();

      expect(button.classList.contains('copy-button--copied')).toBe(true);
      expect(blurSpy).toHaveBeenCalled();
      expect(navigator.clipboard.writeText).toHaveBeenCalledWith('hello world');
    });

    it('removes copied class after 1500ms', async () => {
      createPreWithContents('foo', 'hello');
      const button = createCopyButton('foo');

      button.click();
      await Promise.resolve();

      expect(button.classList.contains('copy-button--copied')).toBe(true);

      jest.advanceTimersByTime(1500);

      expect(button.classList.contains('copy-button--copied')).toBe(false);
    });

    it('handles clicks on child elements via closest()', async () => {
      createPreWithContents('foo', 'nested copy');
      const button = createCopyButton('foo');

      const span = document.createElement('span');
      button.appendChild(span);

      span.click();
      await Promise.resolve();

      expect(navigator.clipboard.writeText).toHaveBeenCalledWith('nested copy');
    });
  });
});
