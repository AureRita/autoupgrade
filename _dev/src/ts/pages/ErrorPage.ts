import DomLifecycle from '../types/DomLifecycle';
import ErrorPage404 from './error/ErrorPage404';
import PageAbstract from './PageAbstract';

export default class ErrorPage extends PageAbstract {
  errorPage?: DomLifecycle;

  constructor() {
    super();

    if (document.getElementById('ua_error_404')) {
      this.errorPage = new ErrorPage404();
    }
  }

  public mount = (): void => {
    this.errorPage?.mount();
  };

  public beforeDestroy = (): void => {
    this.errorPage?.beforeDestroy();
  };
}
