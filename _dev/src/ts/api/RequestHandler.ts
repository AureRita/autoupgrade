import baseApi from './baseApi';
import { ApiResponse, ApiResponseAction } from '../types/apiTypes';
import Hydration from '../utils/Hydration';
import { AxiosError } from 'axios';

export class RequestHandler {
  public currentRequestAbortController: AbortController | null = null;

  /**
   * @public
   * @param {string} route - Target route for the POST request.
   * @param {FormData}[data=new FormData()] - Form data to send with the request by default we send FormData with admin dir required by backend.
   * @param {boolean} [fromPopState] - Indicates if the request originated from a popstate event need by hydration.
   * @returns {Promise<void>}
   * @description Sends a POST request to the specified route with optional data and pop state indicator. Cancels any ongoing request before initiating a new one.
   */
  public async post(
    route: string,
    data: FormData = new FormData(),
    fromPopState?: boolean
  ): Promise<void> {
    // Cancel any previous request if it exists
    if (this.currentRequestAbortController) {
      this.currentRequestAbortController.abort();
    }

    // Create a new AbortController for the current request (used to cancel previous request)
    this.currentRequestAbortController = new AbortController();
    const { signal } = this.currentRequestAbortController;

    // Append admin dir required by backend
    data.append('dir', window.AutoUpgradeVariables.admin_dir);

    try {
      const response = await baseApi.post<ApiResponse>('', data, {
        params: { route },
        signal
      });

      const responseData = response.data;
      await this.#handleResponse(responseData, fromPopState);
    } catch (error) {
      // A couple or errors are returned in an actual response (i.e 404 or 500)
      if (error instanceof AxiosError) {
        if (error.response?.data) {
          const responseData = error.response.data;
          responseData.new_route = 'error-page';
          await this.#handleResponse(responseData, true);
        }
      } else {
        // TODO: catch errors
        console.error(error);
      }
    }
  }

  /**
   * @public
   * @param {string} action - The action to be sent to the API.
   * @returns {Promise<ApiResponseAction | void>} - Resolves to the API response of type `ApiResponseAction` or `void` in case of an error.
   * @description Sends a POST request to the API with the specified action.
   *              Automatically includes the `admin_dir` required by the backend.
   */
  public async postAction(action: string): Promise<ApiResponseAction | void> {
    const data = new FormData();

    data.append('dir', window.AutoUpgradeVariables.admin_dir);
    data.append('action', action);

    try {
      const response = await baseApi.post('', data);
      return response.data as ApiResponseAction;
    } catch (error: unknown) {
      if (error instanceof AxiosError && error?.response?.data?.error) {
        return error.response.data as ApiResponseAction;
      }
      // TODO: catch errors
      console.error(error);
    }
  }

  /**
   * @private
   * @param {ApiResponse} response - The response data from the API.
   * @param {boolean} [fromPopState] - Indicates if the request originated from a popstate event need by hydration.
   * @returns {Promise<void>}
   * @description Handles the API response by checking for next route or hydration data.
   */
  async #handleResponse(response: ApiResponse, fromPopState?: boolean): Promise<void> {
    if ('next_route' in response) {
      await this.post(response.next_route);
    }
    if ('hydration' in response) {
      new Hydration().hydrate(response, fromPopState);
    }
  }
}

const api = new RequestHandler();

export default api;
