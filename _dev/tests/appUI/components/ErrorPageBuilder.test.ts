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
import { JSDOM } from 'jsdom';
import ErrorPageBuilder from '../../src/ts/components/ErrorPageBuilder';

describe('ErrorPageBuilder', () => {
  let errorElement: DocumentFragment;
  let errorPageBuilder: ErrorPageBuilder;

  beforeEach(() => {
    errorElement = JSDOM.fragment(`<div id="ua_error_placeholder" class="error-page">
    <div class="error-page__container">
        <div class="error-page__code">
    <!-- Leave these 3 <span> on the same line or the code will be all broken -->
    <span class="error-page__code-char" data-error-code-char-index="1"></span><span class="error-page__code-char" data-error-code-char-index="2"></span><span class="error-page__code-char" data-error-code-char-index="3"></span>
  </div>

      <div class="error-page__infos">
                  <h2 class="error-page__title">
            Something went wrong...
          </h2>
        
          <div class="error-page__desc">
    <div class="error-page__desc-404 hidden">
      <p>The requested page or resource could not be found. This might be due to:</p>
      <ul>
        <li>A broken or outdated link.</li>
        <li>The page being moved or deleted.</li>
        <li>A typo in the URL.</li>
      </ul>
    </div>

    <div class="error-page__desc-500 hidden">
      <p>It seems there was an issue with the server. This type of error usually happens when:</p>
      <ul>
        <li>The server is temporarily unavailable.</li>
        <li>There's a misconfiguration or unexpected problem on the server.</li>
      </ul>
    </div>

    <div class="error-page__desc-502 hidden">
      <p>It looks like there is no data received from the server. This can happen if:</p>
      <ul>
        <li>The server is temporarily unavailable.</li>
        <li>There's an issue with the network or connection.</li>
      </ul>
    </div>

    <div class="error-page__desc-ETIMEDOUT hidden">
      <p>The request timed out. It seems the connection took too long to respond. This could be due to:</p>
      <ul>
        <li>A slow or unstable internet connection.</li>
        <li>The server is currently busy or unresponsive.</li>
        <li>Temporary network issues.</li>
      </ul>
    </div>

    <div class="error-page__desc-APP_ERR_RESPONSE_BAD_TYPE hidden">
      <p>The response received from the server in malformed. This can happen if:</p>
      <ul>
        <li>The request did not reach the module.</li>
        <li>There’s an issue processing the response on the app or browser.</li>
      </ul>
    </div>

    <div class="error-page__desc-APP_ERR_RESPONSE_EMPTY hidden">
      <p>It looks like there is no data received from the server. This can happen if:</p>
      <ul>
        <li>The server is temporarily unavailable.</li>
        <li>There's an issue with the network or connection.</li>
      </ul>
    </div>
  </div>

        <div class="error-page__buttons">
            <form id="submit-error-report" name="submit-error-report" data-route-to-submit="update-step-update-submit-error-report">
  <button class="btn btn-lg btn-default" type="submit">
    <i class="material-icons">send</i>
    Send error report
  </button>
</form>
                          <form class="error-page__home-page-form hidden" id="home-page-form" name="home-page-form" data-route-to-submit="home-page">
              <button class="btn btn-primary btn-lg" type="submit">
                Go back to Update assistant
              </button>
            </form>

            <a class="btn btn-primary btn-lg hidden" id="exit-button" href="/admin-dev">
              <i class="material-icons">exit_to_app</i>
              Exit
            </a>
          
        </div>
      </div>
    </div>
  </div>

  <pre id="log-additional-contents" class="hidden"></pre>`);
    errorPageBuilder = new ErrorPageBuilder(errorElement);
  });

  test('updateId should update the id of the error placeholder', () => {
    const referenceToDiv = errorElement.getElementById('ua_error_placeholder');
    errorPageBuilder.updateId('404');
    expect(referenceToDiv!.id).toBe('ua_error_404');
  });

  test('updateLeftColumn should update error code display with HTTP 404', () => {
    errorPageBuilder.updateLeftColumn(404);
    const chars = errorElement.querySelectorAll('.error-page__code-char');
    expect(chars[0].innerHTML).toBe('4');
    expect(chars[1].innerHTML).toBe('O');
    expect(chars[2].innerHTML).toBe('4');
  });

  test('updateLeftColumn should update error code display with HTTP 500', () => {
    errorPageBuilder.updateLeftColumn(500);
    const chars = errorElement.querySelectorAll('.error-page__code-char');
    expect(chars[0].innerHTML).toBe('5');
    expect(chars[1].innerHTML).toBe('O');
    expect(chars[2].innerHTML).toBe('O');
  });

  test('updateLeftColumn should hide the panel if not an HTTP error', () => {
    errorPageBuilder.updateLeftColumn(1234);
    expect(errorElement.querySelector('.error-page__code')!.classList.contains('hidden')).toBe(
      true
    );
  });

  test('updateLeftColumn should hide the panel if code is empty', () => {
    errorPageBuilder.updateLeftColumn(undefined);
    expect(errorElement.querySelector('.error-page__code')!.classList.contains('hidden')).toBe(
      true
    );
  });

  test('updateDescriptionBlock should show a user-friendly message of a HTTP code if available', () => {
    errorPageBuilder.updateDescriptionBlock({ code: 404, type: 'NOT_FOUND' });
    expect(errorElement.querySelector('.error-page__desc-404')!.classList.contains('hidden')).toBe(
      false
    );
  });

  test('updateDescriptionBlock should show a user-friendly message of a error type if available', () => {
    errorPageBuilder.updateDescriptionBlock({ code: undefined, type: 'APP_ERR_RESPONSE_EMPTY' });
    expect(
      errorElement
        .querySelector('.error-page__desc-APP_ERR_RESPONSE_EMPTY')!
        .classList.contains('hidden')
    ).toBe(false);
  });

  test('updateDescriptionBlock should set error type as text if no message available', () => {
    errorPageBuilder.updateDescriptionBlock({ code: 999, type: 'CUSTOM_ERROR' });
    expect(errorElement.querySelector('.error-page__desc')!.innerHTML).toBe('CUSTOM_ERROR');
  });

  test('updateResponseBlock should get the response contents', () => {
    errorPageBuilder.updateResponseBlock('{"Some data": "Oh no!"}');
    expect(errorElement.getElementById('log-additional-contents')?.textContent).toBe(
      '{"Some data": "Oh no!"}'
    );
  });
});
