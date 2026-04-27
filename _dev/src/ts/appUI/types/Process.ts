/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { ApiResponseAction } from './apiTypes';

type ProgressTrackerCallbacks = {
  onProcessResponse: (response: ApiResponseAction) => void | Promise<void>;
  onProcessEnd: (response: ApiResponseAction) => void | Promise<void>;
  onError: (response: ApiResponseAction) => void | Promise<void>;
};

type ProcessContainerCallbacks = {
  onError: () => void;
};

export type { ProgressTrackerCallbacks, ProcessContainerCallbacks };
