/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import DomLifecycle from '../../types/DomLifecycle';
import Skeleton from '../components/Skeleton';

export default class TemperedFilesDialog implements DomLifecycle {
  mount = (): void => {
    new Skeleton().mount();
  };

  beforeDestroy = (): void => {};
}
