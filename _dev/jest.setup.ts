/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import './tests/fakeWindow';

import { TextEncoder, TextDecoder } from 'util';
// Needed to avoid error "ReferenceError: TextEncoder is not defined" when using JSDOM in tests
Object.assign(global, { TextDecoder, TextEncoder });

beforeAll(() => {});
