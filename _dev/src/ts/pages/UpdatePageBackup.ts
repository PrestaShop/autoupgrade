import UpdatePage from './UpdatePage';

export default class UpdatePageBackup extends UpdatePage {
  protected stepCode = 'backup';

  constructor() {
    super();
  }

  public mount() {
    this.initStepper();
  }
}
