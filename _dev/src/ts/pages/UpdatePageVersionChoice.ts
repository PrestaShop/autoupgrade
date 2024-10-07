import UpdatePage from './UpdatePage';

export default class UpdatePageVersionChoice extends UpdatePage {
  protected stepCode = 'version-choice';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
