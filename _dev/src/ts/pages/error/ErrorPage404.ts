import api from '../../api/RequestHandler';
import DomLifecycle from '../../types/DomLifecycle';

export default class ErrorPage404 implements DomLifecycle {
  isOnHomePage: boolean = false;

  public constructor() {
    this.isOnHomePage = new URLSearchParams(window.location.search).get('route') === 'home-page';
  }

  public mount = (): void => {
    this.#activeActionButton.classList.remove('hidden');
    this.#form.addEventListener('submit', this.#onSubmit);
  };

  public beforeDestroy = (): void => {
    this.#form.removeEventListener('submit', this.#onSubmit);
  };

  get #activeActionButton(): HTMLFormElement | HTMLAnchorElement {
    return this.isOnHomePage ? this.#form : this.#exitButton;
  }

  get #form(): HTMLFormElement {
    const form = document.forms.namedItem('home-page-form');
    if (!form) {
      throw new Error('Form not found');
    }

    ['routeToSubmit'].forEach((data) => {
      if (!form.dataset[data]) {
        throw new Error(`Missing data ${data} from form dataset.`);
      }
    });

    return form;
  }

  get #exitButton(): HTMLAnchorElement {
    const link = document.getElementById('exit-button');

    if (!link || !(link instanceof HTMLAnchorElement)) {
      throw new Error('Link is not found or invalid');
    }
    return link;
  }

  readonly #onSubmit = async (event: Event): Promise<void> => {
    event.preventDefault();

    await api.post(this.#form.dataset.routeToSubmit!, new FormData(this.#form));
  };
}
