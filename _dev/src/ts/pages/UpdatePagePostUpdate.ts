import UpdatePage from './UpdatePage';

export default class UpdatePagePostUpdate extends UpdatePage {
  protected stepCode = 'post-update';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
