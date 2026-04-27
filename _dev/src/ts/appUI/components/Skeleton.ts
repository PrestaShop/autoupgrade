/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { Mountable } from '../../types/DomLifecycle';
import api from '../api/RequestHandler';

export default class Skeleton implements Mountable {
  mount = () => {
    const skeletons = document.querySelectorAll('[data-skeleton]');
    skeletons.forEach((skeleton) => {
      this.#loadSkeletonContent(skeleton as HTMLElement);
    });
  };

  #loadSkeletonContent = async (skeleton: HTMLElement): Promise<void> => {
    const contentAction = skeleton?.dataset.skeleton;
    if (!contentAction) {
      throw new Error('Skeleton content action missing, cannot loading content.');
    }
    await api.post(contentAction);
  };
}
