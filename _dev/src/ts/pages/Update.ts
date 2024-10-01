import PageAbstract from './PageAbstract';

export default class Update extends PageAbstract {
  constructor() {
    super();
  }

  public mount = () => {
    console.log('UPDATE PAGE MOUNTED');
  };

  public beforeDestroy = () => {
    console.log('BEFORE DESTROY');
  };
}
