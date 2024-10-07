import UpdatePage from './UpdatePage';

export default class UpdatePageUpdateOptions extends UpdatePage {
  protected stepCode = 'update-options';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
