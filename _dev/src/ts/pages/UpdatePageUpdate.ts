import UpdatePage from './UpdatePage';

export default class UpdatePageUpdate extends UpdatePage {
  protected stepCode = 'update';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
