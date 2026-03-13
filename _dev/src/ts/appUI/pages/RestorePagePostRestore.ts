/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import StepPage from './StepPage';

export default class RestorePagePostRestore extends StepPage {
  protected stepCode = 'post-restore';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
